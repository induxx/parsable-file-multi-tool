
# Calculate Action

## Overview

The calculate action performs arithmetic operations on two or more fields and stores the result in a specified field. It supports basic mathematical operations including addition, subtraction, multiplication, and division, making it essential for data transformation workflows that require computed values.

## Syntax

```yaml
actions:
  - action: calculate
    fields: [field_1, field_2]
    operator: operation
    result: result_field
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| fields | array | Yes | - | Array of field names to use in the calculation |
| operator | string | Yes | - | Arithmetic operation to perform |
| result | string | Yes | - | Name of the field where the result will be stored |

### Parameter Details

#### fields
An array containing the names of fields to be used in the calculation. The fields must contain numeric values for the operation to succeed.

- **Format:** Array of strings
- **Minimum:** 2 fields required
- **Example:** `["quantity", "price_gbp"]`

#### operator
The arithmetic operation to perform on the specified fields.

- **Valid values:** `ADD`, `SUBTRACT`, `MULTIPLY`, `DIVIDE`
- **Case sensitive:** Yes, must be uppercase
- **Example:** `MULTIPLY`

#### result
The name of the field where the calculation result will be stored. If the field already exists, it will be overwritten.

- **Format:** String
- **Example:** `"total_price_gbp"`

## Examples

### Basic Multiplication

```yaml
actions:
  - action: calculate
    fields: [quantity, price_gbp]
    operator: MULTIPLY
    result: total_price_gbp
```

**Input:**
```json
{
  "quantity": 10,
  "price_gbp": 2.50
}
```

**Output:**
```json
{
  "quantity": 10,
  "price_gbp": 2.50,
  "total_price_gbp": 25.00
}
```

### Addition with Multiple Fields

```yaml
actions:
  - action: calculate
    fields: [base_price, tax_amount, shipping_cost]
    operator: ADD
    result: total_cost
```

**Input:**
```json
{
  "base_price": 100.00,
  "tax_amount": 15.00,
  "shipping_cost": 10.00
}
```

**Output:**
```json
{
  "base_price": 100.00,
  "tax_amount": 15.00,
  "shipping_cost": 10.00,
  "total_cost": 125.00
}
```

### Division for Rate Calculation

```yaml
actions:
  - action: calculate
    fields: [total_sales, number_of_days]
    operator: DIVIDE
    result: daily_average
```

**Input:**
```json
{
  "total_sales": 1500.00,
  "number_of_days": 30
}
```

**Output:**
```json
{
  "total_sales": 1500.00,
  "number_of_days": 30,
  "daily_average": 50.00
}
```

## Use Cases

### Use Case 1: E-commerce Price Calculations
Calculate total prices including taxes, discounts, and shipping costs for product listings.

### Use Case 2: Financial Reporting
Compute ratios, averages, and totals for financial data analysis and reporting.

### Use Case 3: Inventory Management
Calculate stock values, reorder points, and inventory turnover rates.

## Common Issues and Solutions

### Issue: Division by Zero

**Symptoms:** Action fails or produces unexpected results when dividing by zero.

**Cause:** One of the fields in a division operation contains zero or null value.

**Solution:** Validate data before calculation or use conditional logic.

```yaml
# Add validation before calculation
actions:
  - action: statement
    condition: "{{ number_of_days > 0 }}"
    then:
      - action: calculate
        fields: [total_sales, number_of_days]
        operator: DIVIDE
        result: daily_average
```

### Issue: Missing or Null Fields

**Symptoms:** Calculation produces null or unexpected results.

**Cause:** One or more fields specified in the calculation are missing or contain null values.

**Solution:** Ensure all required fields exist and contain numeric values.

```yaml
# Use default values for missing fields
actions:
  - action: copy
    from: quantity
    to: safe_quantity
    default: 1
  - action: calculate
    fields: [safe_quantity, price_gbp]
    operator: MULTIPLY
    result: total_price_gbp
```

### Issue: Non-numeric Values

**Symptoms:** Calculation fails or produces NaN (Not a Number) results.

**Cause:** Fields contain non-numeric values like strings or booleans.

**Solution:** Convert or validate field types before calculation.

```yaml
# Convert to numeric before calculation
actions:
  - action: format
    field: quantity
    type: number
  - action: calculate
    fields: [quantity, price_gbp]
    operator: MULTIPLY
    result: total_price_gbp
```

## Performance Considerations

- Calculations are performed in memory and are generally fast
- For large datasets, consider batching operations
- Division operations may be slightly slower than other arithmetic operations
- Null value handling adds minimal overhead

## Related Actions

- [Format Action](./format_action.md) - Convert field types before calculation
- [Copy Action](./copy_action.md) - Set default values for missing fields
- [Statement Action](./statement_action.md) - Add conditional logic around calculations

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [Data Type Handling](../user-guide/data-types.md)
- [Error Handling](../user-guide/error-handling.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*

