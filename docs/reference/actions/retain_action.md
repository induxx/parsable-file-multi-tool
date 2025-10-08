
# Retain Action

## Overview

The retain action allows you to keep only the specified keys or fields while discarding all others from an item being processed. It's the opposite of the remove action and is essential for data filtering workflows where you need to extract only specific fields from complex data structures.

## Syntax

```yaml
actions:
  - action: retain
    keys: [field1, field2, field3]
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| keys | array | Yes | - | Array of field names to retain in the item |

### Parameter Details

#### keys
An array containing the names of fields to be retained in the processed item. All other fields will be removed.

- **Format:** Array of strings
- **Example:** `["NAME", "AGE", "GENDER"]`
- **Behavior:** Only the specified fields will remain in the output

## Examples

### Basic Field Retention

```yaml
actions:
  - action: retain
    keys: [NAME, AGE, GENDER]
```

**Input:**
```json
{
  "NAME": "John Doe",
  "AGE": 30,
  "GENDER": "Male",
  "SSN": "123-45-6789",
  "INTERNAL_ID": "EMP001",
  "SALARY": 75000,
  "DEPARTMENT": "Engineering"
}
```

**Output:**
```json
{
  "NAME": "John Doe",
  "AGE": 30,
  "GENDER": "Male"
}
```

### Customer Data Extraction

```yaml
actions:
  - action: retain
    keys: [customer_name, email, phone, address]
```

**Input:**
```json
{
  "customer_name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "555-0123",
  "address": "123 Main St",
  "credit_score": 750,
  "internal_notes": "VIP customer",
  "account_created": "2023-01-15",
  "last_login": "2024-01-16"
}
```

**Output:**
```json
{
  "customer_name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "555-0123",
  "address": "123 Main St"
}
```

### Product Information Filtering

```yaml
actions:
  - action: retain
    keys: [product_id, name, price, category, description]
```

**Input:**
```json
{
  "product_id": "PROD001",
  "name": "Widget Pro",
  "price": 29.99,
  "category": "Electronics",
  "description": "High-quality widget",
  "internal_cost": 15.50,
  "supplier_id": "SUP123",
  "warehouse_location": "A-15-B",
  "last_updated": "2024-01-16T10:30:00Z"
}
```

**Output:**
```json
{
  "product_id": "PROD001",
  "name": "Widget Pro",
  "price": 29.99,
  "category": "Electronics",
  "description": "High-quality widget"
}
```

## Use Cases

### Use Case 1: Data Export Preparation
Extract only the necessary fields for external system integration or API responses.

### Use Case 2: Privacy Compliance
Remove sensitive or internal fields while keeping only the data needed for specific operations.

### Use Case 3: Performance Optimization
Reduce data size by keeping only relevant fields for downstream processing.

## Common Issues and Solutions

### Issue: Retaining Non-Existent Fields

**Symptoms:** Output contains fewer fields than expected.

**Cause:** Some field names in the keys array don't exist in the input data.

**Solution:** Verify that all field names in the keys array match actual field names in the data.

```yaml
# Use debug action to inspect available fields first
actions:
  - action: debug
  - action: retain
    keys: [existing_field_1, existing_field_2]
```

### Issue: Case Sensitivity Problems

**Symptoms:** Expected fields are not retained despite being listed in keys array.

**Cause:** Field names have different capitalization than specified in keys array.

**Solution:** Ensure exact case matching between keys array and actual field names.

```yaml
# Match exact case of field names
actions:
  - action: retain
    keys: [FieldName, FIELD_NAME, field_name]  # Use exact case
```

### Issue: Accidentally Removing Required Fields

**Symptoms:** Downstream processes fail due to missing required fields.

**Cause:** Important fields were not included in the keys array.

**Solution:** Review business requirements and ensure all necessary fields are included.

```yaml
# Include all required fields for downstream processing
actions:
  - action: retain
    keys: [id, name, status, created_date, required_field]
```

## Performance Considerations

- Retain operations are very fast as they work in memory
- Retaining fewer fields can improve performance of downstream operations
- No significant performance difference based on number of fields retained
- Consider memory usage when processing large datasets

## Related Topics

### Core Field Operations
- **[Remove Action](./remove_action.md)** - Remove specific fields (opposite of retain) and eliminate unwanted data
- **[Copy Action](./copy_action.md)** - Create backups before retaining specific fields and preserve original data
- **[Statement Action](./statement_action.md)** - Add conditional logic around field retention and validate field selection
- **[Debug Action](./debug_action.md)** - Debug retention operations and verify field filtering

### Data Processing Actions
- **[Rename Action](./rename_action.md)** - Rename retained fields and standardize field names
- **[Format Action](./format_action.md)** - Format retained fields and clean up data values
- **[Calculate Action](./calculate_action.md)** - Use retained fields in calculations and create derived values
- **[Concat Action](./concat_action.md)** - Combine retained fields and create composite values

### Value Transformation Actions
- **[Value Mapping Action](./value_mapping_in_list_action.md)** - Map values in retained fields and standardize content
- **[Key Mapping Action](./key_mapping_action.md)** - Use retained fields in mapping operations
- **[Field Field Action](./field_field_action.md)** - Perform field operations on retained data

### Configuration and Context
- **[Context Directive](../directives/context.md)** - Define retention patterns and field lists using context variables
- **[Mapping Directive](../directives/mapping.md)** - Use mappings to determine which fields to retain
- **[Pipeline Configuration](../directives/pipelines.md)** - Integrate field retention in data processing workflows
- **[Aliases Directive](../directives/aliases.md)** - Define reusable field lists for consistent retention

### Data Management and Filtering
- **[Transformation Steps](../directives/transformation_steps.md)** - Multi-step workflows with field retention and data filtering
- **[Field Management](../../../user-guide/transformations.md#field-management)** - Best practices for field selection and organization
- **[Data Privacy Guidelines](../../../user-guide/transformations.md#data-privacy)** - Retain only necessary fields and protect sensitive data
- **[Performance Optimization](../../../user-guide/debugging.md#performance-optimization-guidelines)** - Retain essential fields to improve performance

### Debugging and Validation
- **[Debugging Guide](../../../user-guide/debugging.md)** - Debug retention operations and troubleshoot field issues
- **[CLI Commands](../cli-commands.md)** - Test retention operations with limited data and debug mode
- **[Error Handling](../../../user-guide/debugging.md#common-error-scenarios-and-solutions)** - Handle retention errors and field conflicts
- **[Data Validation](../../../user-guide/transformations.md#data-validation)** - Validate data integrity after field retention

### Export and Integration
- **[API Integration](../../../user-guide/transformations.md#api-integration)** - Retain fields for external system compatibility
- **[Data Export](../../../user-guide/transformations.md#data-export)** - Prepare data for export by retaining relevant fields
- **[Format Conversion](../converters/)** - Use retention with data format converters

## See Also

- **[Actions Reference](./index.md)** - Complete list of all available actions and field operations
- **[Transformation Examples](../../../examples/)** - Practical field retention examples and data filtering patterns
- **[Quick Start Guide](../../../getting-started/quick-start.md)** - Basic field manipulation techniques for beginners
- **[Data Minimization Guide](../../../examples/data-minimization.md)** - Field retention strategies and best practices

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*