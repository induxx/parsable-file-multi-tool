# Debugging and Troubleshooting Guide

---
**Navigation:** [🏠 Home](../index.md) | [📚 Getting Started](../getting-started/) | [👥 User Guide](./) | [📖 Reference](../reference/) | [🔧 Developer Guide](../developer-guide/) | [💡 Examples](../examples/)

**📍 You are here:** [Home](../index.md) > [User Guide](./) > Debugging and Troubleshooting Guide

**👥 User Guide:** [Transformations](./transformations.md) | [Debugging](./debugging.md) | [CLI Commands](../reference/cli-commands.md)
---

This comprehensive guide covers debugging techniques, common error scenarios, and performance optimization strategies for the parsable-file-multi-tool. Whether you're developing new transformations or troubleshooting production issues, this guide provides the tools and techniques you need.

## Overview

Effective debugging in the parsable-file-multi-tool involves understanding data flow, identifying bottlenecks, and using the available debugging tools to isolate and resolve issues. The tool provides multiple debugging mechanisms and diagnostic options to help you understand what's happening during transformation processes.

## Debugging Tools and Techniques

### 1. Debug Mode

Enable comprehensive debugging output using the `--debug` flag:

```bash
bin/console transformation \
  --file transformation.yaml \
  --source sources \
  --workpath workpath \
  --debug
```

**Debug mode provides:**
- Detailed processing logs
- Memory usage information
- Execution timing data
- Data flow tracing
- Error stack traces

### 2. Debug Action

Use the debug action to inspect data at specific points in your pipeline:

```yaml
actions:
  # Debug specific field
  inspect_product_name:
    action: debug
    field: product_name
  
  # Debug complete record
  inspect_full_record:
    action: debug
  
  # Debug until condition
  debug_until_processed:
    action: debug
    until_field: processing_complete
```

### 3. Limited Processing

Test with smaller datasets using the `--try` and `--line` options:

```bash
# Process only first 10 records
bin/console transformation -f transform.yaml -s sources -w workpath --try 10

# Process only line 50
bin/console transformation -f transform.yaml -s sources -w workpath --line 50
```

### 4. Show Mappings

Display mapping and list information using `--showMappings`:

```bash
bin/console transformation -f transform.yaml -s sources -w workpath --showMappings
```

## Common Error Scenarios and Solutions

### 1. File and Path Issues

#### Error: "File not found"
```
Error: The file "source_data.csv" could not be found
```

**Causes:**
- Incorrect file path in configuration
- File doesn't exist in source directory
- Permission issues

**Solutions:**
```yaml
# Verify file paths in aliases
aliases:
  source_file: 'data/customers.csv'  # Check this path exists

# Use absolute paths for testing
pipeline:
  input:
    reader:
      type: csv
      filename: '/full/path/to/file.csv'
```

**Debugging steps:**
1. Check if file exists: `ls -la sources/`
2. Verify file permissions: `ls -la sources/filename.csv`
3. Test with absolute path
4. Check for typos in filename

#### Error: "Directory not accessible"
```
Error: Directory "/path/to/workpath" is not accessible
```

**Solutions:**
```bash
# Create missing directories
mkdir -p examples/my_project/{sources,workpath,transformation}

# Fix permissions
chmod 755 examples/my_project/workpath
```

### 2. Data Format Issues

#### Error: "Invalid CSV format"
```
Error: CSV parsing failed at line 42
```

**Debugging approach:**
```yaml
# Add debug action before processing
actions:
  debug_raw_data:
    action: debug
    field: _raw_line
  
  # Process with error handling
  clean_csv_data:
    action: format
    field: [field1, field2]
    functions: [trim]
```

**Common solutions:**
- Check for unescaped quotes in CSV
- Verify delimiter settings
- Handle empty lines
- Check character encoding

#### Error: "JSON parsing failed"
```
Error: Invalid JSON at position 1234
```

**Debugging steps:**
1. Validate JSON format: `cat file.json | jq .`
2. Check for trailing commas
3. Verify character encoding
4. Look for unescaped characters

### 3. Memory and Performance Issues

#### Error: "Out of memory"
```
Fatal error: Allowed memory size exhausted
```

