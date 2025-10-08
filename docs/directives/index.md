# Directives Reference

Directives control how transformations are processed and provide configuration options for various aspects of the data processing pipeline.

## Available Directives

### Core Directives
- [Aliases](./aliases.md) - Define reusable aliases for common configurations
- [Context](./context.md) - Set context variables and environment configuration
- [Converters](./converters.md) - Configure data format converters
- [List](./list.md) - Handle list and array processing
- [Mapping](./mapping.md) - Define field mappings and transformations
- [Pipelines](./pipelines.md) - Configure transformation pipelines
- [Transformation Steps](./transformation_steps.md) - Define multi-step transformations

## Quick Reference

### Basic Usage
```yaml
directives:
  context:
    environment: production
  mapping:
    source_field: target_field
```

### Advanced Configuration
```yaml
directives:
  pipelines:
    - name: data_processing
      steps:
        - action: validate
        - action: transform
        - action: output
```

## Related Topics
- [Configuration Guide](../../getting-started/configuration.md)
- [User Guide](../../user-guide/)
- [Actions Reference](../actions/)