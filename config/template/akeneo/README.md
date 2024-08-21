## Templates

We support the following Akeneo endpoints.

- attributes
- family
- family_variants
- options
- products
- product-models
- categories
- reference-entities

If you like to add support to the template system, make sure you have the required converter(s) in place.

You can the API in 3 different Styles: JSON, CSV & as ITEM.

[JSON] will not change anything, all Payloads are as is.
Multi-dimensional Structure are kept.
The Style is not very flexible, to make structural changes or the read multi-dimensional data.

[CSV] will convert the Payload to CSV style flat data.
This style is not very flexible to make structural changes.

[ITEM] will convert the Payload into a easy parsable Item, 
this type will give you the most flexibility to manipulate the data.

### Examples

```yaml
transformation_steps:
  - {'run': akeneo/json/pull_akeneo-reference-entities.yaml, 'with': {'endpoint': ['brand']}}
  - {'run': akeneo/json/pull_akeneo-entities.yaml, 'with': {'endpoint': ['attributes', 'families']}}

  - {'run': akeneo/json/push_akeneo-reference-entities.yaml, 'with': {'endpoint': ['brand']}}
  - {'run': akeneo/json/push_akeneo-entities.yaml, 'with': {'endpoint': ['attributes', 'families']}}
```

In this example the only custom file is change_other_attributes.yaml.

```yaml
transformation_steps:
  - {'run': akeneo/json/pull_akeneo-entities.yaml, 'with': {'endpoint': ['attributes']}}
  # DO SOMETHING
  - {'run': change_other_attributes.yaml, 'with': {'endpoint': ['attributes']}}
  - {'run': akeneo/json/push_akeneo-entities.yaml, 'with': {'endpoint': ['attributes']}}
```

```yaml
# change_other_attributes.yaml

sources:
  - '%workpath%/akeneo_%endpoint%.jsonl'

pipeline:
  input:
    reader:
      type: jsonl
      filename: '%workpath%/akeneo_%endpoint%.jsonl'
      filter:
        group: 'other'

  actions:
    # DO SOMETHING

  output:
    writer:
      type: jsonl
      filename: '%workpath%/akeneo_%endpoint%.jsonl'
```