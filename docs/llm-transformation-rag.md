# YAML Transformation RAG Guide

This document is a single, reusable reference for LLMs to generate valid transformation YAML files for parsable-file-multi-tool. It summarizes the expected structure, common directives, and patterns based on the current docs/ and examples/.

## 1) Core idea

A transformation file is YAML that defines how data is read, transformed, and written. The minimum usable file is a `pipeline` with `input`, `actions`, and `output`. More complex setups add `context`, `aliases`, `mapping`, `list`, `filter`, `blueprint`, or `transformation_steps`.

## 2) Minimal pipeline skeleton

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: 'source.csv'
      options:
        delimiter: ','
        enclosure: '"'
        header: true

  actions:
    step_name:
      action: retain
      keys: [id, name, email]

  output:
    writer:
      type: csv
      filename: 'output.csv'
      options:
        delimiter: ','
        enclosure: '"'
        header: true
```

## 3) Top-level sections (common)

These sections are optional and can be combined when needed.

```yaml
aliases:        # reusable named values, paths, and patterns
context:        # runtime values, environment-specific settings
account:        # API credentials (prefer secrets file)
blueprint:      # converter blueprints
mapping:        # key/value mapping sets
list:           # list/lookup sets
filter:         # filters to fetch values from source data
pipeline:       # input -> actions -> output
transformation_steps:  # multi-file orchestration
```

## 4) Context and aliases

Use context values and aliases to avoid repeating paths and settings. Values are referenced with `%var%` syntax.

```yaml
aliases:
  input_file: 'products.csv'
  output_file: 'processed_products.csv'

context:
  environment: 'production'
  locale: 'en_US'

pipeline:
  input:
    reader:
      type: csv
      filename: '%input_file%'
```

Notes:
- Examples also use `context.workpath` and `context.sources` for source definitions (see `docs/examples/guides/configuration.yaml`).
- Some examples use `source` with `%workpath%` in older files (see `docs/examples/old_source_test.yaml`).

## 5) Accounts and secrets

Define API accounts under `account`. Do not hardcode real credentials in committed files.

```yaml
account:
  - name: "akeneo-production"
    domain: "https://your-akeneo-instance.cloud.akeneo.com"
    client_id: "client_id"
    secret: "client_secret"
    username: "api_user"
    password: "secure_password"
```

For local runs, store secrets in separate files (see the pattern in `examples/*/transformation/secrets.yaml`).

## 6) Mappings, lists, and filters

Mappings are key/value sets; lists are arrays of values from sources; filters extract values from structured sources.

```yaml
mapping:
  - name: local_measurements
    map:
      mm: MILLIMETER

list:
  - name: attributes_with_booleans
    source: attribute.csv
    source_command: filter
    options:
      return_value: code
      criteria:
        type:
          - pim_catalog_boolean

filter:
  - name: get_attribute_by_code
    source: attribute.csv
    filter:
      code: '$code'
```

Reference:
- `docs/examples/guides/configuration.yaml`
- `docs/examples/new_source_test.yaml`

## 7) Actions (common patterns)

Actions are ordered by YAML key and applied in sequence. The action name is arbitrary; `action` is required.

Common patterns from examples:

```yaml
actions:
  standardize_field_names:
    action: key_mapping
    mapping:
      item_code: sku
      product_title: name

  format_price:
    action: format
    field: price
    template: '$%.2f'

  create_url_slug:
    action: format
    field: name
    template: '%s'
    to: url_slug
    filters:
      - lowercase
      - slug

  categorize_inventory:
    action: calculate
    field: inventory_status
    expression: |
      if (inventory_count > 20) {
        return 'In Stock';
      }
      return 'Low Stock';

  validate_email:
    action: statement
    conditions:
      - field: email
        operator: 'REGEX'
        value: '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$'
        message: 'Invalid email format'
```

Reference:
- `docs/examples/basic/standardize-products.yaml`
- `docs/examples/basic/validate-customers.yaml`

## 8) Readers and writers

Reader and writer types vary by use case. Examples show:

- CSV input/output: `type: csv`
- XLSX input: `type: xlsx`
- List input: `type: list`
- XML output with `options` (e.g., `start`, `xpath`)
- API pulls often write into `read/` under the workpath (e.g., `read/akeneo_products.jsonl`)

Example with list reader:

```yaml
list:
  - name: family_codes
    source: family_1
    source_command: filter
    options:
      criteria:
        code: UNIQUE
      return_values: [code]

pipeline:
  input:
    reader:
      type: list
      list: family_codes
  output:
    writer:
      type: csv
      filename: family_codes_new
```

Reference:
- `docs/examples/new_source_test.yaml`

## 9) Blueprints and encoders (optional)

Some transformations declare converter blueprints and use them in the pipeline encoder.

```yaml
blueprint:
  - name: product/xml
    converter:
      name: induxx/product/xml
      options:
        container: Product

pipeline:
  input:
    reader:
      type: csv
      filename: product.csv
  encoder:
    blueprint: akeneo/csv/product
  output:
    writer:
      type: xml
      filename: product.xml
      options:
        xpath: Products
```

Reference:
- `docs/examples/guides/configuration.yaml`

## 10) Multi-step workflows

Use `transformation_steps` to orchestrate multiple YAML files. The main file lists step files in order.

```yaml
transformation_steps:
  - transformation_in_steps_1.yaml
  - transformation_in_steps_2.yaml
```

Reference:
- `docs/examples/guides/transformation_in_steps.yaml`
- `docs/examples/transformation_in_steps_main.yaml`

## 11) CLI usage (for validation)

```bash
bin/docker/console transformation \
  --file examples/basic/standardize-products.yaml \
  --source examples/basic \
  --workpath var/out
```

Reference:
- `docs/reference/cli-commands.md`

## 12) Authoring checklist

- Use YAML with top-level `pipeline` (or `transformation_steps` for orchestration).
- Prefer `aliases` and `context` for reusable values.
- Keep credentials out of committed files; use secrets files.
- Validate structure against examples under `examples/`.
- Use `bin/docker/console transformation` to test with sample data.

## 13) Converter placement (read vs write)

Converters are directional:
- A converter on read adapts incoming data into a standard data-structure (convert).
- A converter on write reverts that data-structure into the output format (revert).

This allows API => CSV or CSV => API flows by standardizing in the middle.

## 14) Action compatibility

Actions are safest on flat data. Many actions can work with structured data, but not all. When in doubt, flatten or convert into a flat structure before applying complex action chains.
