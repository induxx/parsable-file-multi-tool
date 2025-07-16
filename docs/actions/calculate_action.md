
The **`calculate`** action allows you to perform arithmetic operations on two fields and store the result in a third field. You can use the **`calculate`** action to perform addition, subtraction, multiplication, and division.

## Usage

The **`calculate`** action is used in the following format:

```yaml
actions:
  action_name:
    action: calculate
    fields: [field_1, field_2]
    operator: operation
    result: result_field
```

Where:
- **`action_name`** is the name of the action.
- **`field_1`** is the name of the first field used in the calculation.
- **`field_2`** is the name of the second field used in the calculation.
- **`operation`** is the arithmetic operation to perform. Valid values are `ADD`, `SUBTRACT`, `MULTIPLY`, and `DIVIDE`.
- **`result_field`** is the name of the field where the result of the calculation will be stored.

## Example

Consider the following input:

```yaml
item:
  - quantity: 10
  - price_gbp: 2.50
```

And the following `calculate` action:

```yaml
actions:
  calculate-giftbox_rrp_gbp:
    action: calculate
    fields: [ quantity, price_gbp ]
    operator: MULTIPLY
    result: total_price_gbp
```

When executed, the **`calculate`** action multiplies the values of the `quantity` and `price_gbp` fields, and stores the result in the `total_price_gbp` field.

The output of the `calculate` action, given the input above, would be:

```yaml
item:
  - quantity: 10
  - price_gbp: 2.50
  - total_price_gbp: 25.00
```

## Notes

- The `fields` array can contain any number of fields, as long as the arithmetic operation is valid.
- If any of the fields used in the calculation are missing from the input, the result will be ether unchanged (e.a. 5 multiplied by a null column would result in 5) or `null`.

