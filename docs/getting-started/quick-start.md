# Quick Start Guide

This guide will walk you through your first transformation using the Parsable File Multi-Tool. You'll learn the basic concepts and create a working example in just a few minutes.

## Prerequisites

Before starting, ensure you have:
- Completed the [Installation Guide](installation.md)
- Basic understanding of CSV files and data transformation concepts
- A text editor for viewing configuration files

## Understanding Key Concepts

### Transformation Pipeline
A transformation pipeline consists of:
- **Input**: Where your data comes from (CSV, XML, API, etc.)
- **Actions**: What transformations to apply to your data
- **Output**: Where to save the transformed data

### Configuration Files
Transformations are defined in YAML configuration files that specify:
- Data sources and destinations
- Transformation rules and actions
- Field mappings and data processing steps

## Your First Transformation

Let's create a simple transformation that processes product data and extracts specific fields.

### Step 1: Prepare Sample Data

Create a sample CSV file called `sample-products.csv`:

```csv
sku,name,price,category,brand,description
PROD001,Wireless Headphones,99.99,Electronics,AudioTech,High-quality wireless headphones with noise cancellation
PROD002,Running Shoes,79.99,Footwear,SportMax,Comfortable running shoes for daily exercise
PROD003,Coffee Mug,12.99,Kitchen,HomeWare,Ceramic coffee mug with ergonomic handle
PROD004,Laptop Stand,45.99,Electronics,DeskPro,Adjustable laptop stand for better ergonomics
PROD005,Water Bottle,19.99,Sports,HydroLife,Insulated water bottle keeps drinks cold for 24 hours
```

Save this file in your project's `examples/` directory.

### Step 2: Create Your First Transformation

Create a transformation configuration file called `my-first-transformation.yaml`:

```yaml
# Basic transformation configuration
pipeline:
  # Define input source
  input:
    reader:
      type: csv
      filename: 'sample-products.csv'
      options:
        delimiter: ','
        enclosure: '"'
        header: true

  # Define transformation actions
  actions:
    # Copy SKU to a new field called 'identifier'
    copy_sku_to_identifier:
      action: copy
      from: sku
      to: identifier

    # Format price to include currency
    format_price:
      action: format
      field: price
      template: '$%s USD'

    # Create a short description from the first 50 characters
    create_short_description:
      action: format
      field: description
      template: '%.50s...'
      to: short_description

    # Remove fields we don't need
    remove_unwanted_fields:
      action: remove
      keys: [description]

    # Rename category to product_category
    rename_category:
      action: rename
      from: category
      to: product_category

  # Define output destination
  output:
    writer:
      type: csv
      filename: 'transformed-products.csv'
      options:
        delimiter: ','
        enclosure: '"'
        header: true
```

Save this file in your project's `examples/` directory.

### Step 3: Run Your First Transformation

Execute the transformation using the command line:

**Using Docker (recommended):**
```bash
bin/docker/console transformation \
  --file examples/my-first-transformation.yaml \
  --source examples \
  --workpath examples/output
```

**Using native PHP:**
```bash
php bin/console transformation \
  --file examples/my-first-transformation.yaml \
  --source examples \
  --workpath examples/output
```

### Step 4: Examine the Results

Check the output file `examples/output/transformed-products.csv`:

```csv
sku,name,price,brand,identifier,short_description,product_category
PROD001,Wireless Headphones,$99.99 USD,AudioTech,PROD001,High-quality wireless headphones with noise canc...,Electronics
PROD002,Running Shoes,$79.99 USD,SportMax,PROD002,Comfortable running shoes for daily exercise...,Footwear
PROD003,Coffee Mug,$12.99 USD,HomeWare,PROD003,Ceramic coffee mug with ergonomic handle...,Kitchen
PROD004,Laptop Stand,$45.99 USD,DeskPro,PROD004,Adjustable laptop stand for better ergonomics...,Electronics
PROD005,Water Bottle,$19.99 USD,HydroLife,PROD005,Insulated water bottle keeps drinks cold for 24...,Sports
```

## Understanding What Happened

Let's break down each transformation action:

1. **copy_sku_to_identifier**: Created a new `identifier` field with the same value as `sku`
2. **format_price**: Added currency formatting to the price field
3. **create_short_description**: Created a truncated version of the description
4. **remove_unwanted_fields**: Removed the original `description` field
5. **rename_category**: Changed `category` field name to `product_category`

## Common Actions Reference

Here are the most frequently used actions:

### Copy Action
```yaml
copy_field:
  action: copy
  from: source_field
  to: destination_field
```

### Rename Action
```yaml
rename_field:
  action: rename
  from: old_name
  to: new_name
```

