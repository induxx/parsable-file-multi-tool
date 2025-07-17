
# Format Action

## Overview

The format action allows you to apply a series of formatting functions to one or more fields in an item being processed. It supports various formatting operations including text replacement, number formatting, and prefix/suffix addition, making it essential for data standardization and presentation.

## Syntax

```yaml
actions:
  - action: format
    field: field_name
    functions: [function1, function2]
    search: "search_string"
    replace: "replacement_string"
    mille_sep: "separator"
    prefix: "prefix_string"
    format: "format_string"
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| field | string/array | Yes | - | Field name(s) to be formatted |
| functions | array | Yes | - | List of formatting functions to apply |
| search | string | No | - | String to search for when using replace function |
| replace | string | No | - | String to replace search with when using replace function |
| mille_sep | string | No | - | Thousands separator for number formatting |
| prefix | string | No | - | Prefix to add when using prefix function |
| format | string | No | - | Format string for number formatting |

### Parameter Details

#### field
The field or fields to be formatted. Can be a single field name or an array of field names.

- **Format:** String or array of strings
- **Example:** `"LENGTH"` or `["LENGTH", "HEIGHT", "WIDTH"]`
- **Behavior:** All specified fields will have the same formatting functions applied

#### functions
List of formatting functions to be applied in the specified order.

- **Format:** Array of function names
- **Available functions:** `prefix`, `replace`, `number`, `suffix`, `trim`, `uppercase`, `lowercase`
- **Example:** `["prefix", "replace", "number"]`
- **Behavior:** Functions are applied sequentially

#### search
String to search for when using the replace function.

- **Format:** String
- **Example:** `","`
- **Usage:** Only used with replace function

#### replace
String to replace the search string with when using the replace function.

- **Format:** String
- **Example:** `"."`
- **Usage:** Only used with replace function

#### mille_sep
Thousands separator to use when formatting numbers.

- **Format:** String
- **Example:** `""` (empty for no separator) or `","`
- **Usage:** Only used with number function

#### prefix
Prefix string to add when using the prefix function.

- **Format:** String
- **Example:** `"0"`
- **Usage:** Only used with prefix function

#### format
Format string for number formatting (printf-style).

- **Format:** String
- **Example:** `"%04d"` (4-digit zero-padded integer)
- **Usage:** Only used with number function

## Examples

### Basic Text Replacement

```yaml
actions:
  - action: format
    field: product_code
    functions: [replace]
    search: "-"
    replace: "_"
```

**Input:**
```json
{
  "product_code": "ABC-123-XYZ"
}
```

**Output:**
```json
{
  "product_code": "ABC_123_XYZ"
}
```

### Multiple Field Number Formatting

```yaml
actions:
  - action: format
    field: [LENGTH, HEIGHT, WIDTH, WEIGHT]
    functions: [prefix, replace, number]
    search: ","
    replace: "."
    mille_sep: ""
    prefix: "0"
    format: "%04d"
```

**Input:**
```json
{
  "LENGTH": "12,5",
  "HEIGHT": "8,2",
  "WIDTH": "15,7",
  "WEIGHT": "2,1"
}
```

**Output:**
```json
{
  "LENGTH": "0012",
  "HEIGHT": "0008",
  "WIDTH": "0015",
  "WEIGHT": "0002"
}
```

### Text Case Conversion

```yaml
actions:
  - action: format
    field: product_name
    functions: [trim, uppercase]
```

**Input:**
```json
{
  "product_name": "  widget pro  "
}
```

**Output:**
```json
{
  "product_name": "WIDGET PRO"
}
```

## Use Cases

### Use Case 1: Data Standardization
Standardize field formats across different data sources by applying consistent formatting rules.

### Use Case 2: Number Formatting
Format numeric values for display or export with specific precision, separators, and padding.

### Use Case 3: Text Cleaning
Clean and normalize text data by removing unwanted characters, adjusting case, and trimming whitespace.

## Common Issues and Solutions

### Issue: Functions Applied in Wrong Order

**Symptoms:** Formatting results are not as expected due to function sequence.

**Cause:** Functions are applied in the order specified in the functions array.

**Solution:** Arrange functions in the correct logical order.

```yaml
# Correct order: clean first, then format
actions:
  - action: format
    field: price
    functions: [trim, replace, number]
    search: ","
    replace: "."
    format: "%.2f"
```

### Issue: Missing Required Parameters

**Symptoms:** Format action fails or produces unexpected results.

**Cause:** Required parameters for specific functions are missing.

**Solution:** Ensure all required parameters for used functions are provided.

```yaml
# Include all required parameters for replace function
actions:
  - action: format
    field: code
    functions: [replace]
    search: "-"
    replace: "_"
```

### Issue: Invalid Format String

**Symptoms:** Number formatting fails or produces incorrect output.

**Cause:** Invalid printf-style format string.

**Solution:** Use valid printf format specifiers.

```yaml
# Valid format strings
actions:
  - action: format
    field: price
    functions: [number]
    format: "%.2f"  # 2 decimal places
```

## Performance Considerations

- String operations are generally fast but can add up with large datasets
- Multiple function applications increase processing time
- Consider batching similar formatting operations
- Regular expressions in replace operations may be slower than simple string replacement

## Related Actions

- [Copy Action](./copy_action.md) - Copy fields before formatting to preserve originals
- [Calculate Action](./calculate_action.md) - Perform calculations before number formatting
- [Statement Action](./statement_action.md) - Apply conditional formatting logic

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [Data Type Handling](../user-guide/data-types.md)
- [String Functions Reference](../functions/modifiers.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: formatting*