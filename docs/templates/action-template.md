# [Action Name] Action

## Overview

Brief description of what this action does and its primary purpose in data transformation workflows.

## Syntax

```yaml
actions:
  - action: [action_name]
    parameter1: value
    parameter2: value
    # Additional parameters as needed
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| parameter1 | string | Yes | - | Description of the first parameter |
| parameter2 | boolean | No | false | Description of the second parameter |
| parameter3 | array | No | [] | Description of array parameter |

### Parameter Details

#### parameter1
Detailed explanation of the parameter, including:
- Valid values or format requirements
- How it affects the action's behavior
- Any constraints or limitations

#### parameter2
Detailed explanation of the second parameter.

## Examples

### Basic Usage

```yaml
# Simple example showing basic functionality
actions:
  - action: [action_name]
    parameter1: "example_value"
```

**Input:**
```json
{
  "field1": "value1",
  "field2": "value2"
}
```

**Output:**
```json
{
  "field1": "transformed_value1",
  "field2": "value2"
}
```

### Advanced Usage

```yaml
# More complex example with multiple parameters
actions:
  - action: [action_name]
    parameter1: "complex_value"
    parameter2: true
    parameter3:
      - option1
      - option2
```

**Input:**
```json
{
  "complex_field": {
    "nested": "data"
  }
}
```

**Output:**
```json
{
  "complex_field": {
    "nested": "transformed_data",
    "added_field": "new_value"
  }
}
```

## Use Cases

### Use Case 1: [Specific Scenario]
Description of when and why you would use this action in this way.

### Use Case 2: [Another Scenario]
Description of another common use case.

## Common Issues and Solutions

### Issue: [Common Problem]

**Symptoms:** What the user might see when this issue occurs.

**Cause:** Why this issue happens.

**Solution:** Step-by-step resolution.

```yaml
# Corrected configuration example
actions:
  - action: [action_name]
    parameter1: "correct_value"
```

## Performance Considerations

- Notes about performance impact
- Best practices for optimal performance
- When to use alternatives

## Related Actions

- [Format Action](../actions/format_action.md) - Brief description of relationship
- [Calculate Action](../actions/calculate_action.md) - Brief description of relationship

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [Configuration Guide](../getting-started/configuration.md)
- [Debugging Actions](../user-guide/debugging.md)

---

*Last updated: [YYYY-MM-DD]*
*Category: reference*
*Action Type: [transformation|validation|formatting|utility]*