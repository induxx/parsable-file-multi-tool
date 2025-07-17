# Transformation Workflow Guide

This comprehensive guide explains how to design, execute, and manage transformation workflows using the parsable-file-multi-tool. It covers pipeline concepts, data flow patterns, and best practices for building robust data transformation processes.

## Overview

Transformations in the parsable-file-multi-tool follow a structured workflow that processes data through configurable pipelines. Each transformation consists of three main components:

1. **Input Sources**: Raw data files or API endpoints
2. **Processing Pipeline**: Series of actions that transform the data
3. **Output Destinations**: Processed data files or external systems

The tool supports both simple single-step transformations and complex multi-step workflows that can handle enterprise-scale data processing requirements.

## Core Concepts

### Pipeline Architecture

A pipeline represents a complete data processing workflow with three main sections:

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: source_data.csv
  actions:
    # Data transformation steps
  output:
    writer:
      type: jsonl
      filename: processed_data.jsonl
```

**Key Components:**
- **Reader**: Defines how to read input data (CSV, JSON, XLSX, API, etc.)
- **Actions**: Sequential processing steps that transform the data
- **Writer**: Defines how to output processed data

### Data Flow Patterns

#### 1. Linear Pipeline
Simple sequential processing where data flows through actions in order:

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: customers.csv
  actions:
    clean_data:
      action: remove
      keys: [temp_field, debug_info]
    standardize_names:
      action: format
      field: [first_name, last_name]
      functions: [trim, title_case]
    validate_email:
      action: statement
      when:
        field: email
        operator: 'NOT_CONTAINS'
        state: '@'
      then:
        field: email
        state: 'invalid@example.com'
  output:
    writer:
      type: csv
      filename: clean_customers.csv
```

#### 2. Multi-Step Workflows
Complex transformations broken into manageable steps:

```yaml
# main-STEPS.yaml
transformation_steps:
  - fetch-raw-data.yaml
  - clean-and-validate.yaml
  - enrich-with-external-data.yaml
  - format-for-output.yaml
  - push-to-destination.yaml
```

#### 3. Conditional Processing
Data routing based on content or conditions:

```yaml
actions:
  route_by_type:
    action: statement
    when:
      field: record_type
      operator: 'EQUALS'
      state: 'product'
    then:
      field: category
      state: 'catalog_item'
```

### Context and Configuration

#### Global Context
Shared variables and settings across all transformation steps:

```yaml
context:
  api_base_url: 'https://api.example.com'
  batch_size: 1000
  output_format: 'jsonl'
  debug_mode: false
```

#### Aliases
Reusable references for file names and patterns:

```yaml
aliases:
  source_pattern: 'data_*.csv'
  output_file: 'processed_data.jsonl'
  backup_location: 'archive/'
```

#### Secrets Management
Sensitive configuration stored separately:

```yaml
# secrets.yaml (not version controlled)
account:
  - name: production_api
    domain: https://prod-api.example.com
    client_id: your_client_id
    secret: your_secret_key
    username: api_user
    password: secure_password
```

## Common Transformation Patterns

### 1. Data Cleaning and Standardization

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: raw_customer_data.csv
  actions:
    # Remove empty or invalid records
    filter_valid_records:
      action: statement
      when:
        field: email
        operator: 'NOT_EMPTY'
      then:
        action: retain
    
    # Standardize phone numbers
    format_phone:
      action: format
      field: phone
      functions: [replace]
      search: ['(', ')', '-', ' ']
      replace: ''
    
    # Normalize names
    standardize_names:
      action: format
      field: [first_name, last_name]
      functions: [trim, title_case]
    
    # Add computed fields
    create_full_name:
      action: concat
      fields: [first_name, last_name]
      separator: ' '
      target: full_name
  output:
    writer:
      type: csv
      filename: clean_customer_data.csv
```

### 2. Data Enrichment

```yaml
# Step 1: Fetch base data
pipeline:
  input:
    reader:
      type: csv
      filename: products.csv
  actions:
    prepare_for_enrichment:
      action: copy
      from: sku
      to: lookup_key
  output:
    writer:
      type: csv
      filename: products_prepared.csv

---
# Step 2: Enrich with external data
pipeline:
  input:
    reader:
      type: csv
      filename: products_prepared.csv
  actions:
    add_category_info:
      action: key_mapping
      file: category_mapping.csv
      source_key: category_code
      target_key: category_name
      
    add_pricing:
      action: extension
      extension: PricingLookupExtension
      parameters:
        api_endpoint: pricing_service
        key_field: sku
  output:
    writer:
      type: jsonl
      filename: enriched_products.jsonl
```

### 3. Format Conversion and Export

```yaml
pipeline:
  input:
    reader:
      type: jsonl
      filename: processed_data.jsonl
  actions:
    # Flatten nested structures
    flatten_attributes:
      action: expand
      field: attributes
      
    # Format for target system
    format_for_akeneo:
      action: akeneo_value_formatter
      locale: en_US
      scope: ecommerce
      
    # Apply final transformations
    finalize_export:
      action: format
      field: [created_at, updated_at]
      functions: [date]
      format: 'Y-m-d H:i:s'
  output:
    writer:
      type: csv
      filename: akeneo_import.csv
```

## Running Transformations

### Basic Command Usage

#### Using the Docker Script (Recommended)
```bash
PROJECT=my_project bin/docker/run_example.sh main-STEPS.yaml
```

#### Direct CLI Usage
```bash
bin/console transformation \
  --file examples/my_project/transformation/main-STEPS.yaml \
  --source examples/my_project/sources \
  --workpath examples/my_project/workpath \
  --debug
```

### Command Options

| Option | Short | Description | Example |
|--------|-------|-------------|---------|
| `--file` | `-f` | Path to transformation file | `--file transform.yaml` |
| `--source` | `-s` | Source data directory | `--source ./sources` |
| `--workpath` | `-w` | Working directory for outputs | `--workpath ./workpath` |
| `--addSource` | | Additional source directory | `--addSource ./extra_sources` |
| `--extensions` | | Custom extensions directory | `--extensions ./extensions` |
| `--debug` | `-d` | Enable debug output | `--debug` |
| `--line` | `-l` | Process specific line number | `--line 100` |
| `--try` | `-t` | Limit processing for testing | `--try 50` |
| `--showMappings` | `-m` | Display mapping information | `--showMappings` |

### Project Directory Structure

```
examples/my_project/
├── transformation/
│   ├── main-STEPS.yaml          # Main workflow definition
│   ├── secrets.yaml             # Credentials (not in git)
│   ├── step-1-fetch.yaml        # Individual transformation steps
│   ├── step-2-process.yaml
│   └── step-3-export.yaml
├── sources/                     # Input data files
│   ├── customers.csv
│   ├── products.json
│   └── categories.xlsx
├── workpath/                    # Processing workspace
│   ├── intermediate_file_1.csv  # Generated during processing
│   ├── intermediate_file_2.jsonl
│   └── final_output.csv
├── added_sources/               # Additional data sources
│   └── reference_data.csv
└── extensions/                  # Custom PHP extensions
    └── CustomProcessorExtension.php
```

## Best Practices

### 1. Workflow Design

**Start Simple**: Begin with single-step transformations and gradually add complexity.

```yaml
# Good: Simple, focused transformation
pipeline:
  input:
    reader:
      type: csv
      filename: input.csv
  actions:
    clean_data:
      action: remove
      keys: [unwanted_field]
  output:
    writer:
      type: csv
      filename: output.csv
```

**Use Multi-Step for Complex Workflows**: Break complex processes into logical steps.

```yaml
# Good: Logical separation of concerns
transformation_steps:
  - extract-raw-data.yaml      # Data extraction
  - validate-and-clean.yaml    # Data quality
  - enrich-and-transform.yaml  # Business logic
  - format-and-export.yaml     # Output formatting
```

### 2. Data Quality

**Validate Early**: Check data quality at the beginning of your pipeline.

```yaml
actions:
  validate_required_fields:
    action: statement
    when:
      field: [id, name, email]
      operator: 'NOT_EMPTY'
    then:
      action: retain
```

**Handle Errors Gracefully**: Plan for data inconsistencies.

```yaml
actions:
  handle_missing_dates:
    action: statement
    when:
      field: created_date
      operator: 'EMPTY'
    then:
      field: created_date
      state: '1970-01-01'
```

### 3. Performance Optimization

**Use Appropriate Batch Sizes**: Configure processing limits for large datasets.

```yaml
context:
  try: 1000  # Process first 1000 records for testing
  batch_size: 500  # Process in batches of 500
```

**Optimize File Formats**: Choose efficient formats for intermediate files.

```yaml
# JSONL is often faster for large datasets
output:
  writer:
    type: jsonl  # Instead of CSV for better performance
    filename: intermediate_data.jsonl
```

### 4. Security and Configuration

**Separate Secrets**: Never commit credentials to version control.

```yaml
# secrets.yaml (gitignored)
account:
  - name: production_db
    host: db.example.com
    username: db_user
    password: secure_password
```

**Use Environment-Specific Configs**: Maintain separate configurations for different environments.

```
transformation/
├── main-STEPS.yaml
├── secrets-dev.yaml
├── secrets-staging.yaml
└── secrets-prod.yaml
```

### 5. Testing and Debugging

**Test with Small Datasets**: Use the `--try` option for development.

```bash
# Test with first 10 records
bin/console transformation -f transform.yaml -s sources -w workpath --try 10
```

**Enable Debug Mode**: Use detailed logging during development.

```bash
# Enable debug output
bin/console transformation -f transform.yaml -s sources -w workpath --debug
```

**Validate Intermediate Results**: Check outputs at each step.

```yaml
# Add debug output in your pipeline
actions:
  debug_checkpoint:
    action: debug
    message: "Records processed: {count}"
    fields: [id, status]
```

## Troubleshooting Common Issues

### Memory Issues
- Use streaming readers for large files
- Process data in smaller batches
- Optimize action sequences to reduce memory usage

### Performance Problems
- Profile your transformations with `--debug`
- Use appropriate file formats (JSONL vs CSV)
- Consider parallel processing for independent steps

### Data Quality Issues
- Implement validation early in your pipeline
- Use debug actions to inspect data at key points
- Create separate quality assurance steps

## Related Topics

- [Actions Reference](./reference/actions/) - Complete list of available actions
- [Directives Reference](./reference/directives/) - Pipeline configuration options
- [CLI Commands](./reference/cli-commands.md) - Complete command reference
- [Debugging Guide](./user-guide/debugging.md) - Troubleshooting and optimization
- [Examples](../examples/) - Practical transformation examples

