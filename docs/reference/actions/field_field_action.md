
# Filter Field Action

## Overview

The filter_field action allows you to filter fields based on exact matches or pattern matching such as starts_with, ends_with, and contains. It provides flexible field manipulation capabilities including field removal, retention, and value clearing, making it essential for dynamic field management workflows.

## Syntax

```yaml
actions:
  - action: filter_field
    fields: [field1, field2]
    reverse: false
    clear_value: false

# Or using pattern matching
actions:
  - action: filter_field
    starts_with: 'prefix_'
    ends_with: '_suffix'
    contains: 'pattern'
    reverse: false
    clear_value: false
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| fields | array | No* | - | Array of exact field names to filter |
| starts_with | string | No* | - | Filter fields that start with this string |
| ends_with | string | No* | - | Filter fields that end with this string |
| contains | string | No* | - | Filter fields that contain this string |
| reverse | boolean | No | false | Reverse the filter logic (keep non-matching fields) |
| clear_value | boolean | No | false | Clear values of matching fields instead of removing them |

*At least one filtering parameter must be provided

### Parameter Details

#### fields
An array of exact field names to filter. When specified, only these exact field names will be affected.

- **Format:** Array of strings
- **Example:** `["enabled", "parent", "status"]`
- **Behavior:** Exact field name matching

#### starts_with
Filter fields whose names start with the specified string.

- **Format:** String
- **Example:** `"erp_"`, `"temp_"`
- **Behavior:** Pattern matching from the beginning of field names

#### ends_with
Filter fields whose names end with the specified string.

- **Format:** String
- **Example:** `"_erp"`, `"_temp"`
- **Behavior:** Pattern matching from the end of field names

#### contains
Filter fields whose names contain the specified string anywhere.

- **Format:** String
- **Example:** `"section-"`, `"_internal_"`
- **Behavior:** Pattern matching anywhere in field names

#### reverse
When true, reverses the filter logic to keep non-matching fields instead of removing them.

- **Format:** Boolean
- **Example:** `true`, `false`
- **Behavior:** Inverts the filtering logic

#### clear_value
When true, clears the values of matching fields instead of removing the fields entirely.

- **Format:** Boolean
- **Example:** `true`, `false`
- **Behavior:** Sets field values to empty/null instead of removing fields

## Examples

### Exact Field Removal

```yaml
actions:
  - action: filter_field
    fields: ['enabled', 'parent']
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "enabled": true,
  "parent": "category_1",
  "price": 29.99,
  "status": "active"
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "price": 29.99,
  "status": "active"
}
```

### Reverse Mode (Retain Fields)

```yaml
actions:
  - action: filter_field
    fields: ['product_name', 'price']
    reverse: true
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "enabled": true,
  "parent": "category_1",
  "price": 29.99,
  "status": "active"
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "price": 29.99
}
```

### Pattern Matching - Starts With

```yaml
actions:
  - action: filter_field
    starts_with: 'erp_'
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "erp_code": "ERP001",
  "erp_category": "electronics",
  "erp_status": "active",
  "price": 29.99
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "price": 29.99
}
```

### Pattern Matching - Ends With

```yaml
actions:
  - action: filter_field
    ends_with: '_temp'
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "processing_temp": "in_progress",
  "validation_temp": "pending",
  "price": 29.99,
  "status": "active"
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "price": 29.99,
  "status": "active"
}
```

### Pattern Matching - Contains

```yaml
actions:
  - action: filter_field
    contains: 'internal'
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "internal_code": "INT001",
  "price_internal": 15.50,
  "public_price": 29.99,
  "status": "active"
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "public_price": 29.99,
  "status": "active"
}
```

### Clear Values Instead of Removing Fields

```yaml
actions:
  - action: filter_field
    contains: 'sensitive'
    clear_value: true
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "sensitive_data": "confidential_info",
  "user_sensitive_info": "private_data",
  "price": 29.99
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "sensitive_data": null,
  "user_sensitive_info": null,
  "price": 29.99
}
```

## Use Cases

### Use Case 1: Temporary Field Cleanup
Remove temporary or processing fields that are no longer needed after transformation.

### Use Case 2: Data Privacy Compliance
Clear or remove fields containing sensitive information before data export.

### Use Case 3: System Integration Preparation
Filter out system-specific fields when preparing data for external system integration.

## Common Issues and Solutions

### Issue: No Fields Match Pattern

**Symptoms:** Filter action has no effect on the data.

**Cause:** The specified pattern doesn't match any field names in the data.

**Solution:** Verify the pattern matches actual field names and check case sensitivity.

```yaml
# Debug field names first
actions:
  - action: debug
  - action: filter_field
    starts_with: 'correct_prefix_'
```

### Issue: Too Many Fields Removed

**Symptoms:** More fields are removed than expected.

**Cause:** The pattern is too broad and matches unintended fields.

**Solution:** Use more specific patterns or combine multiple filtering criteria.

```yaml
# Use more specific patterns
actions:
  - action: filter_field
    starts_with: 'temp_processing_'  # More specific than just 'temp_'
```

### Issue: Reverse Logic Confusion

**Symptoms:** Opposite behavior than expected when using reverse parameter.

**Cause:** Misunderstanding of reverse logic - it inverts the filter behavior.

**Solution:** Remember that reverse=true keeps non-matching fields instead of removing them.

```yaml
# reverse=true keeps fields that DON'T match the pattern
actions:
  - action: filter_field
    starts_with: 'keep_'
    reverse: true  # This will remove fields starting with 'keep_'
```

## Performance Considerations

- Pattern matching operations are generally fast
- Complex patterns may have slight performance impact
- Field removal operations are very efficient
- Consider the number of fields when using broad patterns

## Related Actions

- [Remove Action](./remove_action.md) - Remove specific fields by name
- [Retain Action](./retain_action.md) - Keep only specific fields by name
- [Statement Action](./statement_action.md) - Add conditional logic around field filtering

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [Field Management](../../../user-guide/field-management.md)
- [Pattern Matching Guide](../../../user-guide/pattern-matching.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*
