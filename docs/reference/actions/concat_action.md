
# Concat Action

## Overview

The concat action allows you to combine the values of multiple fields into a single field using a specified format string. It's essential for creating composite values, generating display labels, and merging data from different sources into unified fields.

## Syntax

```yaml
actions:
  - action: concat
    key: target_field
    format: "format_string_with_%FIELD_NAME%_placeholders"
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| key | string | Yes | - | Target field name where the concatenated result will be stored |
| format | string | Yes | - | Format string with field placeholders |

### Parameter Details

#### key
The name of the target field where the concatenated result will be stored.

- **Format:** String field name
- **Example:** `"label-nl_BE"`
- **Behavior:** If the field already exists, it will be overwritten with the concatenated result

#### format
The format string that defines how fields should be combined. Uses placeholder syntax to reference field values.

- **Format:** String with `%FIELD_NAME%` placeholders
- **Example:** `"%label-nl_BE% (%code%)"`
- **Behavior:** Placeholders are replaced with actual field values during execution

## Examples

### Basic Field Concatenation

```yaml
actions:
  - action: concat
    key: label-nl_BE
    format: '%label-nl_BE% (%code%)'
```

**Input:**
```json
{
  "label-nl_BE": "Product A",
  "code": "123456"
}
```

**Output:**
```json
{
  "label-nl_BE": "Product A (123456)",
  "code": "123456"
}
```

### Creating Full Names

```yaml
actions:
  - action: concat
    key: full_name
    format: '%first_name% %last_name%'
```

**Input:**
```json
{
  "first_name": "John",
  "last_name": "Doe"
}
```

**Output:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "full_name": "John Doe"
}
```

### Complex Format Strings

```yaml
actions:
  - action: concat
    key: product_description
    format: '%brand% - %model% (SKU: %sku%) - $%price%'
```

**Input:**
```json
{
  "brand": "TechCorp",
  "model": "Widget Pro",
  "sku": "TC-WP-001",
  "price": "299.99"
}
```

**Output:**
```json
{
  "brand": "TechCorp",
  "model": "Widget Pro",
  "sku": "TC-WP-001",
  "price": "299.99",
  "product_description": "TechCorp - Widget Pro (SKU: TC-WP-001) - $299.99"
}
```

### Address Concatenation

```yaml
actions:
  - action: concat
    key: full_address
    format: '%street%, %city%, %state% %zip_code%'
```

**Input:**
```json
{
  "street": "123 Main St",
  "city": "Springfield",
  "state": "IL",
  "zip_code": "62701"
}
```

**Output:**
```json
{
  "street": "123 Main St",
  "city": "Springfield",
  "state": "IL",
  "zip_code": "62701",
  "full_address": "123 Main St, Springfield, IL 62701"
}
```

## Use Cases

### Use Case 1: Display Label Generation
Create user-friendly display labels by combining multiple data fields into readable formats.

### Use Case 2: Unique Identifier Creation
Generate composite keys or identifiers by concatenating multiple field values.

### Use Case 3: Data Export Formatting
Format data for export by combining related fields into single columns for better readability.

## Common Issues and Solutions

### Issue: Missing Field Values

**Symptoms:** Concatenated result contains empty spaces or "null" text where field values should be.

**Cause:** Referenced fields in the format string don't exist or contain null values.

**Solution:** Ensure all referenced fields exist or use default values before concatenation.

```yaml
# Set default values before concatenation
actions:
  - action: copy
    from: optional_field
    to: safe_field
    default: "N/A"
  - action: concat
    key: result
    format: '%required_field% - %safe_field%'
```

### Issue: Special Characters in Format String

**Symptoms:** Format string is not processed correctly due to special characters.

**Cause:** YAML parsing issues with special characters in the format string.

**Solution:** Use proper YAML quoting for strings with special characters.

```yaml
# Properly quote format strings with special characters
actions:
  - action: concat
    key: formatted_data
    format: "%field1% & %field2% @ %field3%"
```

### Issue: Overwriting Important Data

**Symptoms:** Original field data is lost when using the same field as both source and target.

**Cause:** The key parameter overwrites the original field value.

**Solution:** Use a different target field name or create a backup first.

```yaml
# Create backup before overwriting
actions:
  - action: copy
    from: original_field
    to: backup_field
  - action: concat
    key: original_field
    format: '%original_field% (modified)'
```

## Performance Considerations

- String concatenation is generally fast for small to medium-sized strings
- Performance may degrade with very long format strings or many field references
- Consider the memory impact when concatenating large text fields
- Batch similar concatenation operations when possible

## Related Topics

### Core String Processing Actions
- **[Copy Action](./copy_action.md)** - Set default values before concatenation and create backup fields
- **[Format Action](./format_action.md)** - Format individual fields before concatenation and apply string transformations
- **[Statement Action](./statement_action.md)** - Add conditional logic around concatenation and validate field values
- **[Debug Action](./debug_action.md)** - Debug concatenation results and troubleshoot format strings

### Field Manipulation Actions
- **[Rename Action](./rename_action.md)** - Rename concatenated fields and organize results
- **[Remove Action](./remove_action.md)** - Remove source fields after concatenation and clean up data
- **[Retain Action](./retain_action.md)** - Keep only concatenated fields and remove unnecessary data
- **[Calculate Action](./calculate_action.md)** - Use calculated values in concatenation and combine numeric results

### Value Processing Actions
- **[Value Mapping Action](./value_mapping_in_list_action.md)** - Map values before concatenation and standardize inputs
- **[Key Mapping Action](./key_mapping_action.md)** - Use concatenated values as mapping keys
- **[Date Time Action](./date_time_action.md)** - Format dates before concatenation and create time-based labels

### Configuration and Context
- **[Context Directive](../directives/context.md)** - Define concatenation templates and format patterns using context variables
- **[Mapping Directive](../directives/mapping.md)** - Use mappings for value standardization before concatenation
- **[Pipeline Configuration](../directives/pipelines.md)** - Integrate concatenation in data processing workflows
- **[Aliases Directive](../directives/aliases.md)** - Define reusable field aliases for consistent concatenation

### Data Processing and Transformation
- **[Transformation Steps](../directives/transformation_steps.md)** - Multi-step workflows with field concatenation and data preparation
- **[String Handling](../../../user-guide/transformations.md#string-processing)** - Best practices for string operations and concatenation
- **[Field Management](../../../user-guide/transformations.md#field-management)** - Organize concatenated fields and manage data structure
- **[Data Type Handling](../../../user-guide/transformations.md#data-types)** - Understanding data types in concatenation operations

### Debugging and Optimization
- **[Debugging Guide](../../../user-guide/debugging.md)** - Debug concatenation operations and troubleshoot format issues
- **[Performance Optimization](../../../user-guide/debugging.md#performance-optimization-guidelines)** - Optimize concatenation performance for large datasets
- **[CLI Commands](../cli-commands.md)** - Test concatenation operations with limited data and debug mode
- **[Error Handling](../../../user-guide/debugging.md#common-error-scenarios-and-solutions)** - Handle concatenation errors and missing field values

## See Also

- **[Actions Reference](./index.md)** - Complete list of all available actions and string processing capabilities
- **[Transformation Examples](../../../examples/)** - Practical concatenation examples and common patterns
- **[Quick Start Guide](../../../getting-started/quick-start.md)** - Basic string processing techniques for beginners
- **[Advanced String Processing](../../../examples/advanced-workflows.md)** - Complex concatenation patterns and template usage

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*