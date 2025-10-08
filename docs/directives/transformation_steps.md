# Transformation Steps Directive

## Overview

The transformation steps directive orchestrates complex, multi-file transformation workflows by defining a sequence of transformation files to execute. This directive enables modular, reusable transformation components that can be parameterized and combined to create sophisticated data processing pipelines.

## Syntax

```yaml
transformation_steps:
  - step_file.yaml
  - run: template_file.yaml
    once_with:
      parameter: value
  - run: template_file.yaml
    with:
      parameter:
        - value1
        - value2
```

## Configuration Options

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| step_file.yaml | string | Yes* | - | Direct reference to transformation file |
| run | string | Yes* | - | Template file to execute with parameters |
| once_with | object | No | - | Single parameter set for template execution |
| with | object | No | - | Multiple parameter sets for template execution |

*Either direct file reference or `run` with parameters is required.

### Configuration Details

#### Direct File Reference
- Simple string reference to a transformation YAML file
- File is executed as-is without parameter substitution
- Path can be relative or use context variables

#### run (Template Execution)
- Specifies a template file that accepts parameters
- Template file contains placeholder variables that are substituted during execution
- Enables reusable transformation components

#### once_with (Single Execution)
- Executes the template once with the provided parameter set
- Parameters are substituted into template placeholders
- Useful for single-instance operations with specific configuration

#### with (Multiple Execution)
- Executes the template multiple times, once for each parameter set
- Each array item becomes a separate execution with parameter substitution
- Enables batch processing with different configurations

## Examples

### Basic Sequential Steps

```yaml
# Execute transformation files in sequence
transformation_steps:
  - fetch_source_data.yaml
  - clean_and_validate.yaml
  - transform_format.yaml
  - push_to_destination.yaml
```

### Parameterized Template Execution

```yaml
# Execute template with specific parameters
transformation_steps:
  - run: akeneo/jsonl/query_akeneo-entities.yaml
    once_with:
      endpoint: products
      query: '%app_querystring%'
  - process-akeneo-product.yaml
  - run: akeneo/jsonl/push_akeneo-entities.yaml
    once_with:
      endpoint: products
```

### Batch Processing with Multiple Parameters

```yaml
# Execute template multiple times with different parameters
transformation_steps:
  - run: akeneo/jsonl/pull_akeneo-reference-entities.yaml
    with:
      endpoint:
        - brands
        - colors
        - materials
        - categories
```

### Complex Multi-Step Workflow

```yaml
# Comprehensive transformation workflow
transformation_steps:
  # Initial data fetch
  - run: fetch_api_data.yaml
    once_with:
      endpoint: products
      query: '%s?search={"enabled":[{"operator":"=","value":true}]}'
  
  # Process different entity types
  - run: process_entity_data.yaml
    with:
      entity_type:
        - products
        - categories
        - attributes
  
  # Final processing and output
  - validate_processed_data.yaml
  - generate_reports.yaml
  - run: push_to_systems.yaml
    with:
      target_system:
        - production_api
        - backup_storage
```

## Use Cases

### Use Case 1: Modular Transformation Pipelines
Break complex transformations into smaller, reusable components that can be combined and parameterized.

### Use Case 2: Batch Processing
Execute the same transformation logic across multiple data sets, endpoints, or configurations.

### Use Case 3: Template-Based Workflows
Create reusable transformation templates that can be configured for different environments or use cases.

## Behavior and Processing

### Processing Order
Transformation steps are executed sequentially in the order they appear in the configuration.

### Parameter Substitution
- Parameters are substituted into template files before execution
- Uses standard variable substitution syntax (`%parameter_name%`)
- Parameters are scoped to individual step execution

### Error Handling
- Execution stops on the first step that encounters an error
- Failed steps can be logged for debugging
- Some configurations support error tolerance and continuation

## Common Patterns

### Pattern 1: ETL Workflow
```yaml
transformation_steps:
  - extract_source_data.yaml
  - transform_data_format.yaml
  - load_to_destination.yaml
```

### Pattern 2: API Integration Pipeline
```yaml
transformation_steps:
  - run: api_fetch_template.yaml
    once_with:
      endpoint: products
      filters: active_only
  - process_api_response.yaml
  - run: api_push_template.yaml
    once_with:
      endpoint: processed_products
```

### Pattern 3: Multi-Entity Processing
```yaml
transformation_steps:
  - run: entity_processor.yaml
    with:
      entity:
        - customers
        - orders
        - products
        - categories
```

## Template Files

### Template Structure
Template files are standard transformation YAML files with parameter placeholders:

```yaml
# template_file.yaml
context:
  api_endpoint: '%endpoint%'
  query_string: '%query%'

pipeline:
  input:
    http:
      endpoint: '%endpoint%'
      query: '%query%'
  # ... rest of transformation
```

### Parameter Substitution
Parameters are substituted using the `%parameter_name%` syntax throughout the template file.

## Common Issues and Solutions

### Issue: Template File Not Found

**Symptoms:** Error messages about missing template files during step execution.

**Cause:** Incorrect file path or missing template file.

**Solution:** Verify template file paths and ensure files exist.

```yaml
transformation_steps:
  - run: templates/api_fetch.yaml  # Ensure path is correct
    once_with:
      endpoint: products
```

### Issue: Parameter Not Substituted

**Symptoms:** Literal `%parameter_name%` appears in executed transformation instead of actual values.

**Cause:** Parameter name mismatch between step configuration and template file.

**Solution:** Ensure parameter names match exactly between configuration and template.

```yaml
# Step configuration
transformation_steps:
  - run: template.yaml
    once_with:
      api_endpoint: products  # Parameter name

# Template file (template.yaml)
context:
  endpoint: '%api_endpoint%'  # Must match exactly
```

### Issue: Step Execution Order Problems

**Symptoms:** Steps fail because they depend on outputs from later steps.

**Cause:** Incorrect step ordering in the transformation_steps array.

**Solution:** Order steps based on their dependencies.

```yaml
transformation_steps:
  - fetch_dependencies.yaml    # First: get required data
  - process_main_data.yaml     # Then: process main workflow
  - finalize_output.yaml       # Finally: complete processing
```

## Best Practices

- Use descriptive names for transformation step files
- Keep individual step files focused on single responsibilities
- Create reusable templates for common operations
- Document parameter requirements for template files
- Test step sequences with sample data before production use
- Use consistent parameter naming conventions across templates
- Organize step files in logical directory structures

## Related Directives

- [Pipeline](./pipelines.md) - Individual transformation processing workflows
- [Context](./context.md) - For defining variables used across steps
- [Aliases](./aliases.md) - For file path references in step files

## See Also

- [Directive Overview](../directives.md)
- [Transformation Templates](../helpers/transformation_templates.md)
- [Transformation Guide](../user-guide/transformations.md)
- [Configuration Guide](../getting-started/configuration.md)

---

*Last updated: 2024-12-19*
*Category: reference*
*Directive Type: orchestration*

