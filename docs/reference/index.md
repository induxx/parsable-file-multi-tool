# Reference Documentation

---
**Navigation:** [🏠 Home](../index.md) | [📚 Getting Started](../../../getting-started/) | [👥 User Guide](../../../user-guide/) | [📖 Reference](./) | [🔧 Developer Guide](../../../developer-guide/) | [💡 Examples](../../../examples/)

**📍 You are here:** [Home](../index.md) > Reference Documentation
---

The Reference Documentation provides complete technical reference for all components, actions, directives, and features of the parsable-file-multi-tool. This section is organized for quick lookup and comprehensive coverage of all functionality.

## Reference Categories

### [🎯 Actions](./actions/)
Transformation actions for data processing and manipulation. Actions are the core building blocks of transformations.

**Key Actions:**
- [Calculate](./actions/calculate_action.md) - Perform arithmetic operations
- [Format](./actions/format_action.md) - Apply formatting rules to data
- [Debug](./actions/debug_action.md) - Debugging utility for inspections
- [Copy](./actions/copy_action.md) - Copy data fields between structures
- [Statement](./actions/statement_action.md) - Execute conditional statements

[**View All Actions →**](./actions/)

### [🎛️ Directives](./directives/)
Configuration directives and system options that control transformation behavior.

**Key Directives:**
- [Mapping](./directives/mapping.md) - Configure field mappings
- [Pipelines](./directives/pipelines.md) - Set up processing pipelines
- [Context](./directives/context.md) - Define context parameters and variables
- [Aliases](./directives/aliases.md) - Create reusable field aliases

[**View All Directives →**](./directives/)

### [🔄 Converters](./converters/)
Data format converters for handling different input and output formats.

**Available Converters:**
- [Akeneo Product Converter](./converters/akeneo_product_converter.md) - Convert data for Akeneo PIM
- [XML Data Converter](./converters/xml_data.md) - Handle XML data transformations

[**View All Converters →**](./converters/)

### [🛠️ Tools](./tools/)
Utility tools for data manipulation and analysis.

**Available Tools:**
- [Combine Tool](./tools/combine.md) - Combine multiple data sources
- [Compare Tool](./tools/compare.md) - Compare datasets and identify differences
- [Copy Tool](./tools/copy.md) - Copy and duplicate data structures

[**View All Tools →**](./tools/)

### [💻 CLI Commands](./cli-commands.md)
Complete command-line interface reference with all options and parameters.

**Key Commands:**
- `transformation` - Run data transformations
- `compare` - Compare datasets
- Global options and flags

## Core Components

### System Components
- [Caching](../caching.md) - Caching mechanisms and performance optimization
- [Decoders](../decoders.md) - Data decoding and input processing
- [Encoders](../encoders.md) - Output encoding and format generation
- [Parser](../parser.md) - Input parsing and data structure analysis
- [Validator](../validator.md) - Data validation and quality assurance

### Data Handling
- [Reader](../../../data_source/reader.md) - Configure data source readers
- [Writer](../../../data_source/writer.md) - Set up output writers and destinations
- [Formatting](../formatting.md) - Data formatting guidelines and options
- [Modifiers](../modifiers.md) - Data modification and transformation functions

### Functions & Helpers
- [Modifiers](../functions/modifiers.md) - Data modification functions
- [Fetchers](../../../helpers/fetchers.md) - Data fetching utilities
- [Transformation Templates](../../../helpers/transformation_templates.md) - Reusable transformation patterns

## Quick Reference

### Common Patterns
```yaml
# Basic transformation structure
pipeline:
  input:
    reader:
      type: csv
      filename: input.csv
  actions:
    - action: format
      field: field_name
      functions: [trim, lower]
  output:
    writer:
      type: csv
      filename: output.csv
```

### Frequently Used Actions
- **Data Cleaning:** `format`, `remove`, `retain`
- **Data Transformation:** `calculate`, `concat`, `rename`
- **Conditional Logic:** `statement`, `convergence`
- **Debugging:** `debug`, `extension`

### Common Directives
- **Configuration:** `context`, `aliases`
- **Data Flow:** `mapping`, `pipelines`
- **Processing:** `transformation_steps`, `list`

## Navigation by Use Case

### Data Transformation
- [Actions Reference](./actions/) - All available transformation actions
- [Transformation Steps](./directives/transformation_steps.md) - Multi-step workflows
- [Mapping Directives](./directives/mapping.md) - Field mapping configuration

### Data Integration
- [Converters](./converters/) - Format conversion tools
- [Reader Configuration](../../../data_source/reader.md) - Input source setup
- [Writer Configuration](../../../data_source/writer.md) - Output destination setup

### Debugging & Optimization
- [Debug Action](./actions/debug_action.md) - Debugging utilities
- [CLI Commands](./cli-commands.md) - Command-line debugging options
- [Performance Tools](../../../user-guide/debugging.md#performance-optimization-guidelines)

---

## Quick Navigation

- **🏠 [Documentation Home](../index.md)** - Main documentation index
- **🔍 [Search Tips](../index.md#search-tips)** - How to find information quickly
- **❓ [Getting Help](../../../user-guide/debugging.md#getting-help)** - Support and troubleshooting resources

### Reference Categories
- **[Actions](./actions/)** - Transformation actions and data processing
- **[Directives](./directives/)** - Configuration directives and system options
- **[Converters](./converters/)** - Data format converters
- **[Tools](./tools/)** - Utility tools and helpers
- **[CLI Commands](./cli-commands.md)** - Complete command-line reference

### Related Sections
- [User Guide](../../../user-guide/) | [Developer Guide](../../../developer-guide/) | [Examples](../../../examples/)

---
*Last updated: 2024-01-16*  
*Category: reference*  
*Tags: reference, actions, directives, converters, tools, cli, api*