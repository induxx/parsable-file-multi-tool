
# Statement Action

## Overview

The statement action is a versatile conditional logic tool that allows you to apply specific actions or set values based on various conditions. It supports complex conditional operations including field comparisons, list membership checks, numeric comparisons, and date-based conditions, making it essential for dynamic data processing workflows.

## Syntax

```yaml
actions:
  - action: statement
    when:
      field: field_name
      operator: CONDITION_OPERATOR
      state: comparison_value
      context:
        list: list_name
    then:
      field: target_field
      state: result_value
    # Alternative: execute other actions
    then:
      - action: other_action
        parameter: value
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| when | object | Yes | - | Condition definition object |
| then | object/array | Yes | - | Action(s) to execute when condition is true |

### When Condition Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| field | string | Yes | - | Field name to evaluate |
| operator | string | Yes | - | Comparison operator to use |
| state | string | No | - | Value to compare against |
| context | object | No | - | Additional context for complex conditions |

### Parameter Details

#### when.field
The name of the field to evaluate in the condition.

- **Format:** String field name
- **Example:** `"categories"`, `"sku"`, `"price"`
- **Behavior:** Field value will be compared using the specified operator

#### when.operator
The comparison operator to use for the condition.

- **Valid values:** `EQUALS`, `NOT_EMPTY`, `IN_LIST`, `GREATER_THAN_OR_EQUAL_TO`, `LESS_THAN`, `DATE`, `CONTAINS`
- **Example:** `"EQUALS"`, `"NOT_EMPTY"`
- **Behavior:** Determines how the field value is compared

#### when.state
The value to compare the field against (when applicable).

- **Format:** String
- **Example:** `"SAP_EHS_1013_010"`, `"1"`, `"TODAY"`
- **Usage:** Required for operators like EQUALS, GREATER_THAN_OR_EQUAL_TO

#### when.context
Additional context for complex conditions like list membership.

- **Format:** Object with relevant properties
- **Example:** `{"list": "product_ids"}`
- **Usage:** Required for IN_LIST operator

#### then
Defines what action to take when the condition is true. Can be a field assignment or array of actions.

- **Format:** Object for field assignment or array for multiple actions
- **Example:** `{"field": "status", "state": "active"}` or `[{"action": "copy", "from": "a", "to": "b"}]`

## Examples

### Non-Empty Field Check

```yaml
actions:
  - action: statement
    when:
      field: categories
      operator: NOT_EMPTY
    then:
      field: skip
      state: 'true'
```

**Input:**
```json
{
  "product_name": "Widget",
  "categories": "electronics,gadgets"
}
```

**Output:**
```json
{
  "product_name": "Widget",
  "categories": "electronics,gadgets",
  "skip": "true"
}
```

### Equality Check

```yaml
actions:
  - action: statement
    when:
      field: status
      operator: EQUALS
      state: 'active'
    then:
      field: processing_required
      state: 'true'
```

**Input:**
```json
{
  "product_id": "123",
  "status": "active"
}
```

**Output:**
```json
{
  "product_id": "123",
  "status": "active",
  "processing_required": "true"
}
```

### List Membership Check

```yaml
actions:
  - action: statement
    when:
      field: sku
      operator: IN_LIST
      context:
        list: featured_products
    then:
      field: is_featured
      state: 'true'
```

**Input:**
```json
{
  "sku": "WIDGET-001",
  "name": "Premium Widget"
}
```

**Output (assuming WIDGET-001 is in featured_products list):**
```json
{
  "sku": "WIDGET-001",
  "name": "Premium Widget",
  "is_featured": "true"
}
```

### Numeric Comparison

```yaml
actions:
  - action: statement
    when:
      field: quantity
      operator: GREATER_THAN_OR_EQUAL_TO
      state: '10'
    then:
      field: bulk_discount_eligible
      state: 'true'
```

**Input:**
```json
{
  "product_id": "123",
  "quantity": 15
}
```

**Output:**
```json
{
  "product_id": "123",
  "quantity": 15,
  "bulk_discount_eligible": "true"
}
```

### Date-Based Condition

```yaml
actions:
  - action: statement
    when:
      field: created_date
      operator: DATE
      state: 'TODAY'
    then:
      field: is_new
      state: 'true'
```

### Executing Multiple Actions

```yaml
actions:
  - action: statement
    when:
      field: priority
      operator: EQUALS
      state: 'high'
    then:
      - action: copy
        from: standard_processing_time
        to: expedited_processing_time
      - action: format
        field: expedited_processing_time
        functions: [multiply]
        factor: 0.5
```

## Use Cases

### Use Case 1: Data Validation
Apply validation rules and set flags based on field values and business logic.

### Use Case 2: Conditional Processing
Execute different transformation paths based on data characteristics or business rules.

### Use Case 3: Dynamic Field Assignment
Set field values dynamically based on complex conditions and business logic.

## Common Issues and Solutions

### Issue: Condition Never Matches

**Symptoms:** The `then` actions are never executed despite seemingly matching data.

**Cause:** Data type mismatch or incorrect field name/value comparison.

**Solution:** Verify field names and ensure data types match expected values.

```yaml
# Debug the field value first
actions:
  - action: debug
    field: target_field
  - action: statement
    when:
      field: target_field
      operator: EQUALS
      state: 'expected_value'
    then:
      field: result
      state: 'matched'
```

### Issue: Date Comparisons Not Working

**Symptoms:** Date-based conditions don't behave as expected.

**Cause:** Date format inconsistencies between field value and comparison state.

**Solution:** Standardize date formats before comparison.

```yaml
# Format date before comparison
actions:
  - action: date_time
    field: created_date
    format: 'Y-m-d'
  - action: statement
    when:
      field: created_date
      operator: DATE
      state: 'TODAY'
    then:
      field: is_today
      state: 'true'
```

### Issue: List Context Not Found

**Symptoms:** IN_LIST operator fails or behaves unexpectedly.

**Cause:** The specified list in context doesn't exist or is not accessible.

**Solution:** Ensure the list is properly defined and accessible in the processing context.

```yaml
# Verify list exists in context
actions:
  - action: statement
    when:
      field: product_id
      operator: IN_LIST
      context:
        list: valid_product_list  # Ensure this list exists
    then:
      field: is_valid
      state: 'true'
```

## Performance Considerations

- Simple equality checks are very fast
- List membership checks depend on list size
- Date comparisons may require additional processing
- Complex nested conditions can impact performance
- Consider caching frequently used lists

## Related Actions

- [Copy Action](./copy_action.md) - Often used within then clauses
- [Format Action](./format_action.md) - Format data before conditional checks
- [Debug Action](./debug_action.md) - Debug condition evaluation

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [Conditional Logic Guide](../user-guide/conditional-logic.md)
- [Date Handling](../user-guide/date-handling.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: utility*

