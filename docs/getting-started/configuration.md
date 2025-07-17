# Configuration Guide

This guide covers all configuration options for the Parsable File Multi-Tool, including account setup, context parameters, and security best practices.

## Configuration File Structure

Configuration files use YAML format and can include several main sections:

```yaml
# Global aliases for reusable values
aliases:
  input_file: 'products.csv'
  output_file: 'processed_products.csv'

# Account credentials for API integrations
account:
  - name: "production-akeneo"
    domain: "https://your-akeneo.com"
    client_id: "your_client_id"
    client_secret: "your_client_secret"
    username: "api_user"
    password: "secure_password"

# Context parameters for dynamic values
context:
  environment: "production"
  batch_size: 1000
  locale: "en_US"

# Main transformation pipeline
pipeline:
  input:
    # Input configuration
  actions:
    # Transformation actions
  output:
    # Output configuration
```

## Account Configuration

Account configurations store credentials and connection details for external systems.

### Basic Account Setup

```yaml
account:
  - name: "my-account"
    domain: "https://api.example.com"
    username: "api_user"
    password: "secure_password"
```

### Akeneo PIM Account

```yaml
account:
  - name: "akeneo-production"
    domain: "https://your-akeneo-instance.cloud.akeneo.com"
    client_id: "1_abc123def456"
    client_secret: "xyz789uvw012"
    username: "import_user"
    password: "strong_password_123"
```

### Multiple Accounts

You can define multiple accounts for different environments:

```yaml
account:
  - name: "akeneo-dev"
    domain: "https://dev-akeneo.example.com"
    client_id: "dev_client_id"
    client_secret: "dev_client_secret"
    username: "dev_user"
    password: "dev_password"
    
  - name: "akeneo-prod"
    domain: "https://prod-akeneo.example.com"
    client_id: "prod_client_id"
    client_secret: "prod_client_secret"
    username: "prod_user"
    password: "prod_password"
```

### Account Parameters

| Parameter | Description | Required | Example |
|-----------|-------------|----------|---------|
| `name` | Unique identifier for the account | Yes | `"production-api"` |
| `domain` | Base URL for API endpoints | Yes | `"https://api.example.com"` |
| `client_id` | OAuth client identifier | For OAuth | `"1_abc123def456"` |
| `client_secret` | OAuth client secret | For OAuth | `"xyz789uvw012"` |
| `username` | Username for authentication | Yes | `"api_user"` |
| `password` | Password for authentication | Yes | `"secure_password"` |
| `timeout` | Request timeout in seconds | No | `30` |
| `verify_ssl` | Enable SSL certificate verification | No | `true` |

## Context Parameters

Context parameters provide dynamic values that can be reused throughout your configuration.

### Basic Context Usage

```yaml
context:
  environment: "production"
  locale: "en_US"
  batch_size: 500
  date_format: "Y-m-d H:i:s"

pipeline:
  input:
    reader:
      type: csv
      filename: 'products_%environment%.csv'
      options:
        batch_size: '%batch_size%'
```

### Environment-Specific Contexts

```yaml
context:
  # Development environment
  dev:
    api_url: "https://dev-api.example.com"
    debug_mode: true
    batch_size: 10
    
  # Production environment  
  prod:
    api_url: "https://api.example.com"
    debug_mode: false
    batch_size: 1000

# Use context values with %variable% syntax
pipeline:
  input:
    reader:
      endpoint: '%prod.api_url%/products'
      batch_size: '%prod.batch_size%'
```

### Dynamic Context Values

Context parameters can include dynamic values:

```yaml
context:
  current_date: "{{ 'now'|date('Y-m-d') }}"
  timestamp: "{{ 'now'|date('Y-m-d_H-i-s') }}"
  output_filename: "export_{{ 'now'|date('Y-m-d') }}.csv"

pipeline:
  output:
    writer:
      filename: '%output_filename%'
```

## Aliases

Aliases provide shortcuts for commonly used values and improve configuration readability.

### File Path Aliases

```yaml
aliases:
  # Input files
  product_file: 'products.csv'
  category_file: 'categories.csv'
  attribute_file: 'attributes.csv'
  
  # Output files
  processed_products: 'processed_products.csv'
  error_log: 'transformation_errors.log'
  
  # Patterns
  date_pattern: 'Y-m-d H:i:s'
  price_format: '%.2f EUR'
```

