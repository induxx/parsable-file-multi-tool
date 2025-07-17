
# Retain Action

## Overview

The retain action allows you to keep only the specified keys or fields while discarding all others from an item being processed. It's the opposite of the remove action and is essential for data filtering workflows where you need to extract only specific fields from complex data structures.

## Syntax

```yaml
actions:
  - action: retain
    keys: [field1, field2, field3]
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| keys | array | Yes | - | Array of field names to retain in the item |

### Parameter Details

#### keys
An array containing the names of fields to be retained in the processed item. All other fields will be removed.

- **Format:** Array of strings
- **Example:** `["NAME", "AGE", "GENDER"]`
- **Behavior:** Only the specified fields will remain in the output

## Examples

### Basic Field Retention

```yaml
actions:
  - action: retain
    keys: [NAME, AGE, GENDER]
```

**Input:**
```json
{
  "NAME": "John Doe",
  "AGE": 30,
  "GENDER": "Male",
  "SSN": "123-45-6789",
  "INTERNAL_ID": "EMP001",
  "SALARY": 75000,
  "DEPARTMENT": "Engineering"
}
```

**Output:**
```json
{
  "NAME": "John Doe",
  "AGE": 30,
  "GENDER": "Male"
}
```

### Customer Data Extraction

```yaml
actions:
  - action: retain
    keys: [customer_name, email, phone, address]
```

**Input:**
```json
{
  "customer_name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "555-0123",
  "address": "123 Main St",
  "credit_score": 750,
  "internal_notes": "VIP customer",
  "account_created": "2023-01-15",
  "last_login": "2024-01-16"
}
```

**Output:**
```json
{
  "customer_name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "555-0123",
  "address": "123 Main St"
}
```

### Product Information Filtering

```yaml
actions:
  - action: retain
    keys: [product_id, name, price, category, description]
```

**Input:**
```json
{
  "product_id": "PROD001",
  "name": "Widget Pro",
  "price": 29.99,
  "category": "Electronics",
  "description": "High-quality widget",
  "internal_cost": 15.50,
  "supplier_id": "SUP123",
  "warehouse_location": "A-15-B",
  "last_updated": "2024-01-16T10:30:00Z"
}
```

**Output:**
```json
{
  "product_id": "PROD001",
  "name": "Widget Pro",
  "price": 29.99,
  "category": "Electronics",
  "description": "High-quality widget"
}
```

## Use Cases

### Use Case 1: Data Export Preparation
Extract only the necessary fields for external system integration or API responses.

### Use Case 2: Privacy Compliance
Remove sensitive or internal fields while keeping only the data needed for specific operations.

### Use Case 3: Performance Optimization
Reduce data size by keeping only relevant fields for downstream processing.

## Common Issues and Solutions

### Issue: Retaining Non-Existent Fields

**Symptoms:** Output contains fewer fields than expected.

**Cause:** Some field names in the keys array don't exist in the input data.

**Solution:** Verify that all field names in the keys array match actual field names in the data.

```yaml
# Use debug action to inspect available fields first
actions:
  - action: debug
  - action: retain
    keys: [existing_field_1, existing_field_2]
```

### Issue: Case Sensitivity Problems

**Symptoms:** Expected fields are not retained despite being listed in keys array.

**Cause:** Field names have different capitalization than specified in keys array.

**Solution:** Ensure exact case matching between keys array and actual field names.

```yaml
# Match exact case of field names
actions:
  - action: retain
    keys: [FieldName, FIELD_NAME, field_name]  # Use exact case
```

### Issue: Accidentally Removing Required Fields

**Symptoms:** Downstream processes fail due to missing required fields.

**Cause:** Important fields were not included in the keys array.

**Solution:** Review business requirements and ensure all necessary fields are included.

```yaml
# Include all required fields for downstream processing
actions:
  - action: retain
    keys: [id, name, status, created_date, required_field]
```

## Performance Considerations

- Retain operations are very fast as they work in memory
- Retaining fewer fields can improve performance of downstream operations
- No significant performance difference based on number of fields retained
- Consider memory usage when processing large datasets

## Related Actions

- [Remove Action](./remove_action.md) - Remove specific fields (opposite of retain)
- [Copy Action](./copy_action.md) - Create backups before retaining specific fields
- [Statement Action](./statement_action.md) - Add conditional logic around field retention

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [Field Management](../user-guide/field-management.md)
- [Data Privacy Guidelines](../user-guide/data-privacy.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*