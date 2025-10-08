# Extension Development Guide

## Overview

The parsable-file-multi-tool is designed with extensibility in mind, allowing developers to create custom components that integrate seamlessly with the core system. This guide provides comprehensive instructions for extending the tool's functionality through custom actions, readers, writers, modifiers, and other components.

## Prerequisites

Before developing extensions, ensure you have:

- PHP 8.0 or higher
- Composer for dependency management
- Understanding of the [system architecture](architecture.md)
- Familiarity with PHP interfaces and object-oriented programming
- Basic knowledge of the transformation pipeline concepts

## Extension Types

The system supports several types of extensions:

1. **Custom Actions** - Transform data items during processing
2. **Custom Readers** - Read data from new source formats
3. **Custom Writers** - Output data to new target formats
4. **Custom Modifiers** - Process text and data values
5. **Custom Converters** - Convert between data structures
6. **Custom Formatters** - Format data for specific outputs

## Creating Custom Actions

Actions are the most common extension type, allowing you to transform data items as they flow through the pipeline.

### Basic Action Structure

```php
<?php

namespace Misery\Component\Action;

class CustomTransformAction implements ActionInterface
{
    public const NAME = 'custom_transform';
    
    public function execute(array $item, array $config): array
    {
        // Your transformation logic here
        $item['processed'] = true;
        $item['timestamp'] = date('Y-m-d H:i:s');
        
        return $item;
    }
}
```

### Action Interface Requirements

All actions must implement the `ActionInterface`:

```php
interface ActionInterface
{
    public function execute(array $item, array $config): array;
}
```

### Action Configuration

Actions receive configuration from YAML files through the `$config` parameter:

```yaml
# transformation.yaml
actions:
  - action: custom_transform
    enabled: true
    prefix: "CUSTOM_"
    multiplier: 2.5
```

Access configuration in your action:

```php
public function execute(array $item, array $config): array
{
    $enabled = $config['enabled'] ?? true;
    $prefix = $config['prefix'] ?? '';
    $multiplier = $config['multiplier'] ?? 1.0;
    
    if (!$enabled) {
        return $item;
    }
    
    // Use configuration values
    if (isset($item['name'])) {
        $item['name'] = $prefix . $item['name'];
    }
    
    if (isset($item['value'])) {
        $item['value'] = $item['value'] * $multiplier;
    }
    
    return $item;
}
```

### Advanced Action Example

Here's a more complex action that validates and transforms product data:

```php
<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class ProductValidationAction implements ActionInterface, OptionsInterface
{
    use OptionsTrait;
    
    public const NAME = 'product_validation';
    
    private array $requiredFields = ['sku', 'name', 'price'];
    private array $errors = [];
    
    public function execute(array $item, array $config): array
    {
        $this->setOptions($config);
        
        // Validate required fields
        foreach ($this->requiredFields as $field) {
            if (!isset($item[$field]) || empty($item[$field])) {
                $this->errors[] = "Missing required field: {$field}";
                $item['_validation_errors'][] = "Missing required field: {$field}";
            }
        }
        
        // Validate price format
        if (isset($item['price']) && !is_numeric($item['price'])) {
            $this->errors[] = "Invalid price format: {$item['price']}";
            $item['_validation_errors'][] = "Invalid price format";
        }
        
        // Transform price to float
        if (isset($item['price'])) {
            $item['price'] = (float) $item['price'];
        }
        
        // Add validation status
        $item['_is_valid'] = empty($item['_validation_errors'] ?? []);
        
        return $item;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
}
```

## Creating Custom Readers

Readers extract data from various sources and feed it into the transformation pipeline.

### Basic Reader Structure

```php
<?php

namespace Misery\Component\Reader;

class CustomApiReader implements ReaderInterface
{
    public const NAME = 'custom_api';
    
    public function read(array $config): iterable
    {
        $apiUrl = $config['url'] ?? '';
        $apiKey = $config['api_key'] ?? '';
        
        // Fetch data from API
        $data = $this->fetchFromApi($apiUrl, $apiKey);
        
        // Yield each item
        foreach ($data as $item) {
            yield $item;
        }
    }
    
    private function fetchFromApi(string $url, string $apiKey): array
    {
        // Implementation for API data fetching
        $context = stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer {$apiKey}\r\n"
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true) ?? [];
    }
}
```

### Reader Configuration

Configure your custom reader in YAML:

```yaml
# transformation.yaml
input:
  reader: custom_api
  url: "https://api.example.com/products"
  api_key: "${API_KEY}"
  batch_size: 100
```

## Creating Custom Writers

Writers output transformed data to various destinations.

### Basic Writer Structure

