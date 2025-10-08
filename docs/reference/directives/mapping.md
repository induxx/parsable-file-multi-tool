# Mapping Directive

## Overview

The mapping directive defines key-value data sets that can be referenced throughout transformation configurations. Unlike the list directive which handles multi-dimensional arrays, mappings provide simple key-value relationships that are ideal for data translation, field mapping, and lookup operations in processing pipelines.

## Syntax

```yaml
mapping:
  - name: mapping_identifier
    source: mapping_file.yaml
    # OR for combined mappings
  - name: combined_mapping
    sets: [mapping1, mapping2]
```

## Configuration Options

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| name | string | Yes | - | Unique identifier for the mapping |
| source | string | No* | - | External file containing key-value pairs |
| sets | array | No* | - | List of existing mappings to combine |

*Either `source` or `sets` is required.

### Configuration Details

#### name
- Unique identifier used to reference the mapping throughout the configuration
- Must be a valid YAML key
- Case-sensitive identifier

#### source
- Path to external YAML file containing key-value pairs
- File should contain simple key: value structure
- File path can use aliases and context variables

#### sets
- Array of existing mapping names to combine
- Later mappings in the array override earlier ones for duplicate keys
- Enables modular mapping composition

## Examples

### Basic Mapping from Source File

```yaml
# Define mapping from external file
mapping:
  - name: product_codes
    source: product_code_mapping.yaml
```

**Source file (product_code_mapping.yaml):**
```yaml
OLD_PRODUCT_CODE: NEW_PRODUCT_CODE
PRODUCT_A: A001
PRODUCT_B: B002
PRODUCT_C: C003
SKU_OLD_FORMAT: SKU_NEW_FORMAT
```

### Combined Mappings

```yaml
# Define individual mappings
mapping:
  - name: legacy_codes
    source: legacy_mapping.yaml
  - name: new_codes
    source: new_mapping.yaml
  - name: complete_mapping
    sets: [legacy_codes, new_codes]
```

### Using Mappings in Pipelines

```yaml
# Apply mapping in transformation pipeline
mapping:
  - name: field_translations
    source: field_mapping.yaml

pipeline:
  input:
    reader:
      type: csv
      filename: source_data.csv
  actions:
    translate_fields:
      action: key_mapping
      list: field_translations
  output:
    writer:
      type: csv
      filename: translated_data.csv
```

### Multiple Mapping Sources

```yaml
# Combine mappings from different sources
mapping:
  - name: category_mappings
    source: categories.yaml
  - name: status_mappings
    source: statuses.yaml
  - name: attribute_mappings
    source: attributes.yaml
  - name: all_mappings
    sets: [category_mappings, status_mappings, attribute_mappings]
```

## Use Cases

### Use Case 1: Data Translation
Transform legacy field names or values to new standardized formats.

### Use Case 2: Code Mapping
Convert between different coding systems or identifier formats.

### Use Case 3: Modular Configuration
Combine multiple mapping sources for complex transformation scenarios.

## Behavior and Processing

### Processing Order
Mappings are resolved during configuration parsing, before pipeline execution begins.

### Data Flow
Mappings serve as lookup tables and don't directly participate in the main data flow unless referenced by actions.

### Variable Scope
Mappings are globally available throughout the configuration and can be referenced by name in any directive.

## Common Patterns

### Pattern 1: Field Name Translation
```yaml
# field_mapping.yaml
old_customer_id: customer_identifier
old_product_name: product_title
old_price_field: unit_price
old_category: product_category
```

### Pattern 2: Status Code Mapping
```yaml
# status_mapping.yaml
A: Active
I: Inactive
P: Pending
D: Deleted
S: Suspended
```

### Pattern 3: Hierarchical Mapping Combination
```yaml
mapping:
  - name: base_mappings
    source: base_config.yaml
  - name: environment_mappings
    source: prod_overrides.yaml
  - name: final_mappings
    sets: [base_mappings, environment_mappings]  # prod_overrides take precedence
```

## Mapping File Format

### Simple Key-Value Structure
```yaml
# mapping_file.yaml
source_key1: target_value1
source_key2: target_value2
source_key3: target_value3
```

### Complex Value Mapping
```yaml
# complex_mapping.yaml
legacy_status_1: 
  code: ACTIVE
  description: "Customer is active"
legacy_status_2:
  code: INACTIVE
  description: "Customer is inactive"
```

## Common Issues and Solutions

### Issue: Mapping File Not Found

**Symptoms:** Error messages about missing mapping source files.

**Cause:** Source file path is incorrect or file doesn't exist.

**Solution:** Verify file paths and ensure mapping files exist.

```yaml
# Use proper file paths with context variables
mapping:
  - name: product_mappings
    source: '%workpath%/mappings/products.yaml'
```

### Issue: Circular Mapping References

**Symptoms:** Configuration errors or infinite loops during mapping resolution.

**Cause:** Mapping sets reference each other in a circular manner.

**Solution:** Ensure mapping dependencies are acyclic.