### Complex Value Aliases

```yaml
aliases:
  # Database connection
  db_connection:
    host: 'localhost'
    port: 3306
    database: 'products'
    username: 'db_user'
    
  # API endpoints
  api_endpoints:
    products: '/api/v1/products'
    categories: '/api/v1/categories'
    attributes: '/api/v1/attributes'
    
  # Transformation settings
  csv_options:
    delimiter: ';'
    enclosure: '"'
    escape: '\\'
    header: true
```

## Environment Variables

Use environment variables for sensitive information that shouldn't be stored in configuration files.

### Setting Environment Variables

**Linux/macOS:**
```bash
export AKENEO_CLIENT_ID="your_client_id"
export AKENEO_CLIENT_SECRET="your_client_secret"
export AKENEO_USERNAME="api_user"
export AKENEO_PASSWORD="secure_password"
```

**Windows:**
```cmd
set AKENEO_CLIENT_ID=your_client_id
set AKENEO_CLIENT_SECRET=your_client_secret
set AKENEO_USERNAME=api_user
set AKENEO_PASSWORD=secure_password
```

### Using Environment Variables in Configuration

```yaml
account:
  - name: "akeneo-secure"
    domain: "https://your-akeneo.com"
    client_id: "${AKENEO_CLIENT_ID}"
    client_secret: "${AKENEO_CLIENT_SECRET}"
    username: "${AKENEO_USERNAME}"
    password: "${AKENEO_PASSWORD}"
```

### Docker Environment Variables

When using Docker, pass environment variables:

```bash
docker-compose run -e AKENEO_CLIENT_ID=your_id -e AKENEO_CLIENT_SECRET=your_secret fpm php bin/console transformation --file config.yaml
```

Or define them in `docker-compose.yml`:

```yaml
services:
  fpm:
    build: ./docker/fpm
    environment:
      - AKENEO_CLIENT_ID=${AKENEO_CLIENT_ID}
      - AKENEO_CLIENT_SECRET=${AKENEO_CLIENT_SECRET}
      - AKENEO_USERNAME=${AKENEO_USERNAME}
      - AKENEO_PASSWORD=${AKENEO_PASSWORD}
```

## Security Best Practices

### Credential Management

**❌ Don't do this:**
```yaml
account:
  - name: "akeneo"
    username: "admin"
    password: "password123"  # Plain text password in config
```

**✅ Do this instead:**
```yaml
account:
  - name: "akeneo"
    username: "${AKENEO_USERNAME}"
    password: "${AKENEO_PASSWORD}"  # Environment variable
```

### File Permissions

Protect configuration files containing sensitive information:

```bash
# Make config files readable only by owner
chmod 600 config/production.yaml

# Protect entire config directory
chmod 700 config/
```

### Separate Configuration Files

Use separate configuration files for different environments:

```
config/
├── development.yaml    # Development settings
├── staging.yaml       # Staging environment
├── production.yaml    # Production credentials
└── common.yaml        # Shared settings
```

### Git Security

Never commit sensitive configuration files:

```gitignore
# .gitignore
config/production.yaml
config/staging.yaml
config/*.secret.yaml
.env
*.key
*.pem
```

Create template files instead:

```yaml
# config/production.yaml.template
account:
  - name: "akeneo-prod"
    domain: "REPLACE_WITH_ACTUAL_DOMAIN"
    client_id: "REPLACE_WITH_CLIENT_ID"
    client_secret: "REPLACE_WITH_CLIENT_SECRET"
    username: "REPLACE_WITH_USERNAME"
    password: "REPLACE_WITH_PASSWORD"
```

## Advanced Configuration Options

### SSL/TLS Configuration

```yaml
account:
  - name: "secure-api"
    domain: "https://secure-api.example.com"
    username: "api_user"
    password: "secure_password"
    ssl_options:
      verify_peer: true
      verify_host: true
      cafile: "/path/to/ca-bundle.crt"
      local_cert: "/path/to/client.pem"
      local_pk: "/path/to/private.key"
```

