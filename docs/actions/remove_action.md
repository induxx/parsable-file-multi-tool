
# Remove Action

## Overview

The remove action allows you to remove unwanted keys or fields from an item being processed. It's essential for data cleaning workflows where you need to eliminate unnecessary fields, reduce data size, or prepare data for specific output formats.

## Syntax

```yaml
actions:
  - action: remove
    keys: [field1, field2, field3]
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| keys | array | Yes | - | Array of field names to remove from the item |

### Parameter Details

#### keys
An array containing the names of fields to be removed from the processed item.

- **Format:** Array of strings
- **Example:** `["GROUP", "TYPE", "UID", "ATTRIBUTE_CODE"]`
- **Behavior:** All specified fields will be permanently removed from the item

## Examples

### Basic Field Removal

```yaml
actions:
  - action: remove
    keys: [GROUP, TYPE, UID, ATTRIBUTE_CODE, DUMP_TIMESTAMP, MEASUREMENT_FAMILY]
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "price": 29.99,
  "GROUP": "electronics",
  "TYPE": "gadget",
  "UID": "12345",
  "ATTRIBUTE_CODE": "WP001",
  "DUMP_TIMESTAMP": "2024-01-16T10:30:00Z",
  "MEASUREMENT_FAMILY": "metric"
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "price": 29.99
}
```

### Removing Temporary Fields

```yaml
actions:
  - action: remove
    keys: [temp_field, processing_flag, debug_info]
```

**Input:**
```json
{
  "customer_name": "John Doe",
  "order_total": 150.00,
  "temp_field": "temporary_data",
  "processing_flag": true,
  "debug_info": "processed_at_2024-01-16"
}
```

**Output:**
```json
{
  "customer_name": "John Doe",
  "order_total": 150.00
}
```

### Data Privacy Cleanup

```yaml
actions:
  - action: remove
    keys: [ssn, credit_card, internal_notes, employee_id]
```

**Input:**
```json
{
  "customer_name": "Jane Smith",
  "order_id": "ORD-001",
  "ssn": "123-45-6789",
  "credit_card": "4111-1111-1111-1111",
  "internal_notes": "VIP customer",
  "employee_id": "EMP-456"
}
```

**Output:**
```json
{
  "customer_name": "Jane Smith",
  "order_id": "ORD-001"
}
```

## Use Cases

### Use Case 1: Data Export Preparation
Remove internal or sensitive fields before exporting data to external systems or third parties.

### Use Case 2: Performance Optimization
Eliminate unnecessary fields to reduce data size and improve processing performance in downstream operations.

### Use Case 3: Data Privacy Compliance
Remove personally identifiable information (PII) or sensitive data fields to comply with privacy regulations.

## Common Issues and Solutions

### Issue: Removing Non-Existent Fields

**Symptoms:** No error occurs, but expected fields are not removed.

**Cause:** Field names in the keys array don't match actual field names in the data.

**Solution:** Verify field names match exactly, including case sensitivity.

```yaml
# Ensure exact field name matching
actions:
  - action: remove
    keys: [EXACT_FIELD_NAME, another_field]
```

### Issue: Accidentally Removing Required Fields

**Symptoms:** Downstream processes fail due to missing required fields.

**Cause:** Important fields were included in the removal list.

**Solution:** Review the keys array carefully and use conditional removal when needed.

```yaml
# Use conditional logic to preserve important fields
actions:
  - action: statement
    condition: "{{ environment != 'production' }}"
    then:
      - action: remove
        keys: [debug_field, test_data]
```

### Issue: Case Sensitivity Problems

**Symptoms:** Fields are not removed despite being listed in the keys array.

**Cause:** Field names have different capitalization than expected.

**Solution:** Check the exact case of field names in your data.

```yaml
# Match exact case
actions:
  - action: remove
    keys: [fieldName, FIELD_NAME, field_name]  # Different cases
```

## Performance Considerations

- Remove operations are very fast as they work in memory
- Removing fields early in the pipeline can improve overall performance
- Consider the impact on downstream processes that might expect removed fields
- Large numbers of fields to remove have minimal performance impact

## Related Actions

- [Retain Action](./retain_action.md) - Keep only specified fields (opposite of remove)
- [Copy Action](./copy_action.md) - Create backups before removing important fields
- [Statement Action](./statement_action.md) - Add conditional logic around field removal

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [Field Management](../user-guide/field-management.md)
- [Data Privacy Guidelines](../user-guide/data-privacy.md)

---
