
# Date Time Action

## Overview

The date_time action facilitates the transformation of date and time formats from one structure to another. It's essential for data standardization workflows where you need to convert dates between different formats for compatibility with various systems or display requirements.

## Syntax

```yaml
actions:
  - action: date_time
    field: field_name
    inputFormat: 'input_format_string'
    outputFormat: 'output_format_string'
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| field | string | Yes | - | Target field containing the date/time value to format |
| inputFormat | string | Yes | - | Current format of the date and time in the field |
| outputFormat | string | Yes | - | Desired output format for the date and time |

### Parameter Details

#### field
The name of the field containing the date/time value to be formatted.

- **Format:** String field name
- **Example:** `"event_date"`, `"created_at"`, `"timestamp"`
- **Behavior:** The value in this field will be parsed and reformatted

#### inputFormat
Defines the current format of the date and time value in the specified field.

- **Format:** PHP date format string
- **Example:** `"Y-m-d H:i:s"`, `"m/d/Y"`, `"ISO8601"`
- **Behavior:** Used to parse the existing date/time value

#### outputFormat
Dictates the desired format for the transformed date and time value.

- **Format:** PHP date format string
- **Example:** `"F j, Y, g:i a"`, `"Y-m-d"`, `"RFC3339"`
- **Behavior:** The final format of the date/time value

## Examples

### Basic Date Format Conversion

```yaml
actions:
  - action: date_time
    field: event_date
    inputFormat: 'Y-m-d H:i:s'
    outputFormat: 'F j, Y, g:i a'
```

**Input:**
```json
{
  "event_date": "2023-10-18 15:30:00",
  "code": "123456"
}
```

**Output:**
```json
{
  "event_date": "October 18, 2023, 3:30 pm",
  "code": "123456"
}
```

### ISO 8601 to Human Readable

```yaml
actions:
  - action: date_time
    field: created_at
    inputFormat: 'Y-m-d\TH:i:sP'
    outputFormat: 'l, F j, Y \a\t g:i A'
```

**Input:**
```json
{
  "user_id": "USER001",
  "created_at": "2024-01-16T14:30:00+00:00"
}
```

**Output:**
```json
{
  "user_id": "USER001",
  "created_at": "Tuesday, January 16, 2024 at 2:30 PM"
}
```

### US Format to European Format

```yaml
actions:
  - action: date_time
    field: birth_date
    inputFormat: 'm/d/Y'
    outputFormat: 'd/m/Y'
```

**Input:**
```json
{
  "name": "John Doe",
  "birth_date": "03/15/1990"
}
```

**Output:**
```json
{
  "name": "John Doe",
  "birth_date": "15/03/1990"
}
```

### Using Predefined Format Constants

```yaml
actions:
  - action: date_time
    field: timestamp
    inputFormat: 'ATOM'
    outputFormat: 'RFC2822'
```

**Input:**
```json
{
  "id": "MSG001",
  "timestamp": "2024-01-16T14:30:00+00:00"
}
```

**Output:**
```json
{
  "id": "MSG001",
  "timestamp": "Tue, 16 Jan 2024 14:30:00 +0000"
}
```

## Predefined Format Constants

The following predefined format constants can be used for `inputFormat` and `outputFormat` parameters:

| Constant | Format String | Example Output |
|----------|---------------|----------------|
| ATOM | Y-m-d\TH:i:sP | 2024-01-16T14:30:00+00:00 |
| COOKIE | l, d-M-Y H:i:s T | Tuesday, 16-Jan-2024 14:30:00 UTC |
| ISO8601 | Y-m-d\TH:i:sO | 2024-01-16T14:30:00+0000 |
| ISO8601_EXPANDED | X-m-d\TH:i:sP | 2024-01-16T14:30:00+00:00 |
| RFC822 | D, d M y H:i:s O | Tue, 16 Jan 24 14:30:00 +0000 |
| RFC850 | l, d-M-y H:i:s T | Tuesday, 16-Jan-24 14:30:00 UTC |
| RFC1036 | D, d M y H:i:s O | Tue, 16 Jan 24 14:30:00 +0000 |
| RFC1123 | D, d M Y H:i:s O | Tue, 16 Jan 2024 14:30:00 +0000 |
| RFC7231 | D, d M Y H:i:s \G\M\T | Tue, 16 Jan 2024 14:30:00 GMT |
| RFC2822 | D, d M Y H:i:s O | Tue, 16 Jan 2024 14:30:00 +0000 |
| RFC3339 | Y-m-d\TH:i:sP | 2024-01-16T14:30:00+00:00 |
| RFC3339_EXTENDED | Y-m-d\TH:i:s.vP | 2024-01-16T14:30:00.000+00:00 |
| RSS | D, d M Y H:i:s O | Tue, 16 Jan 2024 14:30:00 +0000 |
| W3C | Y-m-d\TH:i:sP | 2024-01-16T14:30:00+00:00 |

## Use Cases

### Use Case 1: API Integration
Convert date formats between different API systems that expect different date/time formats.

### Use Case 2: User Interface Display
Transform database timestamps into user-friendly date formats for display purposes.

### Use Case 3: Data Export Preparation
Standardize date formats for export to systems with specific date/time requirements.

## Common Issues and Solutions

### Issue: Invalid Input Format

**Symptoms:** Date conversion fails or produces unexpected results.

**Cause:** The inputFormat doesn't match the actual format of the date in the field.

**Solution:** Verify the exact format of your input date and adjust the inputFormat accordingly.

```yaml
# Debug the field value first to see actual format
actions:
  - action: debug
    field: date_field
  - action: date_time
    field: date_field
    inputFormat: 'correct_input_format'
    outputFormat: 'desired_output_format'
```

### Issue: Timezone Issues

**Symptoms:** Converted dates show incorrect times or unexpected timezone offsets.

**Cause:** Timezone information is lost or incorrectly handled during conversion.

**Solution:** Include timezone information in both input and output formats when needed.

```yaml
# Handle timezones explicitly
actions:
  - action: date_time
    field: timestamp
    inputFormat: 'Y-m-d H:i:s T'  # Include timezone in input
    outputFormat: 'Y-m-d\TH:i:sP'  # Include timezone in output
```

### Issue: Invalid Date Values

**Symptoms:** Action fails when processing certain date values.

**Cause:** Some field values contain invalid or malformed date strings.

**Solution:** Use conditional logic to handle invalid dates gracefully.

```yaml
# Add validation before date conversion
actions:
  - action: statement
    when:
      field: date_field
      operator: NOT_EMPTY
    then:
      - action: date_time
        field: date_field
        inputFormat: 'Y-m-d'
        outputFormat: 'F j, Y'
```

## Performance Considerations

- Date parsing and formatting operations are generally fast
- Complex format strings may have slight performance impact
- Consider caching converted dates for frequently accessed data
- Timezone conversions add minimal overhead

## Related Actions

- [Format Action](./format_action.md) - Format other data types
- [Statement Action](./statement_action.md) - Add conditional logic around date conversion
- [Copy Action](./copy_action.md) - Create backups before date conversion

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [PHP DateTime Format Documentation](https://www.php.net/manual/en/class.datetime.php)
- [Date Handling Best Practices](../../../user-guide/date-handling.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: formatting*