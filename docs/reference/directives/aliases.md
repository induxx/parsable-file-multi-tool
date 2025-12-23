# Aliases Directive

## Overview

The aliases directive defines reusable placeholders for file paths or filenames used throughout pipeline configurations. This directive enhances configuration flexibility by allowing you to define file references once and reuse them multiple times, making configurations more maintainable and adaptable to different environments.

## Syntax

```yaml
aliases:
  alias_name1: 'file_path_or_pattern'
  alias_name2: 'another_file_path'
  alias_name3: 'file_with_*.csv'
```

## Configuration Options

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| alias_name | string | Yes | - | User-defined name for the alias |
| file_path | string | Yes | - | File path, filename, or pattern to be aliased |

### Configuration Details

#### alias_name
- Must be a valid YAML key
- Should be descriptive and meaningful
- Can be referenced throughout the configuration using the alias name
- Case-sensitive identifier

#### file_path
- Can be a complete file path or just a filename
- Supports wildcard patterns using `*` for dynamic file matching
- Can include placeholder variables like `%sources%` and `%workpath%`
- Must resolve to a single file when wildcards are used

## Examples

### Basic File Aliases

```yaml
# Simple file path aliases
aliases:
  input_file: 'input_data.csv'
  output_file: 'output_data.csv'
  config_file: 'transformation_config.yaml'
```

### Wildcard Patterns

```yaml
# Using wildcards for dynamic file matching
aliases:
  timestamped_input: 'input_data_*.csv'
  product_import_file: 'products_*.csv'
  family_import_file: 'families_*.csv'
```

### Integration with Pipeline

```yaml
# Define aliases
aliases:
  source_data: 'customer_data.csv'
  processed_output: 'processed_customers.csv'

# Use aliases in pipeline
pipeline:
  input:
    reader:
      type: csv
      filename: 'source_data'
  actions:
    clean_data:
      action: retain
      keys: [name, email, phone]
  output:
    writer:
      type: csv
      filename: 'processed_output'
```

## Use Cases

### Use Case 1: Environment-Specific File Paths
Define different file paths for development, staging, and production environments without changing the main pipeline configuration.

### Use Case 2: Timestamped File Processing
Handle files with dynamic timestamps or version numbers using wildcard patterns.

### Use Case 3: Configuration Reusability
Create reusable transformation configurations that can work with different input files by simply changing the alias definitions.

## Behavior and Processing

### Processing Order
Aliases are resolved during the configuration parsing phase, before pipeline execution begins.

### Data Flow
Aliases do not directly affect data flow but determine which files are accessed during pipeline execution.

### Variable Scope
Aliases are globally available throughout the entire configuration file and can be referenced in any directive that accepts file paths.

## Common Patterns

### Pattern 1: Source and Output Pairing
```yaml
aliases:
  raw_data: 'raw_customer_data.csv'
  clean_data: 'cleaned_customer_data.csv'
  final_output: 'final_customer_export.csv'
```

### Pattern 2: Wildcard File Processing
```yaml
aliases:
  daily_export: 'export_*_daily.csv'
  monthly_report: 'report_*_monthly.xlsx'
```

## Common Issues and Solutions

### Issue: Wildcard Matches Multiple Files

**Symptoms:** Error messages about ambiguous file matches or unexpected file selection.

**Cause:** Wildcard pattern matches more than one file in the directory.

**Solution:** Make wildcard patterns more specific or ensure only one matching file exists.

```yaml
# More specific wildcard pattern
aliases:
  specific_export: 'export_2024_01_15_*.csv'  # More specific than 'export_*.csv'
```

### Issue: Alias Not Found

**Symptoms:** Configuration errors about undefined aliases.

**Cause:** Referencing an alias name that wasn't defined in the aliases section.

**Solution:** Ensure all referenced aliases are properly defined.

```yaml
# Correct alias definition and usage
aliases:
  input_data: 'source.csv'

pipeline:
  input:
    reader:
      filename: 'input_data'  # Must match alias name exactly
```

## Best Practices

- Use descriptive alias names that clearly indicate the file's purpose
- Group related aliases together for better organization
- Avoid overly complex wildcard patterns that might match unintended files
- Document the expected file format or structure when using wildcards
- Test wildcard patterns in your target environment to ensure they match correctly

## Related Directives

- [Context](./context.md) - For defining variables and environment settings
- [Pipeline](./pipelines.md) - Where aliases are commonly referenced
- [Sources](../../../data_source/reader.md) - File input configuration

## See Also

- [Directive Overview](../directives.md)
- [Configuration Guide](../../../getting-started/configuration.md)
- [File Processing](../../../user-guide/transformations.md)

---

*Last updated: 2024-12-19*
*Category: reference*
*Directive Type: configuration*