**Solutions:**
```yaml
# Process in smaller batches
context:
  batch_size: 100
  try: 1000  # Limit total records

# Use streaming readers
pipeline:
  input:
    reader:
      type: csv
      filename: large_file.csv
      streaming: true
```

**Performance optimization:**
```bash
# Monitor memory usage
bin/console transformation -f transform.yaml -s sources -w workpath --debug | grep "Memory"

# Use efficient file formats
# JSONL is often faster than CSV for large datasets
```

#### Error: "Process timeout"
```
Error: Maximum execution time exceeded
```

**Solutions:**
```yaml
# Optimize action sequences
actions:
  # Combine multiple format operations
  optimize_formatting:
    action: format
    field: [field1, field2, field3]  # Process multiple fields at once
    functions: [trim, lower]

# Remove unnecessary actions
# Comment out debug actions in production
```

### 4. Transformation Logic Errors

#### Error: "Field not found"
```
Error: Field 'product_name' not found in data
```

**Debugging approach:**
```yaml
actions:
  # Debug available fields
  show_available_fields:
    action: debug
  
  # Check field existence before processing
  validate_required_fields:
    action: statement
    when:
      field: product_name
      operator: 'NOT_EMPTY'
    then:
      action: retain
```

#### Error: "Invalid action configuration"
```
Error: Action 'format' missing required parameter 'field'
```

**Common configuration errors:**
```yaml
# Wrong: Missing required parameters
actions:
  bad_format:
    action: format
    functions: [trim]  # Missing 'field' parameter

# Correct: All required parameters
actions:
  good_format:
    action: format
    field: product_name
    functions: [trim]
```

### 5. API and External Service Issues

#### Error: "API connection failed"
```
Error: Could not connect to API endpoint
```

**Debugging steps:**
```yaml
# Test API connectivity
actions:
  test_api_connection:
    action: debug
    message: "Testing API connection to {{ context.api_base_url }}"

# Add retry logic
context:
  api_retry_count: 3
  api_timeout: 30
```

**Common solutions:**
- Verify API credentials in secrets.yaml
- Check network connectivity
- Validate API endpoint URLs
- Review rate limiting settings

## Performance Optimization Guidelines

### 1. Data Processing Optimization

#### Efficient Action Ordering
```yaml
# Good: Filter early to reduce data volume
actions:
  filter_valid_records:
    action: statement
    when:
      field: status
      operator: 'EQUALS'
      state: 'active'
    then:
      action: retain
  
  expensive_transformation:
    action: format
    field: description
    functions: [complex_processing]

# Bad: Expensive operations on all data
actions:
  expensive_transformation:
    action: format
    field: description
    functions: [complex_processing]
  
  filter_valid_records:
    action: statement
    when:
      field: status
      operator: 'EQUALS'
      state: 'active'
    then:
      action: retain
```

#### Batch Processing
```yaml
context:
  # Optimize batch sizes based on your data
  batch_size: 500        # For CSV processing
  api_batch_size: 100    # For API calls
  memory_limit: '512M'   # Adjust based on available memory
```

### 2. File Format Optimization

#### Choose Appropriate Formats
```yaml
# For large datasets, prefer JSONL over CSV
output:
  writer:
    type: jsonl  # Faster processing, better memory usage
    filename: large_dataset.jsonl

# For small datasets, CSV is fine
output:
  writer:
    type: csv
    filename: small_dataset.csv
```

#### Streaming for Large Files
```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: very_large_file.csv
      streaming: true  # Process line by line
      buffer_size: 1000  # Adjust buffer size
```

### 3. Memory Management

#### Monitor Memory Usage
```bash
# Enable memory monitoring
bin/console transformation -f transform.yaml -s sources -w workpath --debug 2>&1 | grep -i memory
```

#### Optimize Data Structures
```yaml
actions:
  # Remove unnecessary fields early
  cleanup_unused_fields:
    action: remove
    keys: [temp_field, debug_info, large_description]
  
  # Use efficient data types
  optimize_numbers:
    action: format
    field: [price, quantity]
    functions: [number]
```

## Debugging Strategies

### 1. Systematic Debugging Approach

#### Step 1: Isolate the Problem
```yaml
# Create minimal test case
pipeline:
  input:
    reader:
      type: csv
      filename: test_sample.csv  # Small sample file
  actions:
    debug_input:
      action: debug
  output:
    writer:
      type: csv
      filename: debug_output.csv
```

