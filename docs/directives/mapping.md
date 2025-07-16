The mapping directive in a YAML configuration file allows you to specify key-value data sets that can be used when processing actions in a pipeline. There are a few different options for defining mappings, as shown in the following examples:

```yaml
# Define a mapping using the source option
mapping:
  - name: product_codes
    source: product_code_mapping.yaml
```
In this example, the product_codes mapping is defined using the source option, which specifies the file product_code_mapping.yaml as the source for the key-value data. The product_code_mapping.yaml file should contain the key-value data for the mapping, with each key on a separate line and the corresponding value following the key. For example:

```yaml
# product_code_mapping.yaml
OLD_PRODUCT_CODE: NEW_PRODUCT_CODE
PRODUCT_A: A001
PRODUCT_B: B002
PRODUCT_C: C003
```
Once the mapping is defined, you can use it in a pipeline by referencing the name of the mapping. For example:

```yaml
# Use the mapping in a pipeline
pipeline:
  input:
    reader:
      type: csv
      filename: product_file.csv
  actions:
    map_product_codes:
      action: key_mapping
      list: product_codes
  output:
    writer:
      type: csv
      filename: product.csv
```
In this example, the key_mapping action is used to map the keys in the input data to new values using the product_codes mapping. The resulting data is then written to a CSV file.

You can also define mappings using the sets option, which allows you to combine multiple mappings into a single set. For example:

```yaml
# Define mappings using the sets option
mapping:
  - name: product_codes_a
    source: product_code_mapping_1.yaml
  - name: product_codes_b
    source: product_code_mapping_2.yaml
  - name: product_codes
    sets: [product_codes_a, product_codes_b]
```
In this example, the product_codes mapping is defined using the sets option, which specifies a list of mappings to be combined into a single set. The mappings in the sets list will be merged together, with mappings specified later in the list overwriting mappings with the same keys from earlier in the list.
