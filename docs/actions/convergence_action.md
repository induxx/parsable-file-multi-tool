
The **`convergence`** action allows you to combine fields with some formatting options.

This action manipulates **values**.
This action can work with **string** values or **standard data**.

Here's an example of how you might use the **`convergence`** action in a YAML file:

```yaml
actions:
  remove_fields:
    action: convergence
    fields: ['street', 'city', 'state']
    store_field: 'address_line'
# default options
    item_sep: ' ,'
    key_value_sep: ': '
    encapsulate: false
    encapsulation_char: '"'
```
expected output
```yaml
---
address_line: 'street: 123 Main Street, city: Anytown, state: CA'
```
In this example an `address_line` is formed with the default options.