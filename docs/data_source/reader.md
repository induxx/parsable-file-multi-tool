
reading from files is an important part of the pipeline process as it allows for the input of data for processing. In a pipeline, the input directive specifies how the data will be read and from where it will be read. The reader sub-directive specifies the type of reader to use for reading the data.

### File based reading

For example, to read data from a CSV file, the type field in the reader sub-directive should be set to csv and the filename field should specify the name and location of the CSV file.

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: data.csv
  actions:
    # processing actions go here
  output:
    # output configuration goes here
```
Similarly, to read data from a JSON file, the type field in the reader sub-directive should be set to json and the filename field should specify the name and location of the JSON file.

```yaml
pipeline:
  input:
    reader:
      type: json
      filename: data.json
  actions:
    # processing actions go here
  output:
    # output configuration goes here
```
We could also start reading from a list, the list directive will explains this in more detail

```yaml
pipeline:
  input:
    reader:
      type: list
      list: attributes
  actions:
    # processing actions go here
  output:
    # output configuration goes here
```

### API based Reading

The http directive allows you to read data from a REST API endpoint using the HTTP GET method. The type field specifies the type of request to make, in this case it is a rest_api request. The account field specifies the account to use for the request, and the endpoint field specifies the endpoint of the API to send the request to. The method field specifies the HTTP method to use for the request, and in this case it is set to GET. The filters field allows you to specify any filters to apply to the request. In this example, a filter is set to limit the request to a specific list of sku values.

Note that when using the Induxx Middleware Application and checked the option `Source API Credentials`, you can use the code `source_resource` as account code.

```yaml
pipeline:
  input:
    http:
      type: rest_api
      account: alias_to_your_account
      endpoint: products
      method: GET
```

This pipeline reads data from a REST API endpoint using the GET HTTP verb. The account field specifies which account to use for authenticating the request. The endpoint field specifies the API endpoint to access, and the filters field specifies any query parameters or filters to apply to the request. In this example, the identifier filter is set to a list called sku-list, which would contain a list of SKUs to filter the API response by.

### Join

The join directive allows you to read from multiple files and combine them into a single file. In this example, the pipeline reads from the products_masterdata.csv file and then joins data from several other files based on the link and link_join fields. The return field specifies which columns from the joined files should be included in the final output.

Here's an example of how you might use the join directive in a more simplified pipeline:

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: customer_data.csv
      join:
        - filename: order_data.csv
          type: csv
          link: customer_id
          link_join: customer_id
          return: [order_id, order_total]
  output:
    writer:
      type: csv
      filename: customer_orders.csv
```

In this example, the pipeline reads from a customer_data.csv file and joins data from an order_data.csv file based on the customer_id field. The return field specifies that the order_id and order_total columns should be included in the final output file, customer_orders.csv.

Using the join directive allows you to easily combine data from multiple sources and can be a powerful tool for data processing and analysis.

### Filter

The filter directive is a way to specify criteria for selecting specific rows from a file during the reading process.

Here is an example using the filter directive in combination with the statement action:

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: customer_data.csv
      filter:
        GENDER: 'MALE'
  actions:
    # processing actions go here
  output:
    writer:
      type: csv
      filename: male_customers.csv
```

In this example, the reader reads in the customer_data.csv file and filters out any rows where the GENDER is male. Finally, the resulting data is written to a new CSV file called male_customers.csv.

### Multi dimensional array to columns

When using API based reading, for example using Akeneo as a source. The data that we get back is a multi dimensional array. To convert this data to columns we use the next code block after our input statement:

```yaml
  decoder:
    parse:
      unflatten:
        separator: '-'
      nullify: ~
```

This code will convert the array keys to flat data that then can be used for the transformations.
For example, this array:

```php
[ "values" => [
    "quantity" => [
        [
            "locale" => null,
            "scope" => null,
            "data" => 100
        ]
    ]
]
```

Would result in 3 columns named `values-quantity-0-locale`, `values-quantity-0-scope` and `values-quantity-0-data`.
As you can see the array keys are split by `-` but can be defined by the action.

