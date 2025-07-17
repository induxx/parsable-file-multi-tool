# Directive Reference

## Overview

Directives are configuration components that define how data transformations are structured and executed in the parsable-file-multi-tool. Each directive serves a specific purpose in the transformation pipeline, from defining data sources to configuring processing steps.

## Directive Categories

### Configuration Directives
These directives define the environment and setup for transformations:

- **[Context](../directives/context.md)** - Environment variables, connections, and shared configurations
- **[Aliases](../directives/aliases.md)** - Reusable file path placeholders

### Data Source Directives
These directives define collections of data for reference and processing:

- **[List](../directives/list.md)** - Multi-dimensional data collections and lookup tables
- **[Mapping](../directives/mapping.md)** - Simple key-value data sets for translation and mapping

### Processing Directives
These directives define how data is transformed and processed:

- **[Pipeline](../directives/pipelines.md)** - Sequential data processing workflows
- **[Converters](../directives/converters.md)** - Data format transformation between systems
- **[Transformation Steps](../directives/transformation_steps.md)** - Multi-step transformation orchestration

## Quick Reference

| Directive | Purpose | Use When |
|-----------|---------|----------|
| [Context](../directives/context.md) | Define variables and connections | Setting up environment-specific configurations |
| [Aliases](../directives/aliases.md) | Create file path shortcuts | Reusing file references across configurations |
| [List](../directives/list.md) | Store structured data arrays | Need lookup tables or reference data |
| [Mapping](../directives/mapping.md) | Define key-value translations | Simple field or value mapping operations |
| [Pipeline](../directives/pipelines.md) | Process data through stages | Standard ETL operations and data transformation |
| [Converters](../directives/converters.md) | Transform data formats | Converting between API and file formats |
| [Transformation Steps](../directives/transformation_steps.md) | Orchestrate complex workflows | Multi-file, multi-step transformation processes |

## Common Directive Combinations

### Basic Data Processing
```yaml
# Simple file transformation
aliases:
  input_data: 'source.csv'
  output_data: 'processed.csv'

pipeline:
  input:
    reader:
      type: csv
      filename: 'input_data'
  actions:
    clean_data:
      action: retain
      keys: [id, name, email]
  output:
    writer:
      type: csv
      filename: 'output_data'
```

### API Integration with Lookup Data
```yaml
# API data processing with reference lookups
context:
  api_connection: production_api
  
list:
  - name: status_mappings
    source: status_lookup.csv
    source_command: key_value_pair
    options:
      key: old_status
      value: new_status

pipeline:
  input:
    http:
      type: rest_api
      account: '%api_connection%'
      endpoint: products
      converter: 'akeneo/product/api'
  actions:
    map_statuses:
      action: key_mapping
      list: status_mappings
  output:
    writer:
      type: csv
      filename: processed_products.csv
      converter: 'akeneo/product/csv'
```

### Multi-Step Transformation Workflow
```yaml
# Complex multi-step processing
context:
  environment: production
  
transformation_steps:
  - run: fetch_source_data.yaml
    once_with:
      endpoint: products
  - run: process_data.yaml
  - run: push_results.yaml
    once_with:
      target: destination_system
```

## Best Practices

### Directive Organization
- Group related directives together in configuration files
- Use consistent naming conventions across directives
- Document the purpose and dependencies of each directive

### Configuration Management
- Use context variables for environment-specific settings
- Leverage aliases for frequently referenced file paths
- Keep mapping and list data in separate, reusable files

### Performance Considerations
- Use appropriate data structures (lists vs mappings) for your use case
- Consider memory implications of large reference data sets
- Optimize pipeline actions for streaming when processing large files

### Error Handling
- Validate that referenced files and connections exist
- Use descriptive names that make debugging easier
- Test directive combinations with sample data before production use

## Related Topics

- [Actions Reference](../actions/) - Processing operations used within pipelines
- [Data Sources](../../../data_source/) - Input and output configuration options
- [Configuration Guide](../../../getting-started/configuration.md) - Setting up transformation configurations
- [Transformation Guide](../../../user-guide/transformations.md) - Complete workflow examples

---

*Last updated: 2024-12-19*
*Category: reference*
*Documentation Type: index*