#### Step 2: Add Progressive Debug Points
```yaml
actions:
  debug_step_1:
    action: debug
    field: input_field
  
  transform_step_1:
    action: format
    field: input_field
    functions: [trim]
  
  debug_step_2:
    action: debug
    field: input_field
  
  transform_step_2:
    action: rename
    from: input_field
    to: output_field
  
  debug_step_3:
    action: debug
    field: output_field
```

#### Step 3: Validate Each Transformation
```yaml
actions:
  validate_transformation:
    action: statement
    when:
      field: output_field
      operator: 'NOT_EMPTY'
    then:
      action: debug
      message: "Transformation successful"
    else:
      action: debug
      message: "Transformation failed"
```

### 2. Data Quality Debugging

#### Check Data Consistency
```yaml
actions:
  validate_data_types:
    action: statement
    when:
      field: price
      operator: 'IS_NUMERIC'
    then:
      action: debug
      message: "Price is numeric: {{ price }}"
    else:
      action: debug
      message: "Invalid price format: {{ price }}"
```

#### Monitor Data Volume
```yaml
actions:
  count_records:
    action: debug
    message: "Processing record {{ _line_number }} of {{ _total_records }}"
```

### 3. Performance Debugging

#### Measure Processing Time
```yaml
actions:
  start_timer:
    action: debug
    message: "Starting processing at {{ _timestamp }}"
  
  # ... processing actions ...
  
  end_timer:
    action: debug
    message: "Completed processing at {{ _timestamp }}"
```

#### Monitor Resource Usage
```bash
# Monitor system resources during processing
top -p $(pgrep -f "transformation") &
bin/console transformation -f transform.yaml -s sources -w workpath --debug
```

## Advanced Debugging Techniques

### 1. Custom Debug Extensions

Create custom PHP extensions for complex debugging:

```php
<?php
// extensions/DebugExtension.php
class DebugExtension
{
    public function debugComplexData($data, $context)
    {
        // Custom debugging logic
        error_log("Complex debug: " . json_encode($data));
        return $data;
    }
}
```

```yaml
actions:
  custom_debug:
    action: extension
    extension: DebugExtension
    method: debugComplexData
```

### 2. Conditional Debugging

Enable debugging based on environment or data conditions:

```yaml
actions:
  conditional_debug:
    action: statement
    when:
      field: debug_mode
      operator: 'EQUALS'
      state: 'true'
    then:
      action: debug
      field: sensitive_data
```

### 3. Multi-Step Debugging

Debug complex multi-step workflows:

```yaml
# main-STEPS.yaml
context:
  debug_enabled: true

transformation_steps:
  - debug-step-1.yaml
  - actual-step-1.yaml
  - debug-step-2.yaml
  - actual-step-2.yaml
```

## Troubleshooting Checklist

### Before Starting
- [ ] Verify all required files exist
- [ ] Check file permissions
- [ ] Validate YAML syntax
- [ ] Test with small dataset first

### During Development
- [ ] Use debug mode for detailed output
- [ ] Add debug actions at key points
- [ ] Test with limited records (`--try`)
- [ ] Monitor memory usage
- [ ] Validate intermediate outputs

### Performance Issues
- [ ] Profile with `--debug` flag
- [ ] Check file sizes and formats
- [ ] Optimize action sequences
- [ ] Consider batch processing
- [ ] Monitor system resources

### Production Deployment
- [ ] Remove debug actions
- [ ] Test with production-like data volumes
- [ ] Set appropriate memory limits
- [ ] Configure proper logging
- [ ] Set up monitoring and alerts

## Getting Help

### Log Analysis
```bash
# Capture detailed logs
bin/console transformation -f transform.yaml -s sources -w workpath --debug > debug.log 2>&1

# Analyze common patterns
grep -i "error\|warning\|failed" debug.log
grep -i "memory" debug.log
grep -i "time" debug.log
```

### Community Resources
- Check existing examples in the `examples/` directory
- Review similar transformation patterns
- Consult the action reference documentation
- Test with minimal reproducible examples

## Related Topics

