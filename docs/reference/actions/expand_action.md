
# Expand Action

## Overview

The expand action allows you to add new fields to an item being processed while preserving the order of existing fields. It's essential for data enrichment workflows where you need to add default values, mandatory fields, or computed properties to your data structures.

## Syntax

```yaml
actions:
  - action: expand
    set:
      field1: value1
      field2: value2
      field3: []
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| set | object | Yes | - | Dictionary of field-value pairs to add to the item |

### Parameter Details

#### set
A dictionary containing field names and their corresponding values to be added to the item.

- **Format:** Object with key-value pairs
- **Example:** `{"code": "", "attributes": [], "status": "active"}`
- **Behavior:** Fields are added to the item; existing fields are not overridden

## Examples

### Adding Mandatory Fields

```yaml
actions:
  - action: expand
    set:
      code: ''
      attributes: []
      filters: []
      sortorder: []
      attribute_as_image: ''
      attribute_as_label: 'Title'
      default_metric_units: ''
      attribute_filters_filter: []
      attribute_filters_internal: []
      requirements-ecommerce: []
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "price": 29.99
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "price": 29.99,
  "code": "",
  "attributes": [],
  "filters": [],
  "sortorder": [],
  "attribute_as_image": "",
  "attribute_as_label": "Title",
  "default_metric_units": "",
  "attribute_filters_filter": [],
  "attribute_filters_internal": [],
  "requirements-ecommerce": []
}
```

### Adding Default Values

```yaml
actions:
  - action: expand
    set:
      status: 'active'
      created_date: '2024-01-16'
      is_featured: false
      tags: []
      metadata: {}
```

**Input:**
```json
{
  "id": "PROD001",
  "name": "Premium Widget"
}
```

**Output:**
```json
{
  "id": "PROD001",
  "name": "Premium Widget",
  "status": "active",
  "created_date": "2024-01-16",
  "is_featured": false,
  "tags": [],
  "metadata": {}
}
```

### Adding Computed Fields

```yaml
actions:
  - action: expand
    set:
      full_name: ''
      display_price: 0.00
      category_path: ''
      search_keywords: []
      availability_status: 'unknown'
```

**Input:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "base_price": 100.00
}
```

**Output:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "base_price": 100.00,
  "full_name": "",
  "display_price": 0.00,
  "category_path": "",
  "search_keywords": [],
  "availability_status": "unknown"
}
```

### Preserving Existing Fields

```yaml
actions:
  - action: expand
    set:
      status: 'new_default'
      description: 'Default description'
      priority: 1
```

**Input:**
```json
{
  "id": "ITEM001",
  "status": "existing_status",
  "name": "Existing Item"
}
```

**Output:**
```json
{
  "id": "ITEM001",
  "status": "existing_status",
  "name": "Existing Item",
  "description": "Default description",
  "priority": 1
}
```

## Use Cases

### Use Case 1: Schema Standardization
Add mandatory fields with default values to ensure all items conform to a standard schema.

### Use Case 2: Data Enrichment
Add computed or derived fields that will be populated by subsequent transformation steps.

### Use Case 3: API Response Preparation
Add required fields for API responses while preserving existing data structure.

## Common Issues and Solutions

### Issue: Existing Fields Not Preserved

**Symptoms:** Existing field values are unexpectedly changed or lost.

**Cause:** Misunderstanding of expand behavior - expand does not override existing fields.

**Solution:** Expand action preserves existing fields by design. Use other actions if you need to override values.

```yaml
# Expand preserves existing values
actions:
  - action: expand
    set:
      existing_field: 'new_value'  # This won't override existing values
```

### Issue: Wrong Data Types

**Symptoms:** Added fields have incorrect data types for downstream processing.

**Cause:** Default values in set parameter have wrong data types.

**Solution:** Ensure default values match expected data types.

```yaml
# Use correct data types for default values
actions:
  - action: expand
    set:
      count: 0          # Number, not string
      is_active: false  # Boolean, not string
      tags: []          # Array, not string
      metadata: {}      # Object, not string
```

### Issue: Field Order Changes

**Symptoms:** Field order in output is different than expected.

**Cause:** New fields are added after existing fields, which may affect processing order.

**Solution:** Expand action adds fields after existing ones. Use other actions if specific ordering is required.

```yaml
# Fields added by expand appear after existing fields
actions:
  - action: expand
    set:
      new_field: 'value'  # Will appear after existing fields
```

## Performance Considerations

- Expand operations are very fast as they work in memory
- Adding many fields has minimal performance impact
- Consider memory usage when adding large default objects or arrays
- Field order preservation adds minimal overhead

## Related Actions

- [Copy Action](./copy_action.md) - Copy values from existing fields to new fields
- [Statement Action](./statement_action.md) - Add conditional logic around field expansion
- [Format Action](./format_action.md) - Format values after expansion

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [Field Management](../../../user-guide/field-management.md)
- [Data Schema Design](../../../user-guide/schema-design.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*