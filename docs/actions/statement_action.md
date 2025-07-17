
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

## Related Topics

### Core Conditional Logic Actions
- **[Copy Action](./copy_action.md)** - Often used within then clauses for field assignments and data backup
- **[Format Action](./format_action.md)** - Format data before conditional checks and apply transformations conditionally
- **[Debug Action](./debug_action.md)** - Debug condition evaluation and troubleshoot conditional logic
- **[Calculate Action](./calculate_action.md)** - Perform calculations conditionally based on field values

### Data Processing Actions
- **[Concat Action](./concat_action.md)** - Combine fields conditionally and create composite values
- **[Rename Action](./rename_action.md)** - Rename fields based on conditions and reorganize data structure
- **[Remove Action](./remove_action.md)** - Remove fields conditionally and clean up data selectively
- **[Retain Action](./retain_action.md)** - Keep fields conditionally and filter data based on criteria

### Value Transformation Actions
- **[Value Mapping Action](./value_mapping_in_list_action.md)** - Apply value mappings conditionally
- **[Key Mapping Action](./key_mapping_action.md)** - Use conditional logic with mapping operations
- **[Date Time Action](./date_time_action.md)** - Process dates conditionally and handle time-based logic

### Configuration and Context
- **[Context Directive](../directives/context.md)** - Define conditional parameters and environment-specific logic
- **[Mapping Directive](../directives/mapping.md)** - Use mappings in conditional checks and value comparisons
- **[List Directive](../directives/list.md)** - Reference lists in IN_LIST conditions and membership checks
- **[Pipeline Configuration](../directives/pipelines.md)** - Integrate conditional logic in data processing workflows

### Advanced Conditional Processing
- **[Transformation Steps](../directives/transformation_steps.md)** - Multi-step conditional workflows and complex decision trees
- **[Convergence Action](./convergence_action.md)** - Handle data convergence scenarios with conditional logic
- **[Extension Action](./extension_action.md)** - Create custom conditional logic and advanced decision-making

### Debugging and Optimization
- **[Debugging Guide](../user-guide/debugging.md)** - Debug conditional logic and troubleshoot statement evaluation
- **[Performance Optimization](../user-guide/debugging.md#performance-optimization-guidelines)** - Optimize conditional processing for large datasets
- **[CLI Commands](../reference/cli-commands.md)** - Test conditional logic with limited data and debug mode

### Data Validation and Quality
- **[Data Validation Patterns](../user-guide/transformations.md#data-validation)** - Use statements for data quality checks
- **[Error Handling](../user-guide/debugging.md#common-error-scenarios-and-solutions)** - Handle conditional processing errors
- **[Field Management](../user-guide/transformations.md#field-management)** - Conditional field operations and data organization

## See Also

- **[Actions Reference](./index.md)** - Complete list of all available actions and conditional capabilities
- **[Transformation Examples](../examples/)** - Practical conditional logic examples and common patterns
- **[Quick Start Guide](../getting-started/quick-start.md)** - Basic conditional processing techniques for beginners
- **[Advanced Workflows](../examples/advanced-workflows.md)** - Complex conditional logic patterns and decision trees

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: utility*

