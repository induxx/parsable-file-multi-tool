The **`value_mapping_in_list`** action allows you to replace values with a matching value in list.

This action manipulates **values**.

Here's some examples of how you might use the **`value_mapping_in_list`** action in a YAML file:

```yaml
actions:
  replace_color_code:
    action: value_mapping_in_list
    field: ['color']
    list: [RED: Red, GREEN: Green]
```
In this example we will replace the color code with a more appropriate label.

```yaml
list:
  - name: color
    values:
      - RED: Red
      - GREEN: Green

actions:
  replace_color_code:
    action: value_mapping_in_list
    field: ['color']
    list: color
```
Same example just a more dynamic setup to feed a list.