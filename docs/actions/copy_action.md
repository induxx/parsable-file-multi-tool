
The **`copy`** action allows you to copy the value of a field from one field to another. It takes two arguments: **`from`**, which specifies the source field, and **`to`**, which specifies the target field.

Here's an example of how you might use the **`copy`** action in a YAML file:

```yaml
actions:
  merge_filter:
    action: copy
    from: filters
    to: attribute_filters_filter
```

In this example, the **`copy`** action will copy the value of the **`filters`** field to the **`attribute_filters_filter`** field. If the **`filters`** field is empty or does not exist, the **`attribute_filters_filter`** field will also be empty or will not be created.