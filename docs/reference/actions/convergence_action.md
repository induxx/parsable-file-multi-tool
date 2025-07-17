
# Convergence Action

## Overview

The convergence action allows you to combine multiple fields into a single field with customizable formatting options. It's essential for data consolidation workflows where you need to merge related fields into structured, readable formats with control over separators and encapsulation.

## Syntax

```yaml
actions:
  - action: convergence
    fields: [field1, field2, field3]
    store_field: target_field
    item_sep: ' ,'
    key_value_sep: ': '
    encapsulate: false
    encapsulation_char: '"'
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| fields | array | Yes | - | Array of field names to combine |
| store_field | string | Yes | - | Target field name where the combined result will be stored |
| item_sep | string | No | ' ,' | Separator between field items |
| key_value_sep | string | No | ': ' | Separator between field name and value |
| encapsulate | boolean | No | false | Whether to encapsulate values |
| encapsulation_char | string | No | '"' | Character to use for encapsulation |

### Parameter Details

#### fields
An array containing the names of fields to be combined into the target field.

- **Format:** Array of strings
- **Example:** `["street", "city", "state"]`
- **Behavior:** All specified fields will be included in the convergence operation

#### store_field
The name of the target field where the combined result will be stored.

- **Format:** String field name
- **Example:** `"address_line"`
- **Behavior:** If the field already exists, it will be overwritten

#### item_sep
The separator string used between different field items in the combined result.

- **Format:** String
- **Example:** `" ,"`, `" | "`, `"; "`
- **Behavior:** Inserted between each field in the final output

#### key_value_sep
The separator string used between field names and their values.

- **Format:** String
- **Example:** `": "`, `"="`, `" -> "`
- **Behavior:** Inserted between field name and field value

#### encapsulate
Whether to encapsulate field values with the specified encapsulation character.

- **Format:** Boolean
- **Example:** `true`, `false`
- **Behavior:** When true, wraps each value with encapsulation_char

#### encapsulation_char
The character used to encapsulate field values when encapsulate is true.

- **Format:** String (typically single character)
- **Example:** `'"'`, `"'"`, `"`"`
- **Behavior:** Used to wrap field values when encapsulation is enabled

## Examples

### Basic Address Convergence

```yaml
actions:
  - action: convergence
    fields: ['street', 'city', 'state']
    store_field: 'address_line'
    item_sep: ', '
    key_value_sep: ': '
    encapsulate: false
    encapsulation_char: '"'
```

**Input:**
```json
{
  "street": "123 Main Street",
  "city": "Anytown",
  "state": "CA",
  "zip": "12345"
}
```

**Output:**
```json
{
  "street": "123 Main Street",
  "city": "Anytown",
  "state": "CA",
  "zip": "12345",
  "address_line": "street: 123 Main Street, city: Anytown, state: CA"
}
```

### Contact Information with Encapsulation

```yaml
actions:
  - action: convergence
    fields: ['name', 'email', 'phone']
    store_field: 'contact_info'
    item_sep: ' | '
    key_value_sep: '='
    encapsulate: true
    encapsulation_char: '"'
```

**Input:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "555-0123"
}
```

**Output:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "555-0123",
  "contact_info": "name=\"John Doe\" | email=\"john@example.com\" | phone=\"555-0123\""
}
```

### Product Specifications

```yaml
actions:
  - action: convergence
    fields: ['brand', 'model', 'color', 'size']
    store_field: 'product_specs'
    item_sep: '; '
    key_value_sep: ': '
    encapsulate: false
```

**Input:**
```json
{
  "brand": "TechCorp",
  "model": "Widget Pro",
  "color": "Blue",
  "size": "Large",
  "price": 299.99
}
```

**Output:**
```json
{
  "brand": "TechCorp",
  "model": "Widget Pro",
  "color": "Blue",
  "size": "Large",
  "price": 299.99,
  "product_specs": "brand: TechCorp; model: Widget Pro; color: Blue; size: Large"
}
```

### Configuration Summary

```yaml
actions:
  - action: convergence
    fields: ['host', 'port', 'database', 'username']
    store_field: 'connection_string'
    item_sep: ' '
    key_value_sep: '='
    encapsulate: true
    encapsulation_char: "'"
```

**Input:**
```json
{
  "host": "localhost",
  "port": "5432",
  "database": "mydb",
  "username": "admin"
}
```

**Output:**
```json
{
  "host": "localhost",
  "port": "5432",
  "database": "mydb",
  "username": "admin",
  "connection_string": "host='localhost' port='5432' database='mydb' username='admin'"
}
```

## Use Cases

### Use Case 1: Address Formatting
Combine address components into a single formatted address line for display or export.

### Use Case 2: Configuration String Generation
Create configuration strings or connection strings from individual parameters.

### Use Case 3: Data Export Preparation
Merge related fields into single columns for simplified data export formats.

## Common Issues and Solutions

### Issue: Missing Field Values

**Symptoms:** Convergence result contains empty or null values in the combined string.

**Cause:** Some fields specified in the fields array don't exist or contain null values.

**Solution:** Ensure all fields exist or handle missing values before convergence.

```yaml
# Set default values before convergence
actions:
  - action: expand
    set:
      street: ''
      city: ''
      state: ''
  - action: convergence
    fields: ['street', 'city', 'state']
    store_field: 'address_line'
```

### Issue: Unwanted Separators

**Symptoms:** Extra separators appear in the result when some fields are empty.

**Cause:** Convergence includes separators even for empty field values.

**Solution:** Filter out empty fields before convergence or use conditional logic.

```yaml
# Use statement to check for non-empty fields
actions:
  - action: statement
    when:
      field: street
      operator: NOT_EMPTY
    then:
      - action: convergence
        fields: ['street', 'city', 'state']
        store_field: 'address_line'
```

### Issue: Encapsulation Character Conflicts

**Symptoms:** Encapsulation characters appear incorrectly in the final result.

**Cause:** Field values contain the same character used for encapsulation.

**Solution:** Choose a different encapsulation character or escape existing ones.

```yaml
# Use different encapsulation character
actions:
  - action: convergence
    fields: ['description']
    store_field: 'formatted_desc'
    encapsulate: true
    encapsulation_char: "'"  # Use single quotes instead of double
```

## Performance Considerations

- String concatenation operations are generally fast
- Performance scales linearly with the number of fields
- Encapsulation adds minimal overhead
- Consider memory usage when combining large text fields

## Related Actions

- [Concat Action](./concat_action.md) - Simple field concatenation with placeholders
- [Format Action](./format_action.md) - Format individual fields before convergence
- [Statement Action](./statement_action.md) - Add conditional logic around convergence

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [String Handling](../../../user-guide/string-handling.md)
- [Field Management](../../../user-guide/field-management.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*