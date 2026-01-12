# Basic Transformation Examples

This directory contains complete working examples for common data transformation scenarios. Each example includes sample data files and transformation configurations that you can run immediately.

## Available Examples

### 1. Product Data Standardization
- **Files**: `supplier-products.csv`, `standardize-products.yaml`
- **Purpose**: Standardize product data from different suppliers
- **Key Concepts**: Field mapping, data formatting, conditional logic

### 2. Data Validation and Cleansing
- **Files**: `customer-data.csv`, `validate-customers.yaml`
- **Purpose**: Validate and clean inconsistent customer data
- **Key Concepts**: Data validation, text cleaning, quality flags

### 3. Data Enrichment and Calculation
- **Files**: `sales-data.csv`, `tax-rates.csv`, `enrich-sales.yaml`
- **Purpose**: Enrich sales data with calculations and lookups
- **Key Concepts**: Mathematical calculations, data joins, business logic

### 4. Multi-format Processing
- **Files**: `products.xml`, `xml-to-json.yaml`
- **Purpose**: Convert XML to JSON with data transformation
- **Key Concepts**: Format conversion, nested data handling, metadata addition

## Running the Examples

### Prerequisites
1. Ensure the parsable-file-multi-tool is installed and configured
2. Create an output directory: `mkdir -p output`

### Execute Transformations

```bash
# Example 1: Product Standardization
bin/docker/console transformation \
  --file standardize-products.yaml \
  --source . \
  --workpath output

# Example 2: Data Validation
bin/docker/console transformation \
  --file validate-customers.yaml \
  --source . \
  --workpath output

# Example 3: Data Enrichment
bin/docker/console transformation \
  --file enrich-sales.yaml \
  --source . \
  --workpath output

# Example 4: Multi-format Processing
bin/docker/console transformation \
  --file xml-to-json.yaml \
  --source . \
  --workpath output
```

### Debug Mode
Add `--debug` flag to see detailed processing information:

```bash
bin/docker/console transformation \
  --file standardize-products.yaml \
  --source . \
  --workpath output \
  --debug
```

### Test with Limited Records
Use `--try N` to process only the first N records:

```bash
bin/docker/console transformation \
  --file standardize-products.yaml \
  --source . \
  --workpath output \
  --try 3
```

## Expected Output Files

After running the examples, you should see these output files in the `output/` directory:

- `standardized-products.csv` - Standardized product data
- `cleaned-customers.csv` - Validated and cleaned customer data
- `enriched-sales.csv` - Sales data with calculations and tax information
- `products.json` - XML data converted to JSON format

## Next Steps

1. Review the [complete documentation](../../docs/examples/basic-transformation.md) for detailed explanations
2. Modify the sample data to test different scenarios
3. Experiment with different transformation configurations
4. Explore advanced examples in the main documentation

## Troubleshooting

If you encounter issues:

1. Check that all input files exist in the current directory
2. Verify the output directory has write permissions
3. Use `--debug` mode to see detailed error messages
4. Refer to the [troubleshooting guide](../../docs/user-guide/debugging.md)