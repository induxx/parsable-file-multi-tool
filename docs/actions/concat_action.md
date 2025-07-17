
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

## Related Actions

- [Copy Action](./copy_action.md) - Set default values before concatenation
- [Format Action](./format_action.md) - Format individual fields before concatenation
- [Statement Action](./statement_action.md) - Add conditional logic around concatenation

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [String Handling](../user-guide/string-handling.md)
- [Field Management](../user-guide/field-management.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*