### Proxy Configuration

```yaml
account:
  - name: "api-through-proxy"
    domain: "https://api.example.com"
    username: "api_user"
    password: "secure_password"
    proxy:
      host: "proxy.company.com"
      port: 8080
      username: "proxy_user"
      password: "proxy_password"
      type: "http"  # or "socks5"
```

### Timeout and Retry Settings

```yaml
account:
  - name: "resilient-api"
    domain: "https://api.example.com"
    username: "api_user"
    password: "secure_password"
    timeout: 60
    connect_timeout: 10
    retry:
      max_attempts: 3
      delay: 5
      backoff_multiplier: 2
```

### Custom Headers

```yaml
account:
  - name: "api-with-headers"
    domain: "https://api.example.com"
    username: "api_user"
    password: "secure_password"
    headers:
      "User-Agent": "Parsable-File-Multi-Tool/1.0"
      "X-API-Version": "v2"
      "Accept": "application/json"
```

## Configuration Validation

### Required Fields Validation

```yaml
# Validate that required fields exist
pipeline:
  input:
    reader:
      type: csv
      filename: 'products.csv'
      validation:
        required_fields: [sku, name, price]
        
  actions:
    validate_data:
      action: statement
      conditions:
        - field: sku
          operator: 'NOT_EMPTY'
        - field: price
          operator: 'NUMERIC'
        - field: price
          operator: 'GREATER_THAN'
          value: 0
```

### Data Type Validation

```yaml
context:
  validation_rules:
    sku:
      type: 'string'
      pattern: '^[A-Z0-9-]+$'
      max_length: 50
    price:
      type: 'numeric'
      min_value: 0
      max_value: 99999.99
    email:
      type: 'email'
      required: true
```

## Configuration Examples

### E-commerce Product Import

```yaml
aliases:
  source_file: 'product_export.csv'
  processed_file: 'akeneo_import.csv'

account:
  - name: "akeneo-prod"
    domain: "${AKENEO_DOMAIN}"
    client_id: "${AKENEO_CLIENT_ID}"
    client_secret: "${AKENEO_CLIENT_SECRET}"
    username: "${AKENEO_USERNAME}"
    password: "${AKENEO_PASSWORD}"

context:
  locale: "en_US"
  currency: "USD"
  default_family: "default"
  import_date: "{{ 'now'|date('Y-m-d H:i:s') }}"

pipeline:
  input:
    reader:
      type: csv
      filename: 'source_file'
      options:
        delimiter: ','
        header: true
        
  actions:
    add_metadata:
      action: expand
      fields:
        imported_at: '%import_date%'
        locale: '%locale%'
        currency: '%currency%'
        
  output:
    writer:
      type: csv
      filename: 'processed_file'
```

### Multi-Environment API Integration

```yaml
context:
  environments:
    development:
      api_url: "https://dev-api.example.com"
      batch_size: 10
      debug: true
      
    production:
      api_url: "https://api.example.com"
      batch_size: 1000
      debug: false

# Select environment via command line or environment variable
current_env: "${ENVIRONMENT:-development}"

account:
  - name: "api-connection"
    domain: '%environments.%current_env%.api_url%'
    username: "${API_USERNAME}"
    password: "${API_PASSWORD}"

pipeline:
  input:
    reader:
      type: api
      account: "api-connection"
      endpoint: "/products"
      batch_size: '%environments.%current_env%.batch_size%'
```

## Troubleshooting Configuration

### Common Configuration Errors

**Invalid YAML syntax:**
```bash
# Validate YAML syntax
python -c "import yaml; yaml.safe_load(open('config.yaml'))"
```

**Missing environment variables:**
```bash
# Check if environment variables are set
echo $AKENEO_CLIENT_ID
env | grep AKENEO
```

**Account connection issues:**
```bash
# Test with debug mode
bin/docker/console transformation --file config.yaml --debug
```

### Configuration Debugging

Enable debug mode to see resolved configuration values:

```bash
bin/docker/console transformation \
  --file config.yaml \
  --source data \
  --workpath output \
  --debug \
  --showMappings
```

### Validation Tools

Create a configuration validation script:

```php
<?php
// validate-config.php
$config = yaml_parse_file($argv[1]);

// Check required sections
$required = ['pipeline'];
foreach ($required as $section) {
    if (!isset($config[$section])) {
        echo "Missing required section: $section\n";
        exit(1);
    }
}

// Validate account configurations
if (isset($config['account'])) {
    foreach ($config['account'] as $account) {
        if (!isset($account['name']) || !isset($account['domain'])) {
            echo "Account missing required fields: name, domain\n";
            exit(1);
        }
    }
}

echo "Configuration is valid\n";
```

## Related Topics

### Getting Started Workflow
- **[Installation Guide](./installation.md)** - Complete setup instructions and environment preparation
- **[Quick Start Guide](./quick-start.md)** - Your first transformation with configuration examples
- **[Getting Started Overview](./index.md)** - Complete getting started roadmap and learning path

### Core Configuration Directives
- **[Context Directive](../directives/context.md)** - Dynamic variables, environment settings, and parameter management
- **[Aliases Directive](../directives/aliases.md)** - Reusable references, file paths, and configuration shortcuts
- **[Pipeline Configuration](../directives/pipelines.md)** - Data processing workflows and transformation pipelines
- **[Mapping Directive](../directives/mapping.md)** - Field mapping, value transformation, and lookup tables

### Security and Credential Management
- **[Environment Variables](./configuration.md#environment-variables)** - Secure credential storage and environment-specific settings
- **[Security Best Practices](./configuration.md#security-best-practices)** - Protect sensitive information and secure configurations
- **[File Permissions](./configuration.md#file-permissions)** - Proper file and directory security settings
- **[Credential Management](./configuration.md#credential-management)** - API keys, passwords, and authentication setup

### Data Sources and Integration
- **[Data Sources](../data_source/reader.md)** - Configure input data sources and connection parameters
- **[Data Writers](../data_source/writer.md)** - Set up output destinations and export configurations
- **[API Integration](../user-guide/transformations.md#api-integration)** - External API connections and authentication
- **[Converters](../converters/)** - Data format conversion and specialized processing

### Development and Debugging
- **[CLI Commands](../reference/cli-commands.md)** - Command-line options, configuration flags, and execution parameters
- **[Debugging Guide](../user-guide/debugging.md)** - Debug configuration issues and troubleshoot setup problems
- **[Development Setup](../developer-guide/contributing.md#development-setup)** - Advanced development environment configuration
- **[Extension Development](../developer-guide/extending.md)** - Custom configuration options and extension parameters

### Advanced Configuration Topics
- **[Multi-step Transformations](../directives/transformation_steps.md)** - Complex workflow configuration and step orchestration
- **[Performance Optimization](../user-guide/debugging.md#performance-optimization-guidelines)** - Configuration tuning for large datasets and high performance
- **[Error Handling](../user-guide/debugging.md#common-error-scenarios-and-solutions)** - Configuration error handling and recovery strategies
- **[Batch Processing](../user-guide/transformations.md#batch-processing)** - Configure batch operations and throughput optimization

### Practical Examples and Patterns
- **[Configuration Examples](../examples/configuration-patterns.md)** - Real-world configuration examples and best practices
- **[Environment Configuration](./configuration.md#environment-variables)** - Multi-environment configuration and deployment patterns
- **[Integration Examples](../examples/integration-patterns.md)** - External system integration configuration patterns
- **[Security Best Practices](./configuration.md#security-best-practices)** - Secure configuration patterns and credential management

### System Architecture and Components
- **[Architecture Overview](../developer-guide/architecture.md)** - Understanding system components and configuration relationships
- **[System Requirements](./installation.md#system-requirements)** - Hardware and software requirements for optimal configuration
- **[Docker Configuration](./installation.md#docker-installation)** - Containerized deployment and configuration management
- **[Native Installation](./installation.md#native-php-installation)** - Direct system installation and configuration

## See Also

- **[User Guide](../user-guide/)** - Comprehensive usage documentation and configuration workflows
- **[Reference Documentation](../reference/)** - Complete technical reference and configuration options
- **[Developer Guide](../developer-guide/)** - Advanced configuration and customization topics
- **[Transformation Examples](../examples/)** - Practical configuration examples and real-world use cases