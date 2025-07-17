# Basic Transformation Examples

This guide provides step-by-step tutorials for common data transformation use cases. Each example includes complete working configurations, sample data, and detailed explanations of concepts and best practices.

## Prerequisites

Before starting these examples, ensure you have:
- Completed the [Installation Guide](../getting-started/installation.md)
- Read the [Quick Start Guide](../getting-started/quick-start.md)
- Basic understanding of CSV files and YAML configuration

## Example 1: Product Data Standardization

This example demonstrates how to standardize product data from different sources into a consistent format.

### Scenario
You have product data from multiple suppliers with different field names and formats. You need to standardize this data for import into your e-commerce system.

### Sample Input Data

Create `examples/basic-examples/supplier-products.csv`:

```csv
item_code,product_title,cost,product_type,manufacturer,item_description,stock_qty
SUP001,Bluetooth Wireless Headphones,89.99,Audio,TechSound,Premium wireless headphones with active noise cancellation,25
SUP002,Athletic Running Sneakers,129.99,Footwear,RunFast,Lightweight running shoes with advanced cushioning technology,15
SUP003,Stainless Steel Water Bottle,24.99,Drinkware,HydroMax,Double-wall insulated bottle keeps beverages cold for 24 hours,50
SUP004,Ergonomic Office Chair,299.99,Furniture,ComfortDesk,Adjustable office chair with lumbar support and breathable mesh,8
SUP005,Wireless Charging Pad,39.99,Electronics,PowerTech,Fast wireless charging pad compatible with Qi-enabled devices,30
```

### Transformation Configuration

Create `examples/basic-examples/standardize-products.yaml`:

```yaml
# Product Data Standardization Example
# This transformation standardizes supplier product data into a consistent format

pipeline:
  input:
    reader:
      type: csv
      filename: 'supplier-products.csv'
      options:
        delimiter: ','
        enclosure: '"'
        header: true

  actions:
    # Step 1: Standardize field names using key mapping
    standardize_field_names:
      action: key_mapping
      mapping:
        item_code: sku
        product_title: name
        cost: price
        product_type: category
        manufacturer: brand
        item_description: description
        stock_qty: inventory_count

    # Step 2: Format price to include currency and proper decimal places
    format_price:
      action: format
      field: price
      template: '$%.2f'

    # Step 3: Create a URL-friendly slug from the product name
    create_url_slug:
      action: format
      field: name
      template: '%s'
      to: url_slug
      filters:
        - lowercase
        - slug

    # Step 4: Categorize inventory levels
    categorize_inventory:
      action: calculate
      field: inventory_status
      expression: |
        if (inventory_count > 20) {
          return 'In Stock';
        } else if (inventory_count > 5) {
          return 'Low Stock';
        } else {
          return 'Limited Stock';
        }

    # Step 5: Create a short description for listings
    create_short_description:
      action: format
      field: description
      template: '%.80s...'
      to: short_description

    # Step 6: Add metadata fields
    add_import_metadata:
      action: copy
      from: sku
      to: import_source
      value: 'supplier_import'

    add_import_date:
      action: format
      field: import_date
      template: '2024-01-15'  # In real scenarios, use current date

  output:
    writer:
      type: csv
      filename: 'standardized-products.csv'
      options:
        delimiter: ','
        enclosure: '"'
        header: true
```

### Expected Output

The transformation produces `standardized-products.csv`:

```csv
sku,name,price,category,brand,description,inventory_count,url_slug,inventory_status,short_description,import_source,import_date
SUP001,Bluetooth Wireless Headphones,$89.99,Audio,TechSound,Premium wireless headphones with active noise cancellation,25,bluetooth-wireless-headphones,In Stock,Premium wireless headphones with active noise cancellation...,supplier_import,2024-01-15
SUP002,Athletic Running Sneakers,$129.99,Footwear,RunFast,Lightweight running shoes with advanced cushioning technology,15,athletic-running-sneakers,Low Stock,Lightweight running shoes with advanced cushioning technology...,supplier_import,2024-01-15
```

### Key Concepts Explained

1. **Key Mapping**: Renames multiple fields at once for consistency
2. **Format Action**: Applies templates and filters to transform field values
3. **Calculate Action**: Uses expressions to create new fields based on existing data
4. **Field Filters**: Built-in functions like `lowercase` and `slug` for text processing
5. **Conditional Logic**: JavaScript-like expressions for complex calculations

## Example 2: Data Validation and Cleansing

This example shows how to validate data quality and clean inconsistent records.

### Sample Input Data

Create `examples/basic-examples/customer-data.csv`:

