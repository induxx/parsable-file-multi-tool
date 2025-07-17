# Documentation Index

Welcome to the comprehensive documentation for the parsable-file-multi-tool project. This documentation is organized to help you quickly find the information you need, whether you're just getting started or diving deep into advanced features.

## 📚 Getting Started

Perfect for new users who want to quickly set up and begin using the tool.

- **Installation Guide** - Step-by-step setup instructions with prerequisites and troubleshooting
- **Quick Start Tutorial** - Your first transformation with working examples  
- **Configuration Guide** - Setting up accounts, contexts, and security best practices

## 👥 User Guide

Comprehensive guides for day-to-day usage and common workflows.

- [Running Transformations](running_transformations.md) - Complete workflow guide and best practices
- **Debugging & Troubleshooting** - Solve common issues and optimize performance
- **CLI Commands** - Complete command-line reference with examples

## 📖 Reference Documentation

Complete technical reference for all components and features.

### Actions
Transformation actions for data processing and manipulation:

- [Akeneo Value Formatter](actions/akeneo_value_formatter_action.md) - Format values for Akeneo integration
- [Calculate](actions/calculate_action.md) - Perform calculations on data fields
- [Concat](actions/concat_action.md) - Concatenate multiple fields into one
- [Convergence](actions/convergence_action.md) - Handle data convergence scenarios
- [Copy](actions/copy_action.md) - Copy data fields between structures
- [Date Time](actions/date_time_action.md) - Manipulate date and time fields
- [Debug](actions/debug_action.md) - Debugging utility for inspecting transformations
- [Expand](actions/expand_action.md) - Expand data structures for processing
- [Extension](actions/extension_action.md) - Extend functionality with custom actions
- [Field Field](actions/field_field_action.md) - Perform field-to-field transformations
- [Format](actions/format_action.md) - Apply formatting rules to data
- [Key Mapping](actions/key_mapping_action.md) - Map keys between data structures
- [Remove](actions/remove_action.md) - Remove unwanted fields from data
- [Rename](actions/rename_action.md) - Rename fields in data structures
- [Retain](actions/retain_action.md) - Retain specific fields while discarding others
- [Statement](actions/statement_action.md) - Execute conditional statements on data
- [Value Mapping in List](actions/value_mapping_in_list_action.md) - Map values within lists

### Directives
Configuration directives and system options:

- [Directives Overview](directives.md) - Overview of all available directives
- [Aliases](directives/aliases.md) - Create reusable field aliases
- [Context](directives/context.md) - Define context parameters and variables
- [Converters](directives/converters.md) - Configure data format converters
- [List](directives/list.md) - Handle list and array operations
- [Mapping](directives/mapping.md) - Configure field mappings
- [Pipelines](directives/pipelines.md) - Set up processing pipelines
- [Transformation Steps](directives/transformation_steps.md) - Define transformation workflows

### Converters & Tools
Data format converters and utility tools:

- [Akeneo Product Converter](converters/akeneo_product_converter.md) - Convert data for Akeneo PIM
- [XML Data Converter](converters/xml_data.md) - Handle XML data transformations
- [Combine Tool](tools/combine.md) - Combine multiple data sources
- [Compare Tool](tools/compare.md) - Compare datasets and identify differences
- [Copy Tool](tools/copy.md) - Copy and duplicate data structures

### Core Components
System components and processing engines:

- [Caching](caching.md) - Caching mechanisms and performance optimization
- [Decoders](decoders.md) - Data decoding and input processing
- [Encoders](encoders.md) - Output encoding and format generation
- [Formatting](formatting.md) - Data formatting guidelines and options
- [Modifiers](modifiers.md) - Data modification and transformation functions
- [Parser](parser.md) - Input parsing and data structure analysis
- [Processes](processes.md) - Process management and workflow control
- [Reader](reader.md) - Data source reading and input handling
- [Validator](validator.md) - Data validation and quality assurance

### Data Sources
Input and output handling:

- [Reader](data_source/reader.md) - Configure data source readers
- [Writer](data_source/writer.md) - Set up output writers and destinations

### Functions & Helpers
Utility functions and helper components:

- [Modifiers](functions/modifiers.md) - Data modification functions
- [Fetchers](helpers/fetchers.md) - Data fetching utilities
- [Transformation Templates](helpers/transformation_templates.md) - Reusable transformation patterns
- [Sources](Sources/sources.md) - Data source configuration

### Transformations
Specialized transformation modules:

- [Metric Transformation](transformations/metric_transformation.md) - Handle metric and measurement data

## 🔧 Developer Guide

Technical documentation for extending and contributing to the project.

- **Architecture Overview** - System design, components, and data flow
- **Creating Extensions** - Build custom actions and extend functionality  
- **Contributing Guidelines** - Development setup and contribution process

## 💡 Examples & Tutorials

Practical examples and step-by-step tutorials for common use cases.

- **Basic Transformations** - Simple transformation examples with explanations
- **Advanced Workflows** - Complex multi-step transformation patterns

## Quick Navigation

- **New to the project?** Start with [Getting Started](#-getting-started)
- **Looking for a specific action?** Check the [Actions](#actions) reference
- **Need to troubleshoot?** Visit the [User Guide](#-user-guide)
- **Want to extend the tool?** See the [Developer Guide](#-developer-guide)
- **Need working examples?** Browse [Examples & Tutorials](#-examples--tutorials)

## Search Tips

- Use your browser's search (Ctrl/Cmd + F) to find specific terms
- Action names are consistently formatted for easy searching
- Each section includes cross-references to related topics
