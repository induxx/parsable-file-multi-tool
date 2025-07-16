
The **`filter_field`** action allows you to filter out your item on exact matched `fields` or on close matches like `start_with`, `ends_with` and `contains`.
The result is the found matches remain, when using it in `reverse` mode you have to exact opposite. 

This action manipulates **fields** and **values**.

You also clear its value if needed with the `clear_value` option.

Here's some examples of how you might use the **`filter_field`** action in a YAML file:

```yaml
actions:
  remove_fields:
    action: filter_field
    fields: ['enabled', 'parent']
```
This action looks very similar to a remove action.

```yaml
actions:
  retain_fields:
    action: filter_field
    fields: ['enabled', 'parent']
    reverse: true
```
This action now looks very similar to a retain action.

```yaml
actions:
  filter_fields:
    action: filter_field
    starts_with: 'erp_'
```
This action will filter out all fields starting with `erp_`.

```yaml
actions:
  filter_fields:
    action: filter_field
    ends_with: '_erp'
```
This action will filter out all fields ends with `_erp`.

```yaml
actions:
  filter_fields:
    action: filter_field
    contains: 'section-'
```
This action will filter out all fields containing `section-`.

```yaml
actions:
  filter_fields:
    action: filter_field
    contains: 'section-'
    clear_value: true
```
This action will clear values from fields containing `section-`.
