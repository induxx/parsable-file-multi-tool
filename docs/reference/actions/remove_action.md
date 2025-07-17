
# Remove Action

## Overview

The remove action allows you to remove unwanted keys or fields from an item being processed. It's essential for data cleaning workflows where you need to eliminate unnecessary fields, reduce data size, or prepare data for specific output formats.

## Syntax

```yaml
actions:
  - action: remove
    keys: [field1, field2, field3]
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| keys | array | Yes | - | Array of field names to remove from the item |

### Parameter Details

#### keys
An array containing the names of fields to be removed from the processed item.

- **Format:** Array of strings
- **Example:** `["GROUP", "TYPE", "UID", "ATTRIBUTE_CODE"]`
- **Behavior:** All specified fields will be permanently removed from the item

## Examples

### Basic Field Removal

```yaml
actions:
  - action: remove
    keys: [GROUP, TYPE, UID, ATTRIBUTE_CODE, DUMP_TIMESTAMP, MEASUREMENT_FAMILY]
```

**Input:**
```json
{
  "product_name": "Widget Pro",
  "price": 29.99,
  "GROUP": "electronics",
  "TYPE": "gadget",
  "UID": "12345",
  "ATTRIBUTE_CODE": "WP001",
  "DUMP_TIMESTAMP": "2024-01-16T10:30:00Z",
  "MEASUREMENT_FAMILY": "metric"
}
```

**Output:**
```json
{
  "product_name": "Widget Pro",
  "price": 29.99
}
```

### Removing Temporary Fields

```yaml
actions:
  - action: remove
    keys: [temp_field, processing_flag, debug_info]
```

**Input:**
```json
{
  "customer_name": "John Doe",
  "order_total": 150.00,
  "temp_field": "temporary_data",
  "processing_flag": true,
  "debug_info": "processed_at_2024-01-16"
}
```

**Output:**
```json
{
  "customer_name": "John Doe",
  "order_total": 150.00
}
```

### Data Privacy Cleanup

```yaml
actions:
  - action: remove
    keys: [ssn, credit_card, internal_notes, employee_id]
```

**Input:**
```json
{
  "customer_name": "Jane Smith",
  "order_id": "ORD-001",
  "ssn": "123-45-6789",
  "credit_card": "4111-1111-1111-1111",
  "internal_notes": "VIP customer",
  "employee_id": "EMP-456"
}
```

**Output:**
```json
{
  "customer_name": "Jane Smith",
  "order_id": "ORD-001"
}
```

## Use Cases

### Use Case 1: Data Export Preparation
Remove internal or sensitive fields before exporting data to external systems or third parties.

### Use Case 2: Performance Optimization
Eliminate unnecessary fields to reduce data size and improve processing performance in downstream operations.

### Use Case 3: Data Privacy Compliance
Remove personally identifiable information (PII) or sensitive data fields to comply with privacy regulations.

## Common Issues and Solutions

### Issue: Removing Non-Existent Fields

**Symptoms:** No error occurs, but expected fields are not removed.

**Cause:** Field names in the keys array don't match actual field names in the data.

**Solution:** Verify field names match exactly, including case sensitivity.

```yaml
# Ensure exact field name matching
actions:
  - action: remove
    keys: [EXACT_FIELD_NAME, another_field]
```

### Issue: Accidentally Removing Required Fields

**Symptoms:** Downstream processes fail due to missing required fields.

**Cause:** Important fields were included in the removal list.

**Solution:** Review the keys array carefully and use conditional removal when needed.

```yaml
# Use conditional logic to preserve important fields
actions:
  - action: statement
    condition: "{{ environment != 'production' }}"
    then:
      - action: remove
        keys: [debug_field, test_data]
```

### Issue: Case Sensitivity Problems

**Symptoms:** Fields are not removed despite being listed in the keys array.

**Cause:** Field names have different capitalization than expected.

**Solution:** Check the exact case of field names in your data.

```yaml
# Match exact case
actions:
  - action: remove
    keys: [fieldName, FIELD_NAME, field_name]  # Different cases
```

## Performance Considerations

- Remove operations are very fast as they work in memory
- Removing fields early in the pipeline can improve overall performance
- Consider the impact on downstream processes that might expect removed fields
- Large numbers of fields to remove have minimal performance impact

## Related Topics

### Core Field Operations
- **[Retain Action](./retain_action.md)** - Keep only specified fields (opposite of remove) and filter data selectively
- **[Copy Action](./copy_action.md)** - Create backups before removing important fields and preserve original data
- **[Statement Action](./statement_action.md)** - Add conditional logic around field removal and validate before deletion
- **[Debug Action](./debug_action.md)** - Debug removal operations and verify field deletion

### Data Processing Actions
- **[Rename Action](./rename_action.md)** - Rename fields before removal and reorganize data structure
- **[Format Action](./format_action.md)** - Format remaining fields after removal and clean up data
- **[Calculate Action](./calculate_action.md)** - Use field values before removal in calculations
- **[Concat Action](./concat_action.md)** - Combine field values before removal and preserve information

### Value Transformation Actions
- **[Value Mapping Action](./value_mapping_in_list_action.md)** - Map values before field removal and preserve transformed data
- **[Key Mapping Action](./key_mapping_action.md)** - Use field values in mappings before removal
- **[Field Field Action](./field_field_action.md)** - Perform field operations before removal

### Configuration and Context
- **[Context Directive](../directives/context.md)** - Define removal patterns and field lists using context variables
- **[Mapping Directive](../directives/mapping.md)** - Use mappings to determine which fields to remove
- **[Pipeline Configuration](../directives/pipelines.md)** - Integrate field removal in data processing workflows
- **[Aliases Directive](../directives/aliases.md)** - Define reusable field lists for consistent removal

### Data Management and Privacy
- **[Transformation Steps](../directives/transformation_steps.md)** - Multi-step workflows with field removal and data cleanup
- **[Field Management](../../../user-guide/transformations.md#field-management)** - Best practices for field organization and cleanup
- **[Data Privacy Guidelines](../../../user-guide/transformations.md#data-privacy)** - Remove sensitive fields and comply with privacy regulations
- **[Performance Optimization](../../../user-guide/debugging.md#performance-optimization-guidelines)** - Remove unnecessary fields to improve performance

### Debugging and Validation
- **[Debugging Guide](../../../user-guide/debugging.md)** - Debug removal operations and troubleshoot field issues
- **[CLI Commands](../cli-commands.md)** - Test removal operations with limited data and debug mode
- **[Error Handling](../../../user-guide/debugging.md#common-error-scenarios-and-solutions)** - Handle removal errors and field conflicts
- **[Data Validation](../../../user-guide/transformations.md#data-validation)** - Validate data integrity after field removal

## See Also

- **[Actions Reference](./index.md)** - Complete list of all available actions and field operations
- **[Transformation Examples](../../../examples/)** - Practical field removal examples and data cleanup patterns
- **[Quick Start Guide](../../../getting-started/quick-start.md)** - Basic field manipulation techniques for beginners
- **[Data Security Best Practices](../../../user-guide/security.md)** - Secure field removal and data protection guidelines

---
