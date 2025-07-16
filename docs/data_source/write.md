### File based output

##### CSV / Excel

```yaml
pipeline:
  input:
    reader:
      type: list
      list: 'customer_data'
  output:
    writer:
      type: csv
      filename: customer_file.csv
```
This pipeline reads from a list called customer_data and writes the data to a CSV file called customer_file.csv.

##### Json

Another example is writing to a file using the JSON type:

```yaml
pipeline:
  input:
    reader:
      type: list
      list: 'product_data'
  output:
    writer:
      type: json
      filename: product_file.json
```
This pipeline reads from a list called product_data and writes the data to a JSON file called product_file.json.

### API based output

Note that when using the Induxx Middleware Application and checked the option `Target API Credentials`, you can use the code `target_resource` as account code.

```yaml
  output:
    http:
        type: rest_api
        account: account_code
        endpoint: products
        method: MULTI_PATCH
        buffer_file: 'product_updates.jsonl'
```