```yaml
# Correct hierarchical structure
mapping:
  - name: base_mapping
    source: base.yaml
  - name: extended_mapping
    source: extended.yaml
  - name: final_mapping
    sets: [base_mapping, extended_mapping]  # No circular references
```

### Issue: Key Conflicts in Combined Mappings

**Symptoms:** Unexpected mapping results or values being overwritten.

**Cause:** Multiple mappings in a set contain the same keys.

**Solution:** Order mappings intentionally, with higher priority mappings later in the sets array.

```yaml
# Intentional override order
mapping:
  - name: default_mappings
    source: defaults.yaml
  - name: custom_mappings
    source: custom.yaml
  - name: final_mappings
    sets: [default_mappings, custom_mappings]  # custom overrides defaults
```

## Best Practices

- Use descriptive mapping names that clearly indicate their purpose
- Keep mapping files organized in a dedicated directory structure
- Document the source and purpose of each mapping file
- Use consistent key naming conventions across related mappings
- Test combined mappings to ensure proper override behavior
- Validate mapping files contain expected key-value structures
- Consider performance implications of large mapping sets

## Related Topics

### Core Configuration Directives
- **[List Directive](./list.md)** - For multi-dimensional data collections and arrays that complement mappings
- **[Context Directive](./context.md)** - Define variables used in mapping sources and dynamic mapping paths
- **[Aliases Directive](./aliases.md)** - Create reusable field aliases that work with mappings and file references
- **[Transformation Steps](./transformation_steps.md)** - Use mappings across multi-step workflows and complex transformations

### Data Processing Integration
- **[Pipeline Configuration](./pipelines.md)** - Integrate mappings into processing pipelines and data workflows
- **[Converters Directive](./converters.md)** - Use mappings with data format converters and transformation rules
- **[Transformation Steps](./transformation_steps.md)** - Apply mappings across multi-step transformation processes

### Mapping-Related Actions
- **[Key Mapping Action](../actions/key_mapping_action.md)** - Apply mappings to transform field keys and restructure data
- **[Value Mapping in List Action](../actions/value_mapping_in_list_action.md)** - Map values within lists using predefined mappings
- **[Statement Action](../actions/statement_action.md)** - Use mappings in conditional logic and decision-making processes
- **[Format Action](../actions/format_action.md)** - Use mappings for value replacement in formatting operations

### Data Transformation Actions
- **[Copy Action](../actions/copy_action.md)** - Use mappings to determine copy operations and field standardization
- **[Rename Action](../actions/rename_action.md)** - Apply systematic field renaming using mapping configurations
- **[Calculate Action](../actions/calculate_action.md)** - Use mapped values in calculations and mathematical operations
- **[Concat Action](../actions/concat_action.md)** - Combine mapped values in string concatenation operations

### Configuration and Setup
- **[Configuration Guide](../../../getting-started/configuration.md)** - Set up mapping files, directory structure, and file organization
- **[File Organization](../../../developer-guide/architecture.md#file-organization)** - Best practices for organizing mapping files and directory structure
- **[Environment Configuration](../../../getting-started/configuration.md#environment-variables)** - Use environment variables in mapping paths and dynamic configuration
- **[Quick Start Guide](../../../getting-started/quick-start.md)** - Basic mapping setup and usage examples

### Data Transformation and Workflows
- **[Data Transformation Guide](../../../user-guide/transformations.md)** - Understanding how mappings fit in transformation workflows
- **[Pipeline Configuration](./pipelines.md)** - Integrate mappings into processing pipelines and data flow
- **[Multi-step Transformations](./transformation_steps.md)** - Use mappings across complex workflows and transformation chains
- **[Data Standardization](../../../user-guide/transformations.md#data-standardization)** - Use mappings for data normalization and standardization

### Development and Debugging
- **[Debugging Guide](../../../user-guide/debugging.md)** - Debug mapping-related issues and troubleshoot mapping resolution
- **[Extension Development](../../../developer-guide/extending.md)** - Create custom mapping functionality and advanced mapping operations
- **[Performance Optimization](../../../user-guide/debugging.md#performance-optimization-guidelines)** - Optimize mapping performance and memory usage
- **[CLI Commands](../cli-commands.md)** - Test mappings with debug mode and limited data processing

### Advanced Usage and Integration
- **[API Integration](../../../user-guide/transformations.md#api-integration)** - Use mappings for API data transformation and field mapping
- **[Data Migration](../../../user-guide/transformations.md#data-migration)** - Apply mappings in data migration scenarios and schema transformation
- **[Error Handling](../../../user-guide/debugging.md#common-error-scenarios-and-solutions)** - Handle mapping errors and resolution failures

## See Also

- **[Directives Reference](./index.md)** - Complete list of all available directives and configuration options
- **[Actions Reference](../actions/index.md)** - Actions that work with mappings and data transformation
- **[Transformation Examples](../../../examples/)** - Practical mapping examples and common transformation patterns
- **[Data Mapping Best Practices](../../../examples/data-mapping.md)** - Advanced mapping strategies and optimization techniques

---

*Last updated: 2024-12-19*
*Category: reference*
*Directive Type: data-source*
