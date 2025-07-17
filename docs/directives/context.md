# Context Directive

## Overview

The context directive defines environment variables, shared configurations, and resources required for executing transformation steps. It provides a centralized location for configuration details that are accessible throughout the entire transformation process, ensuring consistency and reducing redundancy across multiple steps.

## Syntax

```yaml
context:
  variable_name: value
  connection_name: resource_identifier
  debug:
    marker: debug_specification
```

## Configuration Options

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| variable_name | string/object | No | - | User-defined variable accessible throughout transformation |
| connection_name | string | No | - | Resource connection identifier for external systems |
| debug | object | No | - | Debug configuration for transformation monitoring |

### Configuration Details

#### Custom Variables
- Can be any valid YAML key-value pair
- Values can be strings, numbers, objects, or arrays
- Accessible throughout the transformation using `%variable_name%` syntax
- Case-sensitive variable names

#### Connection Identifiers
- Reference external system connections (APIs, databases)
- Must match configured resource names in your environment
- Used by transformation steps to establish connections

#### Debug Configuration
Special configuration object for debugging transformations:

```yaml
debug:
  marker: "filename.yaml:line_number"  # Capture specific line
  marker: "filename.yaml"              # Capture first output
  marker: "filename.yaml:id:item_id"   # Capture specific item
```

## Examples

### Basic Variable Definition

```yaml
# Simple context variables
context:
  environment: production
  batch_size: 1000
  output_format: csv
```

### API Connection Configuration

```yaml
# External system connections
context:
  akeneo_connection: target_resource
  akeneo_api_account_name: source_resource
  akeneo_read_connection: read_resource
  akeneo_write_connection: write_resource
```

### File and Format Configuration

```yaml
# File naming and formatting
context:
  akeneo_file_csv_filename: akeneo_full_products.jsonl
  product_file: akeneo_products.jsonl
  attribute_file: akeneo_attributes.jsonl
  datetime_file_format: 'ymd-His'
  date_format: 'YmdHis'
```

### Query String Configuration

```yaml
# API query configuration
context:
  querystring: '%s?scope=nuorder&search={"categories":[{"operator":"IN","value":["nuorder"]}],"sales_status":[{"operator":"NOT IN","value":["8","08"]}]}'

# Usage in pipeline
pipeline:
  input:
    http:
      type: rest_api
      account: target_resource
      endpoint: products
      method: GET
      limiters:
        querystring: '%querystring%'
```

### Debug Configuration

```yaml
# Debug marker examples
context:
  debug:
    marker: "get_products.yaml:5"        # Capture line 5
    # marker: "get_products.yaml"        # Capture first output
    # marker: "get_products.yaml:id:nike" # Capture specific item
```

## Use Cases

### Use Case 1: Environment-Specific Configuration
Define different settings for development, staging, and production environments.

### Use Case 2: API Integration
Centralize connection details and query parameters for external API interactions.

### Use Case 3: File Processing Configuration
Define consistent file naming patterns and format specifications.

## Behavior and Processing

### Processing Order
Context variables are resolved during configuration parsing and remain available throughout the entire transformation execution.

### Data Flow
Context variables can be referenced in any transformation step using the `%variable_name%` syntax.

### Variable Scope
All context variables are globally available across all transformation steps and pipeline configurations.

## Common Patterns

### Pattern 1: Environment Configuration
```yaml
context:
  environment: "{{ ENV }}"
  api_base_url: "https://api.{{ environment }}.example.com"
  batch_size: 500
```

### Pattern 2: Connection Management
```yaml
context:
  source_connection: source_system
  target_connection: target_system
  backup_connection: backup_system
```

## Reserved Context Keys

The following keys are reserved for internal use and should not be used as custom context keys:

| Reserved Key | Description |
|--------------|-------------|
| `transformation_file` | The initial transformation file path |
| `last_completed_operation_datetime` | Timestamp of last completed operation |
| `operation_create_datetime` | Timestamp when operation was created |
| `app_querystring` | Query string from application filters |
| `datetime_file_format` | Internal datetime formatting |
| `date_format` | Internal date formatting |
| `debug` | Debug flag and configuration |
| `try` | Retry limit configuration |
| `line` | Line number for debugging |
| `show_mappings` | Flag to display generated mappings |

## Common Issues and Solutions

### Issue: Variable Not Resolved

**Symptoms:** Variables appear as literal `%variable_name%` in output instead of resolved values.

**Cause:** Variable not defined in context or incorrect syntax.

**Solution:** Ensure variable is properly defined and referenced.

