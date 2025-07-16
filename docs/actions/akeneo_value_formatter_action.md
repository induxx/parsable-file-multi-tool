
The **`akeneo_value_format`** action allows you format akeneo specific attribute values.
It needs akeneo attributes to match the correct attribute type.

This action manipulates **values**.
This action can work with **string** values or **standard data**.
This action is limited to data coming from akeneo.

Here's an example of how you might use the **`akeneo_value_format`** action in a YAML file:

```yaml
actions:
  remove_fields:
    action: akeneo_value_format
    fields: ['enabled']
    context:
      pim_catalog_boolean:
        label:
          Y: 'TRUE'
          N: 'FALSE'
      pim_catalog_metric:
        format: '%amount% %unit%'
```
In this example 2 akeneo attribute types are formatted into a string label type.

```yaml
source:
  '%workpath%/attribute_options.csv'

actions:
  format_akeneo_values:
    action: akeneo_value_format
    fields: ['color']
    context:
      pim_catalog_simpleselect:
        source: attribute_options
        filter:
          attribute: '{attribute-code}'
          code: '{value}'
        return: 'labels-nl_BE'
```
In this example we will replace a value of type `pim_catalog_simpleselect` with a label value of `nl_BE`.
The special `{value}` strings are being replaced by the items key-value, so if `item['color']='red'` then we populate the field value as attribute-code`{attribute-code}=color` and value `{value}=red`.
We us lots akeneo naming-schemes here as this is an akeneo specific action.