### Core Debugging Tools and Actions
- **[Debug Action Reference](../actions/debug_action.md)** - Complete debug action documentation and usage examples
- **[Statement Action](../actions/statement_action.md)** - Add conditional debugging logic and validation checks
- **[Extension Action](../actions/extension_action.md)** - Create custom debugging extensions and diagnostic tools
- **[Copy Action](../actions/copy_action.md)** - Create debug backups and preserve original data for analysis

### Command Line and Configuration
- **[CLI Commands](../reference/cli-commands.md)** - All available command options, debug flags, and processing controls
- **[Configuration Guide](../getting-started/configuration.md)** - Set up logging levels, debug settings, and environment configuration
- **[Context Directive](../directives/context.md)** - Use context variables for conditional debugging and environment-specific settings
- **[Pipeline Configuration](../directives/pipelines.md)** - Debug pipeline execution and data flow issues

### Data Processing and Transformation
- **[Transformation Workflow](./transformations.md)** - Understanding data flow, pipeline concepts, and transformation debugging
- **[Format Action](../actions/format_action.md)** - Debug formatting operations and data type issues
- **[Calculate Action](../actions/calculate_action.md)** - Debug mathematical operations and calculation errors
- **[Value Mapping Actions](../actions/value_mapping_in_list_action.md)** - Debug mapping operations and value transformation issues

### Performance and Optimization
- **[Performance Optimization](../user-guide/transformations.md#performance-optimization)** - Advanced optimization techniques and resource management
- **[Memory Management](../user-guide/transformations.md#memory-management)** - Handle memory issues and large dataset processing
- **[Batch Processing](../user-guide/transformations.md#batch-processing)** - Optimize batch operations and throughput
- **[Streaming Processing](../user-guide/transformations.md#streaming)** - Debug streaming workflows and real-time processing

### Error Handling and Troubleshooting
- **[Error Handling Strategies](../user-guide/transformations.md#error-handling)** - Comprehensive error handling and recovery strategies
- **[Data Validation](../user-guide/transformations.md#data-validation)** - Validate data integrity and handle quality issues
- **[File and Path Issues](../getting-started/configuration.md#file-paths)** - Troubleshoot file access and path resolution problems
- **[API Integration Issues](../user-guide/transformations.md#api-troubleshooting)** - Debug external service connections and API errors

### Development and Extension
- **[Extension Development](../developer-guide/extending.md)** - Create custom debugging tools and diagnostic functionality
- **[Architecture Overview](../developer-guide/architecture.md)** - Understanding system components for effective debugging
- **[Contributing Guidelines](../developer-guide/contributing.md)** - Debug development workflows and testing procedures
- **[Development Setup](../developer-guide/contributing.md#development-setup)** - Set up debugging environment and tools

### Data Sources and Integration
- **[Data Source Configuration](../data_source/reader.md)** - Debug data input issues and source connectivity
- **[Data Writer Configuration](../data_source/writer.md)** - Debug output issues and destination problems
- **[Converter Issues](../converters/)** - Debug data format conversion and transformation problems
- **[External Tool Integration](../tools/)** - Debug external tool connectivity and integration issues

### Examples and Patterns
- **[Debugging Examples](../examples/debugging-patterns.md)** - Practical debugging examples and common troubleshooting patterns
- **[Error Recovery Patterns](../examples/error-handling.md)** - Common error scenarios and recovery strategies
- **[Performance Tuning Examples](../examples/performance-optimization.md)** - Real-world performance optimization case studies
- **[Integration Debugging](../examples/integration-debugging.md)** - Debug complex integration scenarios and multi-system workflows

---

## Quick Navigation

- **🏠 [Documentation Home](../index.md)** - Main documentation index
- **🔍 [Search Tips](../index.md#search-tips)** - How to find information quickly
- **❓ [Getting Help](#getting-help)** - Support and troubleshooting resources

### Related User Guide Topics
- **[Running Transformations](./transformations.md)** - Complete workflow guide and best practices
- **[CLI Commands](../reference/cli-commands.md)** - Complete command-line reference with examples
- **[Debug Action](../actions/debug_action.md)** - Debug action reference documentation

### Navigation
- [Previous: Transformations](./transformations.md) | [Next: CLI Commands](../reference/cli-commands.md)
- [Back to User Guide](./index.md)

---
*Last updated: 2024-01-16*  
*Category: user-guide*  
*Tags: debugging, troubleshooting, performance, optimization*