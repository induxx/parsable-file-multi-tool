# XML Data Converter

## Overview

The XML Data Converter transforms XML data into structured, normalized formats for processing and integration. This converter handles complex XML structures and converts them into flat, accessible data formats suitable for downstream actions and transformations.

## Syntax

```yaml
converter:
  name: 'xml/data'
  options:
    root_element: element_name
    namespace_handling: preserve|strip
    # Additional converter-specific options
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| name | string | Yes | - | Converter identifier ('xml/data') |
| options | object | No | {} | Configuration options for XML processing |
| root_element | string | No | - | Specific root element to process |
| namespace_handling | string | No | preserve | How to handle XML namespaces |

## Examples

### Basic XML to Structured Data Conversion

```yaml
# Convert XML file to structured format
pipeline:
  input:
    reader:
      type: xml
      filename: 'product_catalog.xml'
      converter: 'xml/data'
  actions:
    flatten_structure:
      action: retain
      keys: [id, name, category, price]
  output:
    writer:
      type: csv
      filename: 'products.csv'
```

### XML with Namespace Handling

```yaml
# Process XML with namespaces
converter:
  name: 'xml/data'
  options:
    namespace_handling: strip
    root_element: products

pipeline:
  input:
    reader:
      type: xml
      filename: 'namespaced_catalog.xml'
      converter: 'xml/data'
  output:
    writer:
      type: json
      filename: 'converted_products.json'
```

### Complex XML Structure Processing

```yaml
# Handle nested XML structures
pipeline:
  input:
    reader:
      type: xml
      filename: 'complex_catalog.xml'
      converter: 'xml/data'
  actions:
    extract_attributes:
      action: expand
      field: attributes
    normalize_categories:
      action: format
      field: category
      functions: [lowercase, trim]
  output:
    writer:
      type: csv
      filename: 'normalized_products.csv'
```

## Data Structure Transformation

### Input Structure (XML)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<catalog xmlns:product="http://example.com/product">
  <product:item id="001">
    <product:name>Sample Product</product:name>
    <product:category>Electronics</product:category>
    <product:price currency="USD">99.99</product:price>
    <product:attributes>
      <product:color>Red</product:color>
      <product:size>Large</product:size>
    </product:attributes>
  </product:item>
</catalog>
```

### Output Structure (Flattened)
```csv
id,name,category,price,currency,color,size
001,Sample Product,Electronics,99.99,USD,Red,Large
```

## Use Cases

### Use Case 1: Legacy System Integration
Import data from XML-based legacy systems and convert to modern structured formats.

### Use Case 2: API Response Processing
Process XML API responses and transform them into workable data structures.

### Use Case 3: Data Migration
Convert XML data files to CSV or JSON formats for database import or system migration.

## Configuration Options

### Namespace Handling
- **preserve**: Keep namespace prefixes in field names
- **strip**: Remove namespace prefixes for cleaner field names

### Root Element Processing
- Specify which XML element to use as the data root
- Useful for processing specific sections of large XML documents

## Common Issues and Solutions

### Issue: Namespace Conflicts

**Symptoms:** Field names with complex namespace prefixes or missing data.

**Cause:** XML namespaces not handled properly during conversion.

**Solution:** Configure namespace handling appropriately.

```yaml
converter:
  name: 'xml/data'
  options:
    namespace_handling: strip  # Remove namespace prefixes
```

### Issue: Deeply Nested Structures

**Symptoms:** Complex nested objects instead of flat data structure.

**Cause:** XML has deep hierarchical structure that needs flattening.

**Solution:** Use expand actions after conversion to flatten nested data.

```yaml
pipeline:
  input:
    reader:
      type: xml
      filename: 'nested_data.xml'
      converter: 'xml/data'
  actions:
    flatten_attributes:
      action: expand
      field: attributes
    flatten_metadata:
      action: expand
      field: metadata
```

### Issue: Large XML File Performance

**Symptoms:** Slow processing or memory issues with large XML files.

**Cause:** Loading entire XML document into memory.

**Solution:** Consider streaming XML processing or file splitting.

## Best Practices

- Test XML conversion with sample data before processing large files
- Use appropriate namespace handling based on your data requirements
- Validate XML structure and encoding before conversion
- Consider memory implications for large XML documents
- Use expand actions to flatten complex nested structures
- Document XML schema requirements and expected structure

## Integration Guidelines

### With Pipeline Actions
- Apply XML conversion at input stage before other transformations
- Use expand and format actions to process converted XML data
- Validate data structure after XML conversion

### With External Systems
- Ensure XML files are well-formed and valid
- Handle encoding issues (UTF-8, UTF-16, etc.)
- Implement error handling for malformed XML

## Related Components

- [Converters Directive](../directives/converters.md) - Configuration and usage patterns
- [Pipeline Directive](../directives/pipelines.md) - Integration with data processing workflows
- [Expand Action](../actions/expand_action.md) - Flattening nested XML structures

## See Also

- [Data Transformation Guide](../user-guide/transformations.md)
- [File Format Support](../data_source/reader.md)
- [XML Processing Best Practices](../user-guide/transformations.md)

---

*Last updated: 2024-12-19*
*Category: reference*
*Component Type: converter*
*Source File: src/Component/Converter/XmlDataConverter.php*