```csv
customer_id,first_name,last_name,email,phone,registration_date,status
CUST001,John,Doe,john.doe@email.com,+1-555-0123,2024-01-10,active
CUST002,Jane,,jane.smith@email.com,(555) 456-7890,2024/01/11,ACTIVE
CUST003,,Johnson,invalid-email,555.789.0123,01-12-2024,inactive
CUST004,Mike,Wilson,mike.wilson@email.com,+1 555 234 5678,2024-01-13,Active
CUST005,Sarah,Brown,sarah@email.com,,2024-01-14,pending
```

### Transformation Configuration

Create `examples/basic-examples/validate-customers.yaml`:

```yaml
# Customer Data Validation and Cleansing Example
# This transformation validates and cleans customer data

pipeline:
  input:
    reader:
      type: csv
      filename: 'customer-data.csv'
      options:
        delimiter: ','
        enclosure: '"'
        header: true

  actions:
    # Step 1: Validate required fields
    validate_customer_id:
      action: statement
      conditions:
        - field: customer_id
          operator: 'NOT_EMPTY'
          message: 'Customer ID is required'

    validate_email:
      action: statement
      conditions:
        - field: email
          operator: 'REGEX'
          value: '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
          message: 'Invalid email format'

    # Step 2: Clean and standardize names
    clean_first_name:
      action: format
      field: first_name
      template: '%s'
      filters:
        - trim
        - proper_case

    clean_last_name:
      action: format
      field: last_name
      template: '%s'
      filters:
        - trim
        - proper_case

    # Step 3: Create full name field
    create_full_name:
      action: format
      field: full_name
      template: '%s %s'
      fields: [first_name, last_name]

    # Step 4: Standardize phone numbers
    clean_phone:
      action: format
      field: phone
      template: '%s'
      filters:
        - remove_non_numeric
        - phone_format

    # Step 5: Standardize date format
    standardize_date:
      action: format
      field: registration_date
      template: '%s'
      filters:
        - date_normalize
        - date_format: 'Y-m-d'

    # Step 6: Standardize status values
    standardize_status:
      action: value_mapping
      field: status
      mapping:
        'active': 'active'
        'ACTIVE': 'active'
        'Active': 'active'
        'inactive': 'inactive'
        'INACTIVE': 'inactive'
        'Inactive': 'inactive'
        'pending': 'pending'
        'PENDING': 'pending'
        'Pending': 'pending'
      default: 'unknown'

    # Step 7: Add data quality flags
    flag_missing_names:
      action: calculate
      field: has_complete_name
      expression: |
        return (first_name && first_name.trim() !== '') && 
               (last_name && last_name.trim() !== '');

    flag_missing_phone:
      action: calculate
      field: has_phone
      expression: |
        return phone && phone.trim() !== '';

    # Step 8: Remove invalid records (optional)
    # Uncomment to filter out records with invalid emails
    # filter_valid_emails:
    #   action: statement
    #   conditions:
    #     - field: email
    #       operator: 'REGEX'
    #       value: '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'

  output:
    writer:
      type: csv
      filename: 'cleaned-customers.csv'
      options:
        delimiter: ','
        enclosure: '"'
        header: true
```

### Key Concepts Explained

1. **Statement Action**: Validates data against conditions and can filter records
2. **Value Mapping**: Maps inconsistent values to standardized options
3. **Text Filters**: Built-in functions for cleaning and formatting text
4. **Data Quality Flags**: Boolean fields indicating data completeness
5. **Conditional Processing**: Use comments to enable/disable validation steps

## Example 3: Data Enrichment and Calculation

This example demonstrates how to enrich data with calculated fields and external lookups.

### Sample Input Data

Create `examples/basic-examples/sales-data.csv`:

```csv
order_id,product_sku,quantity,unit_price,customer_type,order_date,shipping_country
ORD001,PROD001,2,99.99,premium,2024-01-15,US
ORD002,PROD002,1,79.99,standard,2024-01-15,CA
ORD003,PROD001,3,99.99,premium,2024-01-16,UK
ORD004,PROD003,5,12.99,standard,2024-01-16,US
ORD005,PROD002,2,79.99,premium,2024-01-17,DE
```

Create `examples/basic-examples/tax-rates.csv` (lookup table):

```csv
country,tax_rate,currency
US,0.08,USD
CA,0.13,CAD
UK,0.20,GBP
DE,0.19,EUR
FR,0.20,EUR
```

### Transformation Configuration

Create `examples/basic-examples/enrich-sales.yaml`:

```yaml
# Sales Data Enrichment Example
# This transformation enriches sales data with calculations and lookups

aliases:
  tax_lookup: 'tax-rates.csv'

pipeline:
  input:
    reader:
      type: csv
      filename: 'sales-data.csv'
      options:
        delimiter: ','
        enclosure: '"'
        header: true

  actions:
    # Step 1: Calculate line totals
    calculate_line_total:
      action: calculate
      field: line_total
      expression: |
        return parseFloat(quantity) * parseFloat(unit_price);

    # Step 2: Apply customer discounts
    apply_customer_discount:
      action: calculate
      field: discount_rate
      expression: |
        if (customer_type === 'premium') {
          return 0.10;  // 10% discount for premium customers
        } else if (customer_type === 'standard') {
          return 0.05;  // 5% discount for standard customers
        } else {
          return 0.00;  // No discount for others
        }

    calculate_discount_amount:
      action: calculate
      field: discount_amount
      expression: |
        return parseFloat(line_total) * parseFloat(discount_rate);

    calculate_discounted_total:
      action: calculate
      field: discounted_total
      expression: |
        return parseFloat(line_total) - parseFloat(discount_amount);

    # Step 3: Lookup tax rates by country
    lookup_tax_info:
      action: expand
      source:
        reader:
          type: csv
          filename: 'tax_lookup'
          options:
            delimiter: ','
            header: true
      key: shipping_country
      lookup_key: country
      fields: [tax_rate, currency]

    # Step 4: Calculate taxes
    calculate_tax_amount:
      action: calculate
      field: tax_amount
      expression: |
        return parseFloat(discounted_total) * parseFloat(tax_rate);

    calculate_final_total:
      action: calculate
      field: final_total
      expression: |
        return parseFloat(discounted_total) + parseFloat(tax_amount);

    # Step 5: Format monetary values
    format_line_total:
      action: format
      field: line_total
      template: '%.2f'

    format_discount_amount:
      action: format
      field: discount_amount
      template: '%.2f'

    format_tax_amount:
      action: format
      field: tax_amount
      template: '%.2f'

    format_final_total:
      action: format
      field: final_total
      template: '%.2f'

    # Step 6: Create order summary
    create_order_summary:
      action: format
      field: order_summary
      template: 'Order %s: %s x %s = %s %s (incl. tax)'
      fields: [order_id, quantity, product_sku, final_total, currency]

    # Step 7: Categorize order size
    categorize_order_size:
      action: calculate
      field: order_size_category
      expression: |
        var total = parseFloat(final_total);
        if (total > 200) {
          return 'Large';
        } else if (total > 50) {
          return 'Medium';
        } else {
          return 'Small';
        }

  output:
    writer:
      type: csv
      filename: 'enriched-sales.csv'
      options:
        delimiter: ','
        enclosure: '"'
        header: true
```

### Key Concepts Explained

1. **Calculate Action**: Performs mathematical operations and complex expressions
2. **Expand Action**: Joins data from external lookup tables
3. **Aliases**: Simplifies file references in configuration
4. **Multi-step Calculations**: Building complex calculations step by step
5. **Data Categorization**: Creating business logic for data classification

## Example 4: Multi-format Data Processing

This example shows how to work with different input and output formats.

### Sample Input Data

Create `examples/basic-examples/products.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<products>
    <product>
        <id>PROD001</id>
        <name>Wireless Mouse</name>
        <price>29.99</price>
        <category>Electronics</category>
        <specifications>
            <connectivity>Wireless</connectivity>
            <battery_life>12 months</battery_life>
            <dpi>1600</dpi>
        </specifications>
    </product>
    <product>
        <id>PROD002</id>
        <name>USB Keyboard</name>
        <price>49.99</price>
        <category>Electronics</category>
        <specifications>
            <connectivity>USB</connectivity>
            <layout>QWERTY</layout>
            <backlight>true</backlight>
        </specifications>
    </product>
</products>
```

### Transformation Configuration

Create `examples/basic-examples/xml-to-json.yaml`:

```yaml
# Multi-format Processing Example
# This transformation converts XML to JSON with data enrichment

pipeline:
  input:
    reader:
      type: xml
      filename: 'products.xml'
      xpath: '//product'  # Extract each product element

  actions:
    # Step 1: Flatten nested XML structure
    flatten_specifications:
      action: expand
      source: specifications
      prefix: 'spec_'

    # Step 2: Clean up field names
    standardize_fields:
      action: key_mapping
      mapping:
        id: product_id
        name: product_name
        price: unit_price
        category: product_category

    # Step 3: Convert price to number and add currency
    format_price:
      action: calculate
      field: price_numeric
      expression: |
        return parseFloat(unit_price);

    add_currency:
      action: copy
      from: product_id
      to: currency
      value: 'USD'

    # Step 4: Create product metadata
    create_metadata:
      action: format
      field: metadata
      template: |
        {
          "import_date": "2024-01-15",
          "source": "xml_import",
          "processed": true
        }

    # Step 5: Remove original price field
    cleanup_fields:
      action: remove
      keys: [unit_price]

  output:
    writer:
      type: json
      filename: 'products.json'
      options:
        pretty_print: true
        array_wrapper: 'products'
```

