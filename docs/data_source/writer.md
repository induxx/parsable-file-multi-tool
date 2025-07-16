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

### Writer Parameters

#### `skip_if_exists`
The `skip_if_exists` flag is used in the writer section to optimize processing by avoiding redundant operations. 
When set to `true`, the pipeline will skip fetching data if the specified output file already exists. 
This is particularly useful for workflows where data does not need to be re-fetched or re-processed if it has already been written to the file, for example running with debugger.

**Example:**
```yaml
pipeline:
  input:
    http:
      type: rest_api
      account: '%akeneo_read_connection%'
      endpoint: '%endpoint%'
      method: GET
  output:
    writer:
      type: jsonl
      filename: 'read/akeneo_%endpoint%.jsonl'
      skip_if_exists: true
```

In this example, the pipeline will skip fetching data if the file `read/akeneo_%endpoint%.jsonl` already exists.

#### `buffer_file`
The `buffer_file` parameter is used in the writer section when the type is `rest_api`. It specifies a file where API payloads will be stored in JSONL format. This is useful for debugging, logging, or reusing payloads in subsequent operations.

**Example:**
```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: '%compositions%'
  output:
    http:
      type: rest_api
      account: target_resource
      endpoint: reference-entities
      method: PATCH
      buffer_file: 'compositions.push.jsonl'
```

In this example, the API payloads for the `PATCH` method will be stored in the file `compositions.push.jsonl`. 
This allows for better traceability and reuse of the payloads.

### REST Endpoint Support

The writer section supports multiple REST endpoints, enabling flexible integration with external APIs. These endpoints are defined in the `src/Component/Akeneo/Client` directory and provide various options for interacting with Akeneo resources.

#### Supported Akeneo Endpoints
- **Assets**: "assets" Manage asset families and individual assets.
- **Categories**: "categories" Retrieve and manage categories.
- **Reference Entities**: "reference-entities" Handle reference entities and their attributes.
- **Attributes**: "attributes" Access and manage product attributes.
- **Attribute Options**: "attribute-options" Handle options for attributes, such as select or multi-select attributes.
- **Attribute Groups**: "attribute-groups" Manage groups of attributes for better organization.
- **Families**: "families" Work with product families, including their attributes and attribute groups.
- **Family Variants**: "family-variants" Manage family variants, which allow for different configurations of product families.
- **Products**: "products" Manage product data, including models and variants.
- **Product Models**: "product-models" Work with product models, including their variants.
These endpoints allow for granular control over data retrieval and manipulation, making it easier to integrate Akeneo resources into your workflows.

### Type Support

The writer section supports various output types, ensuring compatibility with different formats and systems. Below are the supported types:

- **CSV**: Write data to CSV files for easy sharing and analysis.
- **JSON**: Output data in JSON format for API compatibility.
- **JSONL**: Store data in JSON Lines format, ideal for streaming and large datasets.
- **XML**: Generate XML files for structured data representation.
- **YAML**: Write data in YAML format for configuration purposes.
- **XLSX**: Export data to Excel files for reporting and analysis.

Each type is designed to meet specific use cases, from debugging and logging to integration with external systems.

