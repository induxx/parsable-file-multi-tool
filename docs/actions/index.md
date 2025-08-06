# Actions Reference

---
**Navigation:** [🏠 Home](../index.md) | [📚 Getting Started](../getting-started/) | [👥 User Guide](../user-guide/) | [📖 Reference](./) | [🔧 Developer Guide](../developer-guide/) | [💡 Examples](../examples/)

**📍 You are here:** [Home](../index.md) > [Reference](./) > Actions Reference

**📖 Reference:** [Actions](./) | [Directives](../directives/) | [Converters](../converters/) | [Tools](../tools/) | [CLI Commands](../reference/cli-commands.md)
---

Actions are the core building blocks of transformations in the parsable-file-multi-tool. Each action performs a specific operation on your data, from simple field copying to complex calculations and conditional logic.

## Action Categories

### Data Transformation Actions
Actions that modify, format, or transform data values.

- **[Calculate](./calculate_action.md)** - Perform arithmetic operations on fields
- **[Concat](./concat_action.md)** - Concatenate multiple fields into one
- **[Format](./format_action.md)** - Apply formatting rules to data fields
- **[Date Time](./date_time_action.md)** - Manipulate date and time fields

### Data Structure Actions
Actions that modify the structure or organization of data.

- **[Copy](./copy_action.md)** - Copy data fields between structures
- **[Rename](./rename_action.md)** - Rename fields in data structures
- **[Remove](./remove_action.md)** - Remove unwanted fields from data
- **[Retain](./retain_action.md)** - Retain specific fields while discarding others

### Conditional and Logic Actions
Actions that implement conditional logic and decision-making.

- **[Statement](./statement_action.md)** - Execute conditional statements on data
- **[Convergence](./convergence_action.md)** - Handle data convergence scenarios

### Data Processing Actions
Actions for advanced data processing and manipulation.

- **[Expand](./expand_action.md)** - Expand data structures for processing
- **[Field Field](./field_field_action.md)** - Perform field-to-field transformations
- **[Key Mapping](./key_mapping_action.md)** - Map keys between data structures
- **[Value Mapping in List](./value_mapping_in_list_action.md)** - Map values within lists

### Integration Actions
Actions for external system integration and specialized formats.

- **[Akeneo Value Formatter](./akeneo_value_formatter_action.md)** - Format values for Akeneo integration

### Utility Actions
Actions for debugging, extension, and utility functions.

- **[Debug](./debug_action.md)** - Debugging utility for inspecting transformations
- **[Extension](./extension_action.md)** - Extend functionality with custom actions

## Complete Actions List

| Action | Purpose | Category |
|--------|---------|----------|
| [Akeneo Value Formatter](./akeneo_value_formatter_action.md) | Format values for Akeneo integration | Integration |
| [Calculate](./calculate_action.md) | Perform calculations on data fields | Transformation |
| [Concat](./concat_action.md) | Concatenate multiple fields into one | Transformation |
| [Convergence](./convergence_action.md) | Handle data convergence scenarios | Logic |
| [Copy](./copy_action.md) | Copy data fields between structures | Structure |
| [Date Time](./date_time_action.md) | Manipulate date and time fields | Transformation |
| [Debug](./debug_action.md) | Debugging utility for inspections | Utility |
| [Expand](./expand_action.md) | Expand data structures for processing | Processing |
| [Extension](./extension_action.md) | Extend functionality with custom actions | Utility |
| [Field Field](./field_field_action.md) | Perform field-to-field transformations | Processing |
| [Format](./format_action.md) | Apply formatting rules to data | Transformation |
| [Key Mapping](./key_mapping_action.md) | Map keys between data structures | Processing |
| [Remove](./remove_action.md) | Remove unwanted fields from data | Structure |
| [Rename](./rename_action.md) | Rename fields in data structures | Structure |
| [Retain](./retain_action.md) | Retain specific fields while discarding others | Structure |
| [Statement](./statement_action.md) | Execute conditional statements on data | Logic |
| [Value Mapping in List](./value_mapping_in_list_action.md) | Map values within lists | Processing |

## Common Action Patterns

### Basic Data Cleaning
```yaml
actions:
  clean_data:
    action: format
    field: [name, description]
    functions: [trim, title_case]
  
  remove_empty:
    action: remove
    keys: [temp_field, debug_info]
```

### Conditional Processing
```yaml
actions:
  process_if_valid:
    action: statement
    when:
      field: status
      operator: 'EQUALS'
      state: 'active'
    then:
      action: format
      field: price
      functions: [number, round:2]
```

### Data Transformation Chain
```yaml
actions:
  step_1:
    action: copy
    from: raw_price
    to: price
  
  step_2:
    action: calculate
    fields: [price, tax_rate]
    operator: MULTIPLY
    result: tax_amount
  
  step_3:
    action: calculate
    fields: [price, tax_amount]
    operator: ADD
    result: total_price
```

