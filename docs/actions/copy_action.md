
# Copy Action

## Overview

The copy action allows you to copy the value of a field from one field to another. It's essential for data transformation workflows where you need to duplicate field values, create backup copies of data, or populate new fields with existing values.

## Syntax

```yaml
actions:
  - action: copy
    from: source_field
    to: target_field
    default: default_value
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| from | string | Yes | - | Source field name to copy from |
| to | string | Yes | - | Target field name to copy to |
| default | any | No | null | Default value if source field is empty or missing |

### Parameter Details

#### from
The name of the source field whose value will be copied.

- **Format:** String field name
- **Example:** `"filters"`
- **Behavior:** If the field doesn't exist, the copy operation will use the default value

#### to
The name of the target field where the copied value will be stored.

- **Format:** String field name
- **Example:** `"attribute_filters_filter"`
- **Behavior:** If the field already exists, it will be overwritten

#### default
Optional default value to use when the source field is empty, null, or doesn't exist.

- **Format:** Any data type
- **Example:** `"N/A"`, `0`, `[]`
- **Behavior:** Used when source field is missing or empty

## Examples

### Basic Field Copy

```yaml
actions:
  - action: copy
    from: filters
    to: attribute_filters_filter
```

**Input:**
```json
{
  "filters": "color:red,size:large",
  "product_name": "Widget"
}
```

**Output:**
```json
{
  "filters": "color:red,size:large",
  "product_name": "Widget",
  "attribute_filters_filter": "color:red,size:large"
}
```

### Copy with Default Value

```yaml
actions:
  - action: copy
    from: description
    to: product_description
    default: "No description available"
```

**Input:**
```json
{
  "product_name": "Widget",
  "price": 29.99
}
```

**Output:**
```json
{
  "product_name": "Widget",
  "price": 29.99,
  "product_description": "No description available"
}
```

### Creating Backup Fields

```yaml
actions:
  - action: copy
    from: original_price
    to: backup_price
```

**Input:**
```json
{
  "original_price": 100.00,
  "discount": 0.15
}
```

**Output:**
```json
{
  "original_price": 100.00,
  "discount": 0.15,
  "backup_price": 100.00
}
```

## Use Cases

### Use Case 1: Data Backup
Create backup copies of important fields before applying transformations that might modify the original values.

### Use Case 2: Field Standardization
Copy values from legacy field names to standardized field names while maintaining backward compatibility.

### Use Case 3: Default Value Assignment
Populate missing fields with default values to ensure data consistency across records.

## Common Issues and Solutions

### Issue: Source Field Missing

**Symptoms:** Target field is not created or contains null value.

**Cause:** Source field doesn't exist in the input data.

**Solution:** Use the default parameter to provide a fallback value.

```yaml
actions:
  - action: copy
    from: optional_field
    to: required_field
    default: "default_value"
```

### Issue: Overwriting Important Data

**Symptoms:** Existing target field data is lost after copy operation.

**Cause:** Copy action overwrites existing field values.

**Solution:** Check if target field exists before copying or use conditional logic.

```yaml
actions:
  - action: statement
    condition: "{{ target_field is empty }}"
    then:
      - action: copy
        from: source_field
        to: target_field
```

### Issue: Copying Complex Data Types

**Symptoms:** Nested objects or arrays are not copied correctly.

**Cause:** Copy action performs shallow copying by default.

**Solution:** The copy action handles complex data types correctly, but verify the structure after copying.

```yaml
# This works correctly for nested data
actions:
  - action: copy
    from: nested_object
    to: backup_nested_object
```

## Performance Considerations

- Copy operations are very fast as they work in memory
- No performance impact for simple data types
- Minimal overhead for complex nested structures
- Consider memory usage when copying large data structures

## Related Actions

- [Rename Action](./rename_action.md) - Move field values instead of copying
- [Format Action](./format_action.md) - Transform data while copying
- [Statement Action](./statement_action.md) - Add conditional logic around copy operations

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [Field Management](../user-guide/field-management.md)
- [Data Type Handling](../user-guide/data-types.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*