
The **`statement`** action is a versatile tool within our data processing framework, designed to apply specific actions or set values based on a variety of conditions, effectively utilizing "when" clauses for conditional logic. This powerful feature supports a broad range of operations, from simple field comparisons to complex conditions involving lists, numeric comparisons, and date checks.

## Usage Examples

Below are several practical examples demonstrating the diverse applications of the **`statement`** action:

### Example 1: Checking for Non-Empty Fields

Checks if a field within a category is not empty and skips processing if so.

```yaml
check_if_type_is_metric:
  action: statement
  when:
    field: categories
    operator: NOT_EMPTY
  then:
    skip: 'true'
```

### Example 2: Equality Check

Evaluates if a field equals a specific value, in this case, checking a sales status.

```yaml
when_sales_status_is_8:
  action: statement
  when:
    field: 'Key Characteristic'
    operator: EQUALS
    state: 'SAP_EHS_1013_010'
  then:
    field: skip
    state: 'false'
```

### Example 3: List Membership

Determines if a product exists within a predefined list of product IDs.

```yaml
when_product_exists:
  action: statement
  when:
    field: sku
    operator: IN_LIST
    context:
      list: product_ids
  then:
    field: check_product_exists
    state: 'true'
```

### Example 4: Numeric Comparison

Checks if a numerical field meets or exceeds a specific value, useful for inventory management.

```yaml
condition_2510_availability_quantity_1_statement:
  action: statement
  when:
    field: 2510_availability_quantity_1
    operator: GREATER_THAN_OR_EQUAL_TO
    state: '1'
  then:
    field: stock_status
    state: 'potentially_in_stock'
```

### Example 5: Date-Based Condition

Applies an action based on the current date, useful for time-sensitive data processing.
When the operator `DATE` is being used, it is prefered that you first convert the date with the `DateTime` action, so that you are sure to compare the same date formats, 'Y-m-d' or 'Y-m-d H:i:s'.
Dates can be compared agaist `TODAY`, `TOMORROW`, `YESTERDAY`, `PAST`, `FUTURE`.

```yaml
statement_manufacturing_plant:
  action: statement
  when:
    field: first
    operator: DATE
    state: 'TODAY'
  then:
    field: diff
    state: 'ok'
```

## Supported Conditions and Actions
The statement action supports a wide range of operators and conditions, enabling dynamic data manipulation based on fields being non-empty, equal to certain values, within a list, greater than or equal to numeric values, or matching specific dates. The actions that can be performed as a result of these conditions include setting fields to specific states, skipping actions, and other custom operations tailored to the specific use case.

This flexibility makes the statement action a cornerstone of conditional logic within our data processing toolkit, suitable for a vast array of applications from data validation and cleansing to dynamic data transformation and categorization.