## Action Usage Guidelines

### Best Practices
1. **Order Matters**: Actions execute in sequence, plan your transformation pipeline
2. **Field Validation**: Use conditional actions to validate data before processing
3. **Error Handling**: Include debug actions to troubleshoot transformation issues
4. **Performance**: Group similar operations and remove unnecessary fields early

### Common Patterns
- **Data Validation**: Use `statement` actions to check data quality
- **Field Cleanup**: Use `format` actions to standardize data
- **Conditional Logic**: Combine `statement` and other actions for complex logic
- **Debugging**: Add `debug` actions at key points in your pipeline

## Troubleshooting Actions

### Debug Your Transformations
```yaml
actions:
  debug_input:
    action: debug
    field: input_field
  
  transform_data:
    action: format
    field: input_field
    functions: [trim, lower]
  
  debug_output:
    action: debug
    field: input_field
```

### Handle Missing Fields
```yaml
actions:
  set_defaults:
    action: copy
    from: optional_field
    to: safe_field
    default: "default_value"
```

### Validate Results
```yaml
actions:
  validate_result:
    action: statement
    when:
      field: calculated_field
      operator: 'NOT_EMPTY'
    then:
      action: debug
      message: "Calculation successful"
    else:
      action: debug
      message: "Calculation failed"
```

## Related Topics

### Core Configuration and Setup
- **[Pipeline Configuration](../directives/pipelines.md)** - Integrate actions into data processing pipelines and workflows
- **[Context Directive](../directives/context.md)** - Use context variables in action parameters and dynamic configuration
- **[Transformation Steps](../directives/transformation_steps.md)** - Orchestrate multi-step action workflows and complex transformations
- **[Configuration Guide](../getting-started/configuration.md)** - Set up action parameters, environment variables, and configuration files

### Data Processing and Transformation
- **[Transformation Guide](../user-guide/transformations.md)** - Understanding action workflows, data flow, and processing concepts
- **[Data Sources](../data_source/reader.md)** - Configure input data sources that feed into action pipelines
- **[Data Writers](../data_source/writer.md)** - Set up output destinations for action-processed data
- **[Mapping Directive](../directives/mapping.md)** - Use mappings with actions for value transformation and field mapping

### Development and Debugging
- **[Debugging Guide](../user-guide/debugging.md)** - Debug action execution, troubleshoot issues, and optimize performance
- **[CLI Commands](../reference/cli-commands.md)** - Execute actions from command line with debug options and processing controls
- **[Extension Development](../developer-guide/extending.md)** - Create custom actions and extend functionality
- **[Architecture Overview](../developer-guide/architecture.md)** - Understanding action system architecture and components

### Advanced Usage and Integration
- **[API Integration](../user-guide/transformations.md#api-integration)** - Use actions with external APIs and web services
- **[Performance Optimization](../user-guide/debugging.md#performance-optimization-guidelines)** - Optimize action performance for large datasets and high throughput
- **[Error Handling](../user-guide/debugging.md#common-error-scenarios-and-solutions)** - Handle action errors and implement recovery strategies
- **[Multi-format Processing](../converters/)** - Use actions with different data formats and converters

### Practical Examples and Patterns
- **[Transformation Examples](../examples/)** - Real-world action usage examples and common transformation patterns
- **[Basic Transformation](../examples/basic-transformation.md)** - Simple action patterns and techniques for beginners
- **[Advanced Workflows](../examples/advanced-workflows.md)** - Complex action sequences and sophisticated transformation patterns
- **[Integration Examples](../examples/integration-patterns.md)** - Action usage in external system integration scenarios

## See Also

- **[Directives Reference](../directives/)** - Configuration directives that work with actions
- **[User Guide](../user-guide/)** - Comprehensive usage documentation and workflows
- **[Reference Documentation](../reference/)** - Complete technical reference and API documentation
- **[Developer Guide](../developer-guide/)** - Advanced development and customization topics

---

## Quick Navigation

- **🏠 [Documentation Home](../index.md)** - Main documentation index
- **🔍 [Search Tips](../index.md#search-tips)** - How to find information quickly
- **❓ [Getting Help](../user-guide/debugging.md#getting-help)** - Support and troubleshooting resources

### Action Categories
- **[Data Transformation](#data-transformation-actions)** - Modify and format data values
- **[Data Structure](#data-structure-actions)** - Modify data organization
- **[Conditional Logic](#conditional-and-logic-actions)** - Implement decision-making
- **[Integration](#integration-actions)** - External system integration
- **[Utilities](#utility-actions)** - Debugging and extensions

### Related Reference
- [Directives](../directives/) | [Converters](../converters/) | [Tools](../tools/) | [CLI Commands](../reference/cli-commands.md)

---
*Last updated: 2024-01-16*  
*Category: reference*  
*Tags: actions, transformations, reference, data-processing*