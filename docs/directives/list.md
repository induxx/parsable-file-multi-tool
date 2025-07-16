The list directive is used to define a list of values in a YAML configuration file. Lists can hold multi-dimensional arrays, unlike the mapping directive which is used for key-value pairs. Lists are useful for storing data that will be used in processing actions in a pipeline.

### Static lists

Here is an example of how you could use the list directive to define a list of attributes:

```yaml
# Define a static list of attributes
list:
  - name: attributes
    values:
      - code: sku
      - code: EAN
      - code: SALES_PRICE
      - code: SUPPLIER
      - code: SUPPLIER_SKU
      - code: Klium_price
      - code: STOCK_CODE
      - code: SALES_QUANTITY
      - code: LENGTH_CM
        label-nl_BE: Logistic length
      - code: HEIGHT_CM
        label-nl_BE: Logistic height
      - code: WIDTH_CM
        label-nl_BE: Logistic width
      - code: WEIGHT_KG
        label-nl_BE: Logistic weight
```
In this example, the list directive is used to define a list of attributes with hard-coded values. The name field is used to give the list a unique identifier, which can be used to reference the list in other parts of the configuration file. The values field is used to specify the actual values in the list.

Once a list has been defined, it can be used in a pipeline like this:

```yaml
# Use the list in a pipeline
pipeline:
  input:
    reader:
      type: list
      list: attributes
  actions:
    # Some processing actions go here
  output:
    writer:
      type: csv
      filename: attributes.csv
```
In this example, the list directive is used as the input source for the pipeline. The reader type is set to list, and the list field specifies the name of the list to use as the input data. The pipeline can then perform some processing actions on the data from the list, and write the resulting data to a CSV file using the writer output.

### Dynamic lists

A dynamic list type is a type of list in a configuration file that is created based on data from an external source, rather than being hard-coded into the configuration file. This allows the list to be updated or changed without modifying the configuration file itself.

In the context of yaml configuration files, dynamic list types are specified using the list directive, which allows you to specify the source of the data and the source_command that should be used to create the list.

There are several different types of dynamic list types available, depending on the source_command that is used. For example, the key_value_pair source command creates a list of key-value pairs by reading data from a file and using the specified key and value fields as the keys and values in the list. The filter source command creates a list by reading data from a file and only including the specified return_value field for rows that meet the specified criteria.

Dynamic list types can be useful when you need to create a list based on data that is stored in an external source and may change over time, as it allows you to update the list without modifying the configuration file.

This example that reads in data from a CSV file called customer_projects.csv, maps the customer_id field to a username field using the key_value_pair list type, and then renames the customer_id field to username using the key_mapping action:

```yaml
# Define a key-value pair list that maps customer IDs to usernames
list:
  - name: 'customer_id_to_username_mapping'
    source: customer_info.csv
    source_command: key_value_pair
    options:
      key: customer_id
      value: username

# Read in data from the customer_projects.csv file and map the customer_id field to a username field using the customer_id_to_username_mapping list
pipeline:
  input:
    reader:
      type: csv
      filename: customer_projects.csv
  actions:
    map_customer_id_to_username:
      action: key_mapping
      list: 'customer_id_to_username_mapping'
    rename_customer_id_to_username:
      action: rename
      from: customer_id
      to: username
  output:
    writer:
      type: csv
      filename: customer_projects_mapped.csv
```
This pipeline will read in the customer_projects.csv file, apply the key mappings specified in the key_mapping action to map the customer_id field to the corresponding username field using the customer_id_to_username_mapping list, and then rename the customer_id field to username using the rename action. The resulting data will be written to a new CSV file called customer_projects_mapped.csv, which will contain a username field in place of the original customer_id field.

Here is an example of how you might use a dynamic list in combination with the statement action to filter a list of customer data based on whether or not the customer is considered "active":

```yaml
# Define the dynamic list that will contain the IDs of active customers
list:
  - name: 'active_customer_ids'
    source: customer_data.csv
    source_command: filter
    options:
      criteria:
        is_active: '1'
      return_value: customer_id

# Define the pipeline that will process the customer data
pipeline:
  input:
    reader:
      type: csv
      filename: customer_data.csv
  actions:
    # Use the statement action to filter the customer data based on whether or not the customer is considered active
    filter_active_customers:
      action: statement
      when:
        field: customer_id
        operator: IN_LIST
        context:
          list: active_customer_ids
      then:
        field: is_active
        state: '1'
  output:
    writer:
      type: csv
      filename: active_customers.csv
```

In this example, the dynamic list active_customer_ids is created by reading data from the customer_data.csv file and only including the customer_id field for rows where the is_active field is set to '1'. The pipeline then reads in the customer_data.csv file and uses the statement action to filter the data based on whether or not the customer_id field is included in the active_customer_ids list. Finally, the filtered customer data is written to a new active_customers.csv file.