# Debug Action

## Overview

The debug action is a utility designed to facilitate debugging during data transformation processes. It provides mechanisms to inspect and output specific parts of the data being processed, enabling developers to identify issues, verify transformation correctness, and understand data flow at various pipeline stages.

## Syntax

```yaml
actions:
  - action: debug
    field: field_name
    until_field: field_name
    marker: "filename:line"
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| field | string | No | - | Specific field to debug and output |
| until_field | string | No | - | Debug until this field's value is encountered |
| marker | string | No | - | File and line number for marker-based debugging |

### Parameter Details

#### field
Specifies a particular field in the data to debug. When set, only the value of this field will be output to the console or log.

- **Format:** String field name
- **Example:** `"product_name"`
- **Behavior:** Outputs only the specified field's value

#### until_field
Specifies a field to debug until its value is encountered. Useful for inspecting data processing up to a certain point in the transformation pipeline.

- **Format:** String field name
- **Example:** `"processed_flag"`
- **Behavior:** Continues debugging until the specified field appears in the data

#### marker
Allows debugging based on a specific file and line number. The format is `filename:line`. When provided, the content of the specified line in the file will be output.

- **Format:** `"filename:line_number"`
- **Example:** `"transformation.yaml:42"`
- **Behavior:** Outputs the content of the specified line from the given file

## Examples

### Basic Field Debugging

```yaml
actions:
  - action: debug
    field: product_name
```

**Input:**
```json
{
  "product_id": "12345",
  "product_name": "Widget Pro",
  "price": 29.99
}
```

**Debug Output:**
```
DEBUG [field: product_name]: Widget Pro
```

### Complete Data Debugging

```yaml
actions:
  - action: debug
```

**Input:**
```json
{
  "product_id": "12345",
  "product_name": "Widget Pro",
  "price": 29.99
}
```

**Debug Output:**
```
DEBUG [complete data]: {
  "product_id": "12345",
  "product_name": "Widget Pro",
  "price": 29.99
}
```

### Marker-Based Debugging

```yaml
actions:
  - action: debug
    marker: "config/transformation.yaml:15"
```

**Debug Output:**
```
DEBUG [marker config/transformation.yaml:15]: action: calculate
```

### Until Field Debugging

```yaml
actions:
  - action: debug
    until_field: validation_complete
```

**Behavior:** Continues outputting debug information until a field named `validation_complete` appears in the processed data.

## Use Cases

### Use Case 1: Development and Testing
Monitor data transformation at specific pipeline stages to verify that transformations are working as expected.

### Use Case 2: Production Troubleshooting
Temporarily add debug actions to identify issues in production data processing without stopping the entire pipeline.

### Use Case 3: Data Flow Analysis
Understand how data changes through complex transformation pipelines by strategically placing debug actions.

## Common Issues and Solutions

### Issue: Too Much Debug Output

**Symptoms:** Console or logs are flooded with debug information, making it hard to find relevant data.

**Cause:** Using debug action without field specification on large datasets.

**Solution:** Use the `field` parameter to focus on specific data points.

```yaml
# Instead of debugging everything
actions:
  - action: debug

# Focus on specific fields
actions:
  - action: debug
    field: critical_field
```

### Issue: Debug Action Affecting Performance

**Symptoms:** Transformation pipeline runs significantly slower when debug actions are enabled.

**Cause:** Debug actions output data to console/logs, which can be I/O intensive.

**Solution:** Remove or disable debug actions in production environments.

```yaml
# Use conditional debugging
actions:
  - action: statement
    condition: "{{ env.DEBUG_MODE == 'true' }}"
    then:
      - action: debug
        field: product_name
```

### Issue: Marker File Not Found

**Symptoms:** Debug action fails when using marker parameter with non-existent file.

**Cause:** Specified file path in marker parameter doesn't exist or is incorrect.

**Solution:** Verify file paths and ensure files are accessible.

```yaml
# Ensure correct relative path
actions:
  - action: debug
    marker: "./config/transformation.yaml:15"
```

## Performance Considerations

- Debug actions add I/O overhead due to console/log output
- Large data structures can significantly slow down processing
- Consider using field-specific debugging for better performance
- Remove debug actions from production configurations
- Use conditional debugging based on environment variables

## Troubleshooting

### Debug Action Not Producing Output

1. **Check Log Level:** Ensure your logging configuration captures debug-level messages
2. **Verify Field Names:** Confirm that field names in debug parameters match actual data fields
3. **Check Conditional Logic:** If using statements, verify conditions are met
4. **File Permissions:** For marker debugging, ensure files are readable

### Understanding Debug Output Format

Debug output typically follows this format:
```
DEBUG [type: parameter]: value
```

Where:
- `type` indicates the debug type (field, marker, complete data)
- `parameter` shows the specific parameter used
- `value` is the actual data being debugged

## Related Actions

- [Statement Action](./statement_action.md) - Add conditional logic around debug actions
- [Copy Action](./copy_action.md) - Create debug-specific fields for monitoring
- [Format Action](./format_action.md) - Format data before debugging for better readability

## See Also

- [Debugging Guide](../user-guide/debugging.md) - Comprehensive debugging strategies
- [Troubleshooting](../user-guide/troubleshooting.md) - Common issues and solutions
- [Logging Configuration](../getting-started/configuration.md#logging) - Set up proper logging levels
- [Development Best Practices](../developer-guide/best-practices.md) - Debug action usage guidelines

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: utility*
