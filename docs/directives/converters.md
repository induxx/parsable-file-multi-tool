The converter directive is used to convert unstructured none-normalized data into structured normalized data.
Especially when the data comes from an API endpoint. API data is often too challenging to manipulate directly with actions.

Here are some key pointers when or why you should consider making use of a converter.
- the key and value is not always single dimensional
- the values are scattered in multi-dimensional nodes
- the value belongs to a locale or another scope, and it's hard to filter out those scopes
- the value needs specific formatting that the destination can accept

Every Converter has two methods, the convert() method and the revert() method.
The general idea here is to
1. convert the data into structured normalized data
2. apply actions to further transform the data
3. revert back to its original form if needed


### Converter linking
Another powerful idea is to connect two different converters onto each other. 
This is only possible if the output item of both converters have the same data structure. This means that converter A `convert()` method can be linked to converter B `revert()` method.
If done correctly you could convert API data to CSV, and CSV data to API without any data loss.
To streamline this linking process a stricter structured data Object is recommended.
Using a structured data Object is however not required to make the link.

Here's an example of standard akeneo converters that you can use:

```yaml
pipeline:
  input:
    http:
      type: rest_api
      account: '%akeneo_api_account%'
      endpoint: products
      method: GET
      converter: 'akeneo/product/api' # WIP

  output:
    writer:
      type: buffer_csv
      filename: 'akeneo_products.csv'
      converter: 'akeneo/product/csv'
```
this example reads `akeneo/product/api` data and converts it to `akeneo/product/csv` calls.

```yaml
sources:
  - '%workpath%/akeneo_product.csv'
  - '%workpath%/akeneo_attributes.csv'

list:
  - name: attribute_types
    source: akeneo_attributes.csv
    source_command: key_value_pair
    options:
      key: code
      value: type

converter:
  name: 'akeneo/product/csv'
  options:
    attribute_types:list: attribute_types

pipeline:
  input:
    reader:
      type: csv
      filename: 'akeneo_product.csv'
      converter: 'akeneo/product/csv'

  output:
    http:
      type: rest_api
      account: '%akeneo_api_account%'
      endpoint: products
      method: MULTI_PATCH
      converter: 'akeneo/product/api'
```
This example reads `akeneo/product/csv` data and converts it to `akeneo/product/api` calls.

### Helpers
Some converter needs to be assisted with other source data.
It's not always required, but it helps when you for example have to convert specific attribute types like currencies or metrics.
Often these types need a helping hand before they can be acceptable values.

```yaml
list:
  - name: attribute_types
    source: akeneo_attributes.csv
    source_command: key_value_pair
    options:
      key: code
      value: type

converter:
  name: 'akeneo/product/csv'
  options:
    attribute_types:list: attribute_types
```
In this example we supply the `akeneo/product/csv` with list data from attributes.
This so that we can manipulate specific attribute types.

#### Converter Assistance
Since we often have to "Assist" or "Correct" source data to fit the attribute type requirements, a special Converter was created to fit these needs.
This converter expects a compatible akeneo CSV file where further assist is needed. The better your input the better the output.
The converter is named `flat/akeneo/product/csv` and currently offers help with multi-select, simple-select, and number type attributes.

### Best Practices
- Always convert API data to flat structured data like CSV.
- Don't use Custom Converters to manipulate data that can be manipulated with actions.
- Make first good flat CSV file and later convert that file data to API calls.

### Custom Converters (WIP)
We cannot cover all possible scenarios when we have to read or write foreign data.
And it's not ideal to always need a build for supporting every custom source.

#### Decide your own read() or write() loop (WIP)
Sometimes the item you read is not the item you want in your pipeline.
The values have to be collected or expanded to form a new main loop.
To help you in this task we provide a read() and write() method that expects an ItemCollection.
Find out more about this in the ItemCollection section.