
# Extension Action

## Overview

The extension action allows you to extend the action framework beyond the shipped action set by implementing custom logic through PHP extensions. It provides a powerful way to create specialized transformations while maintaining access to configuration data and following established interfaces.

## Syntax

```yaml
actions:
  - action: extension
    extension: ExtensionClassName
    source: data_source
    # Custom options for your extension
    custom_option: custom_value
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| extension | string | Yes | - | Name of the extension class to execute |
| source | string | No | - | Optional data source for the extension |
| [custom] | any | No | - | Custom parameters specific to your extension |

### Parameter Details

#### extension
The name of the PHP extension class that implements the ExtensionInterface.

- **Format:** String class name
- **Example:** `"MyCustomExtension"`, `"MakeAddressLineExtension"`
- **Behavior:** The specified class will be instantiated and executed

#### source
Optional data source that the extension can access for additional data.

- **Format:** String source name
- **Example:** `"other_products.csv"`, `"reference_data"`
- **Behavior:** Makes external data available to the extension

#### Custom Parameters
Any additional parameters specific to your extension implementation.

- **Format:** Various types depending on extension needs
- **Example:** `{"my_option": "my_value", "threshold": 100}`
- **Behavior:** Passed to the extension constructor as options

## Extension Development

### Basic Extension Template

```php
<?php

namespace Extensions;

use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Configurator\ReadOnlyConfiguration;
use Misery\Component\Extension\ExtensionInterface;

class MyCustomExtension implements ExtensionInterface
{
    use OptionsTrait;
    
    private ReadOnlyConfiguration $configuration;
    
    private $options = [
        'my_option' => null,
        'threshold' => 0,
    ];

    public function __construct(ReadOnlyConfiguration $configuration, array $options)
    {
        $this->configuration = $configuration;
        $this->setOptions($options);
    }

    public function apply($item): array
    {
        $option = $this->getOption('my_option');
        $threshold = $this->getOption('threshold');
        
        // Your custom logic here
        // Manipulate $item and return modified array
        
        return $item;
    }
}
```

### ReadOnlyConfiguration Access

The ReadOnlyConfiguration provides access to:
- Lists and mapping data
- Source configurations
- Pipeline context data
- External data sources

## Examples

### Address Line Extension

```yaml
actions:
  - action: extension
    extension: MakeAddressLineExtension
    fields: ['street', 'city', 'state', 'country']
    store_field: address_line
```

**Input:**
```json
{
  "street": "123 Main Street",
  "city": "Anytown",
  "state": "CA",
  "country": "USA"
}
```

**Output:**
```json
{
  "street": "123 Main Street",
  "city": "Anytown",
  "state": "CA",
  "country": "USA",
  "address_line": "street: 123 Main Street, city: Anytown, state: CA, country: USA"
}
```

### Data Enrichment Extension

```yaml
actions:
  - action: extension
    extension: ProductEnrichmentExtension
    source: product_catalog.csv
    enrich_fields: ['category', 'brand', 'specifications']
    fallback_value: 'Unknown'
```

**Input:**
```json
{
  "product_id": "PROD001",
  "name": "Widget Pro"
}
```

**Output:**
```json
{
  "product_id": "PROD001",
  "name": "Widget Pro",
  "category": "Electronics",
  "brand": "TechCorp",
  "specifications": "High-quality widget with advanced features"
}
```

### Validation Extension

```yaml
actions:
  - action: extension
    extension: DataValidationExtension
    validation_rules:
      email: 'email_format'
      phone: 'phone_format'
      age: 'numeric_range'
    min_age: 18
    max_age: 120
```

## Best Practices

### When to Use Extensions

✅ **Recommended Use Cases:**
- Complex field transformations that require custom logic
- Data enrichment from external sources
- Specialized validation or calculation logic
- Integration with third-party APIs or services
- Performance-critical operations that benefit from native PHP

### When NOT to Use Extensions

❌ **Avoid Extensions For:**
- Simple field operations (use built-in actions instead)
- Making external HTTP calls (use converters)
- Combining multiple regular actions (use pipeline composition)
- Operations that can be achieved with existing actions

### Development Guidelines

1. **Follow the Interface:** Always implement `ExtensionInterface`
2. **Use OptionsTrait:** Leverage the options trait for parameter handling
3. **Error Handling:** Implement proper error handling and validation
4. **Documentation:** Document your extension's purpose and parameters
5. **Testing:** Write unit tests for your extension logic

## Use Cases

### Use Case 1: Complex Business Logic
Implement specialized business rules that cannot be expressed through standard actions.

### Use Case 2: Performance Optimization
Create optimized implementations for computationally intensive operations.

### Use Case 3: External System Integration
Interface with external systems or APIs that require custom authentication or protocols.

## Common Issues and Solutions

### Issue: Extension Class Not Found

**Symptoms:** Extension action fails with class not found error.

**Cause:** Extension class is not properly loaded or namespace is incorrect.

**Solution:** Ensure the extension class is in the correct namespace and autoloaded.

```php
// Ensure proper namespace and class name
namespace Extensions;

class MyCustomExtension implements ExtensionInterface
{
    // Implementation
}
```

### Issue: Configuration Data Not Accessible

**Symptoms:** Extension cannot access expected configuration data.

**Cause:** ReadOnlyConfiguration is not properly utilized or data is not available.

**Solution:** Use the configuration object to access available data sources.

```php
public function apply($item): array
{
    // Access configuration data
    $lists = $this->configuration->getLists();
    $sources = $this->configuration->getSources();
    
    return $item;
}
```

### Issue: Options Not Working

**Symptoms:** Custom options passed to extension are not accessible.

**Cause:** Options are not properly defined or accessed.

**Solution:** Define default options and use OptionsTrait methods.

```php
private $options = [
    'custom_option' => 'default_value',
];

public function apply($item): array
{
    $value = $this->getOption('custom_option');
    // Use the option value
    return $item;
}
```

## Performance Considerations

- Extensions run in PHP and can be more performant than multiple action chains
- Consider memory usage when processing large datasets
- Implement efficient algorithms for complex operations
- Cache frequently accessed configuration data

## Security Considerations

- Validate all input parameters and data
- Sanitize data when interfacing with external systems
- Follow secure coding practices
- Avoid exposing sensitive information in error messages

## Related Actions

- [Statement Action](./statement_action.md) - For conditional logic without custom code
- [Format Action](./format_action.md) - For standard formatting operations
- [Convergence Action](./convergence_action.md) - For field combination without custom logic

## See Also

- [Extension Development Guide](../../../developer-guide/extensions.md)
- [Transformation Steps](../directives/transformation_steps.md)
- [Configuration Reference](../reference/configuration.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: utility*
