
The **`expand`** action allows you to add new fields to an item being processed, as well as preserve the order of the existing fields. It takes a single argument, **`set`**, which is a dictionary of field-value pairs to be added to the item.

Here's an example of how you might use the **`expand`** action in a YAML file:

```yaml
actions:
  add_mandatory_columns:
    action: expand
    set:
      code: ''
      attributes: []
      filters: []
      sortorder: []
      attribute_as_image: ''
      attribute_as_label: 'Title'
      default_metric_units: ''
      attribute_filters_filter: []
      attribute_filters_internal: []
      requirements-ecommerce: []
```

In this example, the **`expand`** action will add the specified fields and values to the item being processed. If any of the fields already exist in the item, their values will not be overridden.