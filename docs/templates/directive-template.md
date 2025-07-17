# [Directive Name] Directive

## Overview

Brief description of what this directive does and its role in the transformation configuration.

## Syntax

```yaml
[directive_name]:
  option1: value
  option2: value
  # Additional configuration options
```

## Configuration Options

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| option1 | string | Yes | - | Description of the first option |
| option2 | boolean | No | false | Description of the second option |
| option3 | object | No | {} | Description of object option |

### Configuration Details

#### option1
Detailed explanation of the configuration option, including:
- Valid values or format requirements
- How it affects the directive's behavior
- Any constraints or limitations

#### option2
Detailed explanation of the second option.

#### option3
For complex options, provide structure details:

```yaml
option3:
  sub_option1: value
  sub_option2: value
```

## Examples

### Basic Configuration

```yaml
# Simple example showing basic usage
[directive_name]:
  option1: "basic_value"
```

### Advanced Configuration

```yaml
# More complex example with multiple options
[directive_name]:
  option1: "advanced_value"
  option2: true
  option3:
    sub_option1: "nested_value"
    sub_option2: 42
    sub_array:
      - item1
      - item2
```

### Integration Example

```yaml
# Example showing how this directive works with others
context:
  variable: "example"

[directive_name]:
  option1: "{{ variable }}"
  option2: true

transformation_steps:
  - actions:
      - action: copy
        field: source_field
```

## Use Cases

### Use Case 1: [Specific Scenario]
Description of when and why you would use this directive configuration.

### Use Case 2: [Another Scenario]
Description of another common use case with example.

## Behavior and Processing

### Processing Order
Explanation of when this directive is processed in relation to other directives.

### Data Flow
Description of how this directive affects data flow through the transformation pipeline.

### Variable Scope
If applicable, explain variable scoping and availability.

## Common Patterns

### Pattern 1: [Common Usage Pattern]
```yaml
# Example of a common configuration pattern
[directive_name]:
  option1: "pattern_value"
  option2: true
```

### Pattern 2: [Another Pattern]
```yaml
# Example of another useful pattern
[directive_name]:
  option1: "{{ context.variable }}"
  option3:
    dynamic_option: "{{ calculated_value }}"
```

## Common Issues and Solutions

### Issue: [Common Problem]

**Symptoms:** What the user might see when this issue occurs.

**Cause:** Why this issue happens.

**Solution:** Step-by-step resolution with corrected configuration.

```yaml
# Corrected configuration
[directive_name]:
  option1: "correct_value"
```

## Best Practices

- Recommendation 1 with explanation
- Recommendation 2 with explanation
- Performance considerations
- Security considerations (if applicable)

## Related Directives

- [Related Directive 1](./related_directive_1.md) - Brief description of relationship
- [Related Directive 2](./related_directive_2.md) - Brief description of relationship

## See Also

- [Directive Overview](../directives.md)
- [Configuration Guide](../user-guide/configuration.md)
- [Context Variables](./context.md)
- [Transformation Pipeline](../user-guide/transformation-workflow.md)

---

*Last updated: [YYYY-MM-DD]*
*Category: reference*
*Directive Type: [configuration|processing|data-source|output]*