### Key Concepts Explained

1. **XML Processing**: Using XPath to extract records from XML documents
2. **Nested Data Handling**: Flattening complex data structures
3. **Format Conversion**: Converting between XML, CSV, and JSON formats
4. **Data Type Conversion**: Converting strings to numbers and other types
5. **Metadata Addition**: Adding processing information to records

## Running the Examples

### Prerequisites Setup

Create the examples directory structure:

```bash
mkdir -p examples/basic-examples
mkdir -p examples/basic-examples/output
```

### Execute Transformations

Run each example using Docker:

```bash
# Example 1: Product Standardization
bin/docker/console transformation \
  --file examples/basic-examples/standardize-products.yaml \
  --source examples/basic-examples \
  --workpath examples/basic-examples/output

# Example 2: Data Validation
bin/docker/console transformation \
  --file examples/basic-examples/validate-customers.yaml \
  --source examples/basic-examples \
  --workpath examples/basic-examples/output

# Example 3: Data Enrichment
bin/docker/console transformation \
  --file examples/basic-examples/enrich-sales.yaml \
  --source examples/basic-examples \
  --workpath examples/basic-examples/output

# Example 4: Multi-format Processing
bin/docker/console transformation \
  --file examples/basic-examples/xml-to-json.yaml \
  --source examples/basic-examples \
  --workpath examples/basic-examples/output
```

### Debug Mode

Run with debug information to understand processing steps:

```bash
bin/docker/console transformation \
  --file examples/basic-examples/standardize-products.yaml \
  --source examples/basic-examples \
  --workpath examples/basic-examples/output \
  --debug
```

### Test with Limited Records

Process only the first few records for testing:

```bash
bin/docker/console transformation \
  --file examples/basic-examples/standardize-products.yaml \
  --source examples/basic-examples \
  --workpath examples/basic-examples/output \
  --try 3
```

## Best Practices

### Configuration Organization

1. **Use Aliases**: Define file names as aliases for easier maintenance
2. **Comment Your Actions**: Explain complex transformations
3. **Logical Step Names**: Use descriptive names for each action
4. **Group Related Actions**: Organize actions by purpose (validation, formatting, etc.)

### Data Processing

1. **Validate Early**: Check data quality at the beginning of your pipeline
2. **Clean Before Transform**: Standardize data before complex operations
3. **Test Incrementally**: Use `--try` to test with small datasets first
4. **Handle Edge Cases**: Consider empty values, special characters, and data types

### Performance Optimization

1. **Minimize Actions**: Combine operations where possible
2. **Use Appropriate Data Types**: Convert to numbers for calculations
3. **Filter Early**: Remove unnecessary records early in the pipeline
4. **Monitor Memory Usage**: Use `--debug` to identify memory-intensive operations

## Common Patterns

### Field Standardization Pattern
```yaml
standardize_fields:
  action: key_mapping
  mapping:
    old_field1: new_field1
    old_field2: new_field2

clean_values:
  action: format
  field: target_field
  template: '%s'
  filters:
    - trim
    - proper_case
```

### Conditional Processing Pattern
```yaml
conditional_field:
  action: calculate
  field: result_field
  expression: |
    if (condition_field === 'value') {
      return 'result1';
    } else {
      return 'result2';
    }
```

### Data Enrichment Pattern
```yaml
lookup_data:
  action: expand
  source:
    reader:
      type: csv
      filename: 'lookup_table.csv'
  key: join_field
  lookup_key: lookup_field
  fields: [field1, field2]
```

## Troubleshooting

### Common Issues

**Field not found errors:**
- Check field names match your input data exactly
- Use debug mode to see available fields at each step

**Calculation errors:**
- Ensure numeric fields are properly converted using `parseFloat()`
- Handle null/empty values in expressions

**Memory issues:**
- Use `--try` to process smaller batches
- Optimize actions to reduce memory usage

**Output format issues:**
- Verify output writer configuration
- Check file permissions in output directory

## Next Steps

After mastering these basic examples:

1. Explore [Advanced Workflow Examples](advanced-workflows.md)
2. Learn about [API Integration](../user-guide/transformations.md#api-integration)
3. Study [Performance Optimization](../user-guide/debugging.md#performance-optimization-guidelines)
4. Review [Action Reference](../reference/actions/) for all available actions

## Related Topics

- [Quick Start Guide](../getting-started/quick-start.md) - Your first transformation
- [Configuration Guide](../getting-started/configuration.md) - Advanced configuration options
- [Actions Reference](../reference/actions/) - Complete action documentation
- [Troubleshooting Guide](../user-guide/troubleshooting.md) - Common issues and solutions