```yaml
# Correct variable definition and usage
context:
  api_endpoint: "https://api.example.com"

pipeline:
  input:
    http:
      endpoint: "%api_endpoint%/products"  # Correct syntax
```

### Issue: Reserved Key Conflict

**Symptoms:** Unexpected behavior or system errors.

**Cause:** Using reserved context keys for custom variables.

**Solution:** Use different variable names that don't conflict with reserved keys.

```yaml
# Avoid reserved keys
context:
  custom_debug_flag: true  # Instead of 'debug'
  transformation_name: "my_transform"  # Instead of 'transformation_file'
```

## Best Practices

- Use descriptive variable names that clearly indicate their purpose
- Group related variables together for better organization
- Avoid using reserved context keys for custom variables
- Document complex variable structures and their expected values
- Use environment-specific context files for different deployment environments
- Remove debug markers in production configurations for better performance

## Related Topics

### Core Configuration Directives
- **[Aliases Directive](./aliases.md)** - Create file path placeholders and reusable references that work with context variables
- **[Pipeline Configuration](./pipelines.md)** - Use context variables throughout pipeline definitions and data processing workflows
- **[Transformation Steps](./transformation_steps.md)** - Apply context variables across multi-step transformation configurations
- **[Mapping Directive](./mapping.md)** - Use context variables in mapping file paths and dynamic mapping configuration

### Data Processing Integration
- **[List Directive](./list.md)** - Reference context variables in list configurations and data collections
- **[Converters Directive](./converters.md)** - Use context variables in converter configurations and format specifications

### Context-Aware Actions
- **[Statement Action](../actions/statement_action.md)** - Use context variables in conditional logic and decision-making processes
- **[Debug Action](../actions/debug_action.md)** - Reference context variables in debug operations and conditional debugging
- **[Format Action](../actions/format_action.md)** - Use context variables in formatting operations and template strings
- **[Copy Action](../actions/copy_action.md)** - Apply context variables for default values and field assignments

### Data Transformation Actions
- **[Calculate Action](../actions/calculate_action.md)** - Use context variables for calculation parameters and mathematical constants
- **[Concat Action](../actions/concat_action.md)** - Reference context variables in concatenation templates and format strings
- **[Value Mapping Action](../actions/value_mapping_in_list_action.md)** - Use context variables to specify mapping sources and configurations
- **[Extension Action](../actions/extension_action.md)** - Pass context variables to custom extensions and external integrations

### Configuration and Setup
- **[Configuration Guide](../getting-started/configuration.md)** - Set up environment variables, secrets management, and context configuration
- **[Environment Setup](../getting-started/configuration.md#environment-variables)** - Configure environment-specific context variables and deployment settings
- **[Quick Start Guide](../getting-started/quick-start.md)** - Basic context variable usage and configuration examples
- **[Installation Guide](../getting-started/installation.md)** - Environment setup and context variable configuration

### API and External Integration
- **[API Integration](../user-guide/transformations.md#api-integration)** - Use context variables for API endpoints, authentication, and connection management
- **[Data Sources](../data_source/reader.md)** - Configure data source connections using context variables
- **[Data Writers](../data_source/writer.md)** - Set up output destinations with context variable configuration
- **[External Services](../user-guide/transformations.md#external-services)** - Manage external service connections and credentials

### Development and Debugging
- **[Debugging Guide](../user-guide/debugging.md)** - Use context variables for conditional debugging and environment-specific logging
- **[CLI Commands](../reference/cli-commands.md)** - Pass context variables through command-line options and environment settings
- **[Extension Development](../developer-guide/extending.md)** - Access context variables in custom extensions and development workflows
- **[Performance Optimization](../user-guide/debugging.md#performance-optimization-guidelines)** - Use context variables for performance tuning and resource management

### Security and Best Practices
- **[Security Best Practices](../user-guide/transformations.md#security)** - Secure context variable usage and credential management
- **[Secrets Management](../getting-started/configuration.md#secrets-management)** - Store sensitive context variables securely
- **[Environment Configuration](../getting-started/configuration.md#environment-configuration)** - Manage context variables across different environments
- **[Error Handling](../user-guide/debugging.md#common-error-scenarios-and-solutions)** - Handle context variable resolution errors and missing variables

## See Also

- **[Directives Reference](./index.md)** - Complete list of all available directives and configuration options
- **[Actions Reference](../actions/index.md)** - Actions that work with context variables and dynamic configuration
- **[Transformation Examples](../examples/)** - Practical context variable examples and configuration patterns
- **[Environment Configuration Guide](../examples/environment-setup.md)** - Advanced context variable strategies and deployment patterns

---

*Last updated: 2024-12-19*
*Category: reference*
*Directive Type: configuration*