```php
<?php

namespace Misery\Component\Writer;

class CustomDatabaseWriter implements WriterInterface
{
    public const NAME = 'custom_database';
    
    private $connection;
    
    public function write(iterable $items, array $config): void
    {
        $this->initializeConnection($config);
        
        foreach ($items as $item) {
            $this->insertItem($item);
        }
        
        $this->closeConnection();
    }
    
    private function initializeConnection(array $config): void
    {
        $dsn = $config['dsn'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        
        $this->connection = new \PDO($dsn, $username, $password);
    }
    
    private function insertItem(array $item): void
    {
        $sql = "INSERT INTO products (sku, name, price) VALUES (?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            $item['sku'] ?? '',
            $item['name'] ?? '',
            $item['price'] ?? 0
        ]);
    }
    
    private function closeConnection(): void
    {
        $this->connection = null;
    }
}
```

## Creating Custom Modifiers

Modifiers process individual values within data items.

### Basic Modifier Structure

```php
<?php

namespace Misery\Component\Modifier;

class CustomFormatModifier implements ModifierInterface
{
    public const NAME = 'custom_format';
    
    public function modify($value, array $options = [])
    {
        $format = $options['format'] ?? 'default';
        
        switch ($format) {
            case 'uppercase':
                return strtoupper($value);
            case 'lowercase':
                return strtolower($value);
            case 'title':
                return ucwords($value);
            default:
                return $value;
        }
    }
}
```

### Using Modifiers in Configuration

```yaml
actions:
  - action: format
    field: name
    modifier: custom_format
    options:
      format: title
```

## Registration and Discovery

### Automatic Registration

The system automatically discovers and registers components that follow naming conventions:

1. Place your classes in the appropriate namespace
2. Implement the required interface
3. Define the `NAME` constant
4. The registry will automatically find and register your component

### Manual Registration

For custom registration logic, you can manually register components:

```php
// In your bootstrap or configuration file
$actionRegistry = new ActionRegistry();
$actionRegistry->register(new CustomTransformAction());

$readerRegistry = new ReaderRegistry();
$readerRegistry->register(new CustomApiReader());
```

## Testing Extensions

### Unit Testing

Create unit tests for your extensions:

```php
<?php

namespace Tests\Component\Action;

use PHPUnit\Framework\TestCase;
use Misery\Component\Action\CustomTransformAction;

class CustomTransformActionTest extends TestCase
{
    public function testExecuteAddsProcessedFlag(): void
    {
        $action = new CustomTransformAction();
        $item = ['name' => 'Test Product'];
        $config = [];
        
        $result = $action->execute($item, $config);
        
        $this->assertTrue($result['processed']);
        $this->assertArrayHasKey('timestamp', $result);
    }
    
    public function testExecuteWithConfiguration(): void
    {
        $action = new CustomTransformAction();
        $item = ['name' => 'Product', 'value' => 10];
        $config = ['prefix' => 'CUSTOM_', 'multiplier' => 2.0];
        
        $result = $action->execute($item, $config);
        
        $this->assertEquals('CUSTOM_Product', $result['name']);
        $this->assertEquals(20.0, $result['value']);
    }
}
```

### Integration Testing

Test your extensions within the full pipeline:

```php
<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Misery\Component\Pipeline\Pipeline;

class CustomActionIntegrationTest extends TestCase
{
    public function testCustomActionInPipeline(): void
    {
        $config = [
            'input' => [
                'reader' => 'array',
                'data' => [
                    ['name' => 'Product 1', 'value' => 100],
                    ['name' => 'Product 2', 'value' => 200]
                ]
            ],
            'actions' => [
                [
                    'action' => 'custom_transform',
                    'prefix' => 'TEST_',
                    'multiplier' => 1.5
                ]
            ],
            'output' => [
                'writer' => 'array'
            ]
        ];
        
        $pipeline = new Pipeline();
        $results = $pipeline->process($config);
        
        $this->assertCount(2, $results);
        $this->assertEquals('TEST_Product 1', $results[0]['name']);
        $this->assertEquals(150.0, $results[0]['value']);
    }
}
```

## Best Practices

### Error Handling

Always implement proper error handling in your extensions:

```php
public function execute(array $item, array $config): array
{
    try {
        // Your transformation logic
        return $this->processItem($item, $config);
    } catch (\Exception $e) {
        // Log the error
        error_log("Custom action error: " . $e->getMessage());
        
        // Add error information to item
        $item['_errors'][] = $e->getMessage();
        return $item;
    }
}
```

### Configuration Validation

Validate configuration parameters:

```php
public function execute(array $item, array $config): array
{
    $this->validateConfig($config);
    
    // Process item
    return $item;
}

private function validateConfig(array $config): void
{
    $required = ['api_url', 'api_key'];
    
    foreach ($required as $field) {
        if (!isset($config[$field])) {
            throw new \InvalidArgumentException("Missing required config: {$field}");
        }
    }
}
```

### Memory Efficiency

Use generators and iterators for large datasets:

```php
public function read(array $config): iterable
{
    $file = fopen($config['filename'], 'r');
    
    try {
        while (($line = fgets($file)) !== false) {
            yield json_decode($line, true);
        }
    } finally {
        fclose($file);
    }
}
```

### Documentation

Document your extensions thoroughly:

```php
/**
 * Custom Transform Action
 * 
 * Transforms items by adding metadata and applying multipliers.
 * 
 * Configuration options:
 * - prefix: String prefix to add to names
 * - multiplier: Numeric multiplier for values
 * - enabled: Boolean to enable/disable processing
 * 
 * @package Misery\Component\Action
 */
class CustomTransformAction implements ActionInterface
{
    // Implementation
}
```

## Deployment and Distribution

### Packaging Extensions

Create a composer package for your extensions:

```json
{
    "name": "vendor/parsable-file-extensions",
    "description": "Custom extensions for parsable-file-multi-tool",
    "type": "library",
    "require": {
        "php": ">=8.0"
    },
    "autoload": {
        "psr-4": {
            "Vendor\\Extensions\\": "src/"
        }
    }
}
```

### Installation

Users can install your extensions via Composer:

```bash
composer require vendor/parsable-file-extensions
```

## Examples

### Complete Custom Action Example

Here's a complete example of a custom action that processes e-commerce product data:

```php
<?php

namespace Vendor\Extensions\Action;

use Misery\Component\Action\ActionInterface;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

/**
 * E-commerce Product Processor
 * 
 * Processes product data for e-commerce platforms by:
 * - Calculating discounted prices
 * - Formatting product URLs
 * - Adding category hierarchies
 * - Validating required fields
 */
class EcommerceProductAction implements ActionInterface, OptionsInterface
{
    use OptionsTrait;
    
    public const NAME = 'ecommerce_product';
    
    public function execute(array $item, array $config): array
    {
        $this->setOptions($config);
        
        // Calculate discounted price
        $item = $this->calculateDiscount($item);
        
        // Format product URL
        $item = $this->formatProductUrl($item);
        
        // Build category hierarchy
        $item = $this->buildCategoryHierarchy($item);
        
        // Validate required fields
        $item = $this->validateProduct($item);
        
        return $item;
    }
    
    private function calculateDiscount(array $item): array
    {
        $discountPercent = $this->getOption('discount_percent', 0);
        
        if ($discountPercent > 0 && isset($item['price'])) {
            $originalPrice = (float) $item['price'];
            $discountAmount = $originalPrice * ($discountPercent / 100);
            $item['discounted_price'] = $originalPrice - $discountAmount;
            $item['savings'] = $discountAmount;
        }
        
        return $item;
    }
    
    private function formatProductUrl(array $item): array
    {
        $baseUrl = $this->getOption('base_url', '');
        
        if ($baseUrl && isset($item['sku'])) {
            $slug = strtolower(str_replace(' ', '-', $item['name'] ?? $item['sku']));
            $item['product_url'] = rtrim($baseUrl, '/') . '/products/' . $slug;
        }
        
        return $item;
    }
    
    private function buildCategoryHierarchy(array $item): array
    {
        if (isset($item['category_path'])) {
            $categories = explode('>', $item['category_path']);
            $item['category_hierarchy'] = array_map('trim', $categories);
            $item['primary_category'] = trim($categories[0] ?? '');
        }
        
        return $item;
    }
    
    private function validateProduct(array $item): array
    {
        $required = $this->getOption('required_fields', ['sku', 'name', 'price']);
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($item[$field]) || empty($item[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }
        
        $item['validation_errors'] = $errors;
        $item['is_valid'] = empty($errors);
        
        return $item;
    }
}
```

### Usage Configuration

```yaml
# transformation.yaml
actions:
  - action: ecommerce_product
    discount_percent: 15
    base_url: "https://mystore.com"
    required_fields:
      - sku
      - name
      - price
      - category_path
```

## Related Topics

- [Architecture Overview](architecture.md) - Understanding the system structure
- [Contributing Guidelines](contributing.md) - How to contribute extensions back to the project
- [Action Reference](../reference/actions/) - Complete action documentation
- [Configuration Guide](../getting-started/configuration.md) - System configuration options

## Troubleshooting

### Common Issues

**Extension Not Found**
- Verify the class is in the correct namespace
- Check that the `NAME` constant is defined
- Ensure the class implements the required interface

**Configuration Not Working**
- Validate YAML syntax
- Check configuration parameter names match your code
- Verify required parameters are provided

**Memory Issues with Large Datasets**
- Use generators instead of arrays for large data sets
- Process items one at a time rather than loading all into memory
- Consider implementing batch processing

**Performance Problems**
- Profile your extension code to identify bottlenecks
- Use caching for expensive operations
- Minimize database queries and API calls

### Getting Help

- Check the [troubleshooting guide](../user-guide/troubleshooting.md)
- Review existing extensions in the codebase for examples
- Join the community discussions for support
- Submit issues on the project repository