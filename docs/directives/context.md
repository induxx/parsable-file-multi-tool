The `context` directive is used to define the environment and resources required for executing transformation steps. It provides essential configuration details that are shared across multiple steps in the transformation process.

### Example Usage
In the transformation file `main-STEPS.yaml`, the `context` directive is used as follows:

```yaml
context:
  akeneo_connection: target_resource
```

This example specifies a connection to a target resource, which is likely used by subsequent transformation steps to interact with external systems or APIs.

### Purpose
The `context` directive:
- Sets up shared configurations or variables that are accessible throughout the transformation process.
- Ensures consistency and reduces redundancy by centralizing common settings.
- Facilitates integration with external systems, such as APIs or databases, by defining connection details.

### Practical Application
In the example provided, the `context` directive defines an `akeneo_connection` key, which might be used to establish a connection to an Akeneo resource. This connection can then be referenced in transformation steps like data retrieval, transformation, and pushing data to the target system.

### Additional Examples
The `context` directive can include various keys that are later matched and replaced during the transformation process. Here are more examples:

#### File Naming
```yaml
context:
  akeneo_file_csv_filename: akeneo_full_products.jsonl
  product_file: akeneo_products.jsonl
  attribute_file: akeneo_attributes.jsonl
```
These keys define filenames that are used in the transformation steps for reading or writing data.

#### Query Strings
```yaml
context:
  querystring: '%s?scope=nuorder&search={"categories":[{"operator":"IN","value":["nuorder"]}],"sales_status":[{"operator":"NOT IN","value":["8","08"]}]}'

# a different transformation step file

pipeline:
  input:
    http:
      type: rest_api
      account: target_resource
      endpoint: products
      method: GET
      limiters:
        querystring: '%querystring%'
```

This example specifies a query string used for API calls, enabling dynamic filtering and data retrieval.

#### Date Formatting
```yaml
context:
  datetime_file_format: 'ymd-His'
  date_format: 'YmdHis'
```
These keys define date formats used in filenames or data processing.

#### API Connections
```yaml
context:
  akeneo_api_account_name: 'source_resource'
  akeneo_read_connection: target_resource
  akeneo_write_connection: target_resource
```

These keys specify API account names and connections for interacting with external systems.

#### Reserved Context Keys

The following keys are reserved for internal use and should not be used as custom context keys:


- `transformation_file` # the initial transformation file
- `last_completed_operation_datetime` # the last completed operation datetime
- `operation_create_datetime` # the operation create datetime
- `app_querystring` # the query string used for API calls coming from the app filters
- `datetime_file_format` # the datetime file format
- `date_format` # the date format
- `debug` # the debug flag
- `try` # a limit to try
- `line` # the line number to debug
- `show_mappings` # the show generated mappings flag


