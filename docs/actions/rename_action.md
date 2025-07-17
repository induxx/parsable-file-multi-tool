
# Rename Action

## Overview

The rename action allows you to rename specific fields in an item being processed. It supports both single field renaming and bulk field renaming operations, making it essential for data standardization workflows where field names need to be updated or normalized.

## Syntax

```yaml
# Single field rename
actions:
  - action: rename
    from: old_field_name
    to: new_field_name

# Multiple field rename
actions:
  - action: rename
    fields:
      old_field_1: new_field_1
      old_field_2: new_field_2
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| from | string | No* | - | Source field name to rename (single field mode) |
| to | string | No* | - | Target field name (single field mode) |
| fields | object | No* | - | Dictionary mapping old field names to new field names |

*Either `from`/`to` or `fields` must be provided

### Parameter Details

#### from
The name of the source field to be renamed. Used in single field rename mode.

- **Format:** String field name
- **Example:** `"ATTRIBUTE_UNIT"`
- **Behavior:** Must be used together with `to` parameter

#### to
The new name for the field. Used in single field rename mode.

- **Format:** String field name
- **Example:** `"unit"`
- **Behavior:** Must be used together with `from` parameter

#### fields
A dictionary mapping old field names to new field names for bulk renaming operations.

- **Format:** Object with key-value pairs
- **Example:** `{"ATTRIBUTE_UNIT": "unit", "OLD_NAME": "new_name"}`
- **Behavior:** All specified field mappings will be applied

## Examples

### Single Field Rename

```yaml
actions:
  - action: rename
    from: ATTRIBUTE_UNIT
    to: unit
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "ATTRIBUTE_UNIT": "kg",
  "price": 29.99
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "unit": "kg",
  "price": 29.99
}
```

### Multiple Field Rename

```yaml
actions:
  - action: rename
    fields:
      ATTRIBUTE_UNIT: unit
      PRODUCT_NAME: name
      PRICE_USD: price
```

**Input:**
```json
{
  "ATTRIBUTE_UNIT": "kg",
  "PRODUCT_NAME": "Widget Pro",
  "PRICE_USD": 29.99,
  "category": "electronics"
}
```

**Output:**
```json
{
  "unit": "kg",
  "name": "Widget Pro",
  "price": 29.99,
  "category": "electronics"
}
```

### Legacy Field Standardization

```yaml
actions:
  - action: rename
    fields:
      old_customer_id: customer_id
      cust_name: customer_name
      addr_line_1: address_line_1
      addr_line_2: address_line_2
      ph_number: phone_number
```

**Input:**
```json
{
  "old_customer_id": "CUST001",
  "cust_name": "John Doe",
  "addr_line_1": "123 Main St",
  "addr_line_2": "Apt 4B",
  "ph_number": "555-0123"
}
```

**Output:**
```json
{
  "customer_id": "CUST001",
  "customer_name": "John Doe",
  "address_line_1": "123 Main St",
  "address_line_2": "Apt 4B",
  "phone_number": "555-0123"
}
```

## Use Cases

### Use Case 1: API Response Standardization
Rename fields from external API responses to match internal data schema standards.

### Use Case 2: Database Migration
Update field names during data migration from legacy systems to new database schemas.

### Use Case 3: Data Export Formatting
Rename fields to match the expected format of target systems or export specifications.

## Common Issues and Solutions

### Issue: Source Field Not Found

**Symptoms:** Rename operation has no effect, original field names remain unchanged.

**Cause:** The source field specified in `from` parameter or `fields` mapping doesn't exist in the data.

**Solution:** Verify that source field names match exactly with the data fields.

```yaml
# Use debug action to inspect field names first
actions:
  - action: debug
    field: all_fields
  - action: rename
    from: correct_field_name
    to: new_name
```

### Issue: Target Field Already Exists

**Symptoms:** Existing data in target field is overwritten unexpectedly.

**Cause:** The target field name already exists in the data.

**Solution:** Check for existing fields or use a different target name.

```yaml
# Create backup before renaming if target might exist
actions:
  - action: copy
    from: existing_target_field
    to: backup_target_field
  - action: rename
    from: source_field
    to: existing_target_field
```

### Issue: Conflicting Parameter Usage

**Symptoms:** Rename action fails or behaves unexpectedly.

**Cause:** Both single field parameters (`from`/`to`) and bulk parameter (`fields`) are used together.

**Solution:** Use either single field or bulk renaming, not both.

```yaml
# Correct: Use only one approach
actions:
  - action: rename
    fields:
      old_field_1: new_field_1
      old_field_2: new_field_2
```

## Performance Considerations

- Rename operations are very fast as they work in memory
- Bulk renaming with `fields` parameter is more efficient than multiple single renames
- No performance difference between short and long field names
- Consider the impact on downstream processes that expect the old field names

## Related Actions

- [Copy Action](./copy_action.md) - Create copies with new names while preserving originals
- [Remove Action](./remove_action.md) - Remove old fields after renaming
- [Statement Action](./statement_action.md) - Add conditional logic around renaming operations

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [Field Management](../user-guide/field-management.md)
- [Data Schema Migration](../user-guide/schema-migration.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*