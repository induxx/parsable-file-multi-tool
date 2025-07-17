# Value Mapping in List Action

## Overview

The value_mapping_in_list action allows you to replace field values with corresponding values from a predefined mapping list. It's essential for data standardization workflows where you need to transform coded values into human-readable labels or convert between different value systems.

## Syntax

```yaml
actions:
  - action: value_mapping_in_list
    field: [field_name]
    list:
      - OLD_VALUE: NEW_VALUE
      - ANOTHER_OLD: ANOTHER_NEW

# Or using external list reference
actions:
  - action: value_mapping_in_list
    field: [field_name]
    list: list_name
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| field | array | Yes | - | Array of field names whose values should be mapped |
| list | array/string | Yes | - | Value mapping list or reference to external list |

### Parameter Details

#### field
An array containing the names of fields whose values should be mapped using the provided list.

- **Format:** Array of strings
- **Example:** `["color"]`, `["status", "priority"]`
- **Behavior:** All specified fields will have their values mapped if matches are found

#### list
Defines the value mappings to apply. Can be either an inline array of mappings or a reference to an external list configuration.

- **Format:** Array of key-value pairs or string reference
- **Example:** `[{"RED": "Red"}, {"GREEN": "Green"}]` or `"color_mappings"`
- **Behavior:** Values matching the keys will be replaced with corresponding values

## Examples

### Basic Color Code Mapping

```yaml
actions:
  - action: value_mapping_in_list
    field: ['color']
    list:
      - RED: Red
      - GREEN: Green
      - BLUE: Blue
      - YELLOW: Yellow
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "color": "RED",
  "size": "large"
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "color": "Red",
  "size": "large"
}
```

### External List Reference

```yaml
list:
  - name: status_mappings
    values:
      - ACTIVE: Active
      - INACTIVE: Inactive
      - PENDING: Pending Review
      - ARCHIVED: Archived

actions:
  - action: value_mapping_in_list
    field: ['status']
    list: status_mappings
```

**Input:**
```json
{
  "user_id": "USER001",
  "status": "PENDING",
  "created_date": "2024-01-16"
}
```

**Output:**
```json
{
  "user_id": "USER001",
  "status": "Pending Review",
  "created_date": "2024-01-16"
}
```

### Multiple Field Mapping

```yaml
actions:
  - action: value_mapping_in_list
    field: ['priority', 'severity']
    list:
      - LOW: Low
      - MED: Medium
      - HIGH: High
      - CRIT: Critical
```

**Input:**
```json
{
  "ticket_id": "TICK001",
  "priority": "HIGH",
  "severity": "MED",
  "description": "System issue"
}
```

**Output:**
```json
{
  "ticket_id": "TICK001",
  "priority": "High",
  "severity": "Medium",
  "description": "System issue"
}
```

### Country Code to Name Mapping

```yaml
actions:
  - action: value_mapping_in_list
    field: ['country_code']
    list:
      - US: United States
      - CA: Canada
      - GB: United Kingdom
      - DE: Germany
      - FR: France
      - JP: Japan
```

**Input:**
```json
{
  "customer_name": "John Doe",
  "country_code": "US",
  "order_total": 150.00
}
```

**Output:**
```json
{
  "customer_name": "John Doe",
  "country_code": "United States",
  "order_total": 150.00
}
```

## Use Cases

### Use Case 1: Code Translation
Convert system codes or abbreviations into human-readable labels for display purposes.

### Use Case 2: Data Standardization
Normalize values from different data sources that use different coding systems.

### Use Case 3: Localization
Transform values into localized versions for different languages or regions.

## Common Issues and Solutions

### Issue: Values Not Being Mapped

**Symptoms:** Field values remain unchanged despite being listed in the mapping.

**Cause:** The field values don't exactly match the keys in the mapping list.

**Solution:** Ensure exact case-sensitive matching between field values and mapping keys.

```yaml
# Debug field values first
actions:
  - action: debug
    field: target_field
  - action: value_mapping_in_list
    field: ['target_field']
    list:
      - EXACT_VALUE: Mapped Value
```

### Issue: External List Not Found

**Symptoms:** Value mapping action fails when using external list reference.

**Cause:** The referenced list name doesn't exist in the list configuration.

**Solution:** Ensure the list is properly defined and the name matches exactly.

```yaml
# Ensure list is defined
list:
  - name: correct_list_name
    values:
      - OLD_VALUE: New Value

actions:
  - action: value_mapping_in_list
    field: ['field_name']
    list: correct_list_name  # Must match list name exactly
```

### Issue: Partial Mapping Results

**Symptoms:** Some values are mapped while others remain unchanged.

**Cause:** Not all possible values are included in the mapping list.

**Solution:** Add all expected values to the mapping list or handle unmapped values gracefully.

```yaml
# Include all possible values or add default handling
actions:
  - action: value_mapping_in_list
    field: ['status']
    list:
      - ACTIVE: Active
      - INACTIVE: Inactive
      - UNKNOWN: Unknown Status  # Handle unexpected values
```

## Performance Considerations

- Value mapping operations are very fast for small to medium-sized lists
- Large mapping lists may have slight performance impact
- External list references have minimal lookup overhead
- Consider organizing frequently used mappings for better maintainability

## Related Actions

- [Key Mapping Action](./key_mapping_action.md) - Map field names instead of field values
- [Format Action](./format_action.md) - Format values after mapping
- [Statement Action](./statement_action.md) - Add conditional logic around value mapping

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [List Configuration](../directives/list.md)
- [Value Transformation](../user-guide/value-transformation.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*