### Remove Action
```yaml
remove_fields:
  action: remove
  keys: [field1, field2, field3]
```

### Format Action
```yaml
format_field:
  action: format
  field: target_field
  template: 'Formatted: %s'
  to: new_field  # optional, modifies original field if omitted
```

### Key Mapping Action
```yaml
map_fields:
  action: key_mapping
  mapping:
    old_field1: new_field1
    old_field2: new_field2
    old_field3: new_field3
```

## Debugging Your Transformations

### Debug Mode
Add `--debug` to see detailed processing information:

```bash
bin/docker/console transformation \
  --file examples/my-first-transformation.yaml \
  --source examples \
  --workpath examples/output \
  --debug
```

### Process Limited Records
Test with only the first few records using `--try`:

```bash
bin/docker/console transformation \
  --file examples/my-first-transformation.yaml \
  --source examples \
  --workpath examples/output \
  --try 3
```

### Process Specific Line
Test a specific record using `--line`:

```bash
bin/docker/console transformation \
  --file examples/my-first-transformation.yaml \
  --source examples \
  --workpath examples/output \
  --line 2
```

## Working with Different File Formats

### XML Input
```yaml
input:
  reader:
    type: xml
    filename: 'products.xml'
    xpath: '//product'  # XPath to extract records
```

### JSON Output
```yaml
output:
  writer:
    type: json
    filename: 'products.json'
    options:
      pretty_print: true
```

## Advanced Example: Multi-Step Transformation

Create a more complex transformation with multiple processing steps:

```yaml
# Advanced transformation with multiple steps
aliases:
  input_file: 'sample-products.csv'
  output_file: 'processed-products.csv'

pipeline:
  input:
    reader:
      type: csv
      filename: 'input_file'
      options:
        delimiter: ','
        header: true

  actions:
    # Step 1: Data validation
    validate_required_fields:
      action: statement
      conditions:
        - field: sku
          operator: 'NOT_EMPTY'
        - field: name
          operator: 'NOT_EMPTY'
        - field: price
          operator: 'NUMERIC'

    # Step 2: Data enrichment
    calculate_price_category:
      action: calculate
      field: price_category
      expression: |
        if (price < 20) {
          return 'Budget';
        } else if (price < 50) {
          return 'Mid-range';
        } else {
          return 'Premium';
        }

    # Step 3: Text processing
    clean_description:
      action: format
      field: description
      template: '%s'
      filters:
        - trim
        - uppercase_first

    # Step 4: Field organization
    organize_fields:
      action: key_mapping
      mapping:
        sku: product_id
        name: product_name
        price: unit_price
        category: product_category
        brand: manufacturer
        description: product_description

  output:
    writer:
      type: csv
      filename: 'output_file'
      options:
        delimiter: ';'
        enclosure: '"'
        header: true
```

## Next Steps

Now that you've completed your first transformation, explore these topics:

### Learn More Actions
- [Actions Reference](../actions/) - Complete list of available actions
- [Directives Documentation](../directives/) - Advanced configuration options
- [Converters Guide](../converters/) - Working with different data formats

### Advanced Topics
- [Configuration Guide](configuration.md) - Account setup and context parameters
- [User Guide](../user-guide/) - Comprehensive usage documentation
- [Examples](../examples/) - Real-world transformation scenarios

### Integration Examples
- [Akeneo PIM Integration](../examples/akeneo-integration.md)
- [API Data Processing](../examples/api-processing.md)
- [Multi-format Conversions](../examples/format-conversions.md)

## Troubleshooting

### Common Issues

**File not found error:**
- Ensure file paths are relative to the `--source` directory
- Check file permissions and existence

**Transformation fails:**
- Use `--debug` mode to see detailed error information
- Validate your YAML syntax
- Check field names match your input data

**Memory issues with large files:**
- Use `--try` to process smaller batches
- Increase PHP memory limit: `bin/docker/php -d memory_limit=2G bin/console ...`

**Output not as expected:**
- Review action order - actions execute sequentially
- Use debug mode to inspect intermediate results
- Verify field names and data types

### Getting Help

- Check the [Troubleshooting Guide](../user-guide/troubleshooting.md)
- Review [Common Patterns](../examples/common-patterns.md)
- Examine working examples in the `examples/` directory

## Summary

You've successfully:
1. ✅ Created your first transformation configuration
2. ✅ Processed CSV data with multiple actions
3. ✅ Generated formatted output
4. ✅ Learned debugging techniques
5. ✅ Explored advanced transformation patterns

The Parsable File Multi-Tool provides powerful capabilities for data transformation, integration, and processing. Continue exploring the documentation to unlock its full potential for your data processing needs.