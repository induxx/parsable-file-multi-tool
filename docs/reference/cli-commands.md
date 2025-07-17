# CLI Commands Reference

This comprehensive reference covers all command-line interface options available in the parsable-file-multi-tool. The tool provides powerful CLI commands for data transformation, file comparison, and project management.

## Overview

The parsable-file-multi-tool provides a console application with multiple commands accessible through the `bin/console` script. Commands can be run directly or through convenient wrapper scripts for common workflows.

### Basic Usage

```bash
# Direct command usage
bin/console [command] [options]

# Using Docker wrapper
bin/docker/console [command] [options]

# Using project wrapper (recommended for transformations)
PROJECT=my_project bin/docker/run_example.sh transformation_file.yaml
```

## Available Commands

### 1. transformation

The primary command for running data transformation workflows.

#### Syntax

```bash
bin/console transformation [options]
```

#### Options

| Option | Short | Type | Required | Default | Description |
|--------|-------|------|----------|---------|-------------|
| `--file` | `-f` | string | Yes | - | Path to transformation YAML file |
| `--source` | `-s` | string | Yes | - | Source data directory path |
| `--workpath` | `-w` | string | Yes | - | Working directory for outputs and temp files |
| `--addSource` | | string | No | - | Additional source directory path |
| `--extensions` | | string | No | - | Custom extensions directory path |
| `--debug` | `-d` | boolean | No | false | Enable detailed debug output |
| `--line` | `-l` | integer | No | - | Process only specific line number |
| `--try` | `-t` | integer | No | - | Limit processing to N records for testing |
| `--showMappings` | `-m` | boolean | No | false | Display mapping and list information |

#### Examples

##### Basic Transformation
```bash
bin/console transformation \
  --file examples/my_project/transformation/main-STEPS.yaml \
  --source examples/my_project/sources \
  --workpath examples/my_project/workpath
```

##### Debug Mode with Limited Records
```bash
bin/console transformation \
  --file transform.yaml \
  --source ./sources \
  --workpath ./workpath \
  --debug \
  --try 100
```

##### With Additional Sources and Extensions
```bash
bin/console transformation \
  --file transform.yaml \
  --source ./sources \
  --workpath ./workpath \
  --addSource ./additional_sources \
  --extensions ./custom_extensions
```

##### Process Specific Line
```bash
bin/console transformation \
  --file transform.yaml \
  --source ./sources \
  --workpath ./workpath \
  --line 42
```

#### Usage Patterns

##### Development and Testing
```bash
# Test with small dataset
bin/console transformation -f transform.yaml -s sources -w workpath --try 10 --debug

# Debug specific line
bin/console transformation -f transform.yaml -s sources -w workpath --line 100 --debug

# Show mapping information
bin/console transformation -f transform.yaml -s sources -w workpath --showMappings
```

##### Production Processing
```bash
# Full processing without debug
bin/console transformation -f transform.yaml -s sources -w workpath

# With additional sources
bin/console transformation -f transform.yaml -s sources -w workpath --addSource extra_sources
```

### 2. compare

Command for comparing two data files and identifying differences.

#### Syntax

```bash
bin/console compare [options]
```

#### Options

| Option | Short | Type | Required | Default | Description |
|--------|-------|------|----------|---------|-------------|
| `--master` | `-m` | string | Yes | - | Path to master/reference file |
| `--branch` | `-b` | string | Yes | - | Path to comparison file |
| `--reference` | `-r` | string | Yes | - | Field(s) to use as unique identifiers (comma-separated) |
| `--delimiter` | `-d` | string | No | `;` | CSV delimiter character |
| `--excluded` | `-e` | string | No | - | Fields to exclude from comparison (comma-separated) |

#### Examples

##### Basic File Comparison
```bash
bin/console compare \
  --master data/original.csv \
  --branch data/modified.csv \
  --reference id
```

##### Multiple Reference Fields
```bash
bin/console compare \
  --master products_old.csv \
  --branch products_new.csv \
  --reference sku,variant_id
```

##### Custom Delimiter and Exclusions
```bash
bin/console compare \
  --master file1.csv \
  --branch file2.csv \
  --reference code \
  --delimiter "," \
  --excluded "created_at,updated_at,temp_field"
```

#### Output Format

The compare command outputs a detailed report showing:
- **Added records**: Present in branch but not in master
- **Removed records**: Present in master but not in branch
- **Changed records**: Records with different values
- **Unchanged records**: Identical records

Example output:
```json
{
  "summary": {
    "total_master": 1000,
    "total_branch": 1050,
    "added": 75,
    "removed": 25,
    "changed": 100,
    "unchanged": 900
  },
  "items": {
    "ADDED": [...],
    "REMOVED": [...],
    "CHANGED": [...]
  }
}
```

## Wrapper Scripts

### run_example.sh

Convenient wrapper script for running transformations with automatic directory setup.

#### Syntax

```bash
PROJECT=project_name bin/docker/run_example.sh transformation_file.yaml [additional_options]
```

#### Features

- Automatically creates required directory structure
- Sets up proper paths for sources, workpath, and extensions
- Forwards additional arguments to the transformation command
- Simplifies project-based workflow management

#### Directory Structure Created

```
examples/${PROJECT}/
├── transformation/     # Transformation YAML files
├── sources/           # Input data files
├── workpath/          # Processing workspace
├── added_sources/     # Additional source files
└── extensions/        # Custom PHP extensions
```

#### Examples

##### Basic Project Transformation
```bash
PROJECT=customer_import bin/docker/run_example.sh main-STEPS.yaml
```

##### With Debug Mode
```bash
PROJECT=product_sync bin/docker/run_example.sh transform.yaml --debug
```

##### Limited Processing for Testing
```bash
PROJECT=data_migration bin/docker/run_example.sh process.yaml --try 50 --debug
```

## Advanced Usage Patterns

### 1. Automated Processing Scripts

Create shell scripts for repeated transformations:

```bash
#!/bin/bash
# process_daily_data.sh

PROJECT=daily_import
TRANSFORM_FILE=daily-process.yaml

# Set up environment
export DEBUG_MODE=false
export BATCH_SIZE=1000

# Run transformation
bin/docker/run_example.sh $TRANSFORM_FILE

# Check for errors
if [ $? -eq 0 ]; then
    echo "Processing completed successfully"
    # Move processed files to archive
    mv examples/$PROJECT/workpath/*.csv examples/$PROJECT/archive/
else
    echo "Processing failed"
    exit 1
fi
```

### 2. Development Workflow

```bash
#!/bin/bash
# dev_workflow.sh

PROJECT=development
TRANSFORM_FILE=test-transform.yaml

# Test with small dataset
echo "Testing with 10 records..."
PROJECT=$PROJECT bin/docker/run_example.sh $TRANSFORM_FILE --try 10 --debug

# If successful, test with larger dataset
if [ $? -eq 0 ]; then
    echo "Testing with 100 records..."
    PROJECT=$PROJECT bin/docker/run_example.sh $TRANSFORM_FILE --try 100
fi
```

### 3. Data Quality Validation

```bash
#!/bin/bash
# validate_data.sh

MASTER_FILE="data/reference.csv"
PROCESSED_FILE="examples/my_project/workpath/output.csv"

# Compare processed data with reference
bin/console compare \
    --master $MASTER_FILE \
    --branch $PROCESSED_FILE \
    --reference id \
    --excluded "processed_at,batch_id"

# Generate validation report
echo "Validation completed at $(date)" >> validation.log
```

## Environment Variables

### Supported Variables

| Variable | Description | Default | Example |
|----------|-------------|---------|---------|
| `PROJECT` | Project name for run_example.sh | - | `customer_data` |
| `DEBUG_MODE` | Enable debug output | `false` | `true` |
| `BATCH_SIZE` | Processing batch size | - | `500` |
| `MEMORY_LIMIT` | PHP memory limit | - | `512M` |

### Usage

```bash
# Set environment variables
export PROJECT=my_project
export DEBUG_MODE=true
export MEMORY_LIMIT=1G

# Run transformation
bin/docker/run_example.sh transform.yaml
```

## Error Handling and Exit Codes

### Exit Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 0 | Success | Command completed successfully |
| 1 | General Error | Unspecified error occurred |
| 2 | File Not Found | Required file could not be found |
| 3 | Permission Error | Insufficient permissions |
| 4 | Configuration Error | Invalid configuration or parameters |
| 5 | Processing Error | Error during data processing |

### Error Handling Examples

```bash
#!/bin/bash
# error_handling.sh

PROJECT=my_project bin/docker/run_example.sh transform.yaml

case $? in
    0)
        echo "Success: Transformation completed"
        ;;
    2)
        echo "Error: File not found - check file paths"
        ;;
    4)
        echo "Error: Configuration issue - check YAML syntax"
        ;;
    *)
        echo "Error: Unexpected error occurred"
        ;;
esac
```

## Performance Considerations

### Memory Management

```bash
# For large datasets, increase memory limit
php -d memory_limit=2G bin/console transformation -f transform.yaml -s sources -w workpath

# Use batch processing for very large files
bin/console transformation -f transform.yaml -s sources -w workpath --try 1000
```

### Parallel Processing

```bash
#!/bin/bash
# parallel_processing.sh

# Split large file into chunks
split -l 1000 large_file.csv chunk_

# Process chunks in parallel
for chunk in chunk_*; do
    PROJECT=chunk_$(basename $chunk) bin/docker/run_example.sh process_chunk.yaml &
done

# Wait for all processes to complete
wait

# Combine results
cat examples/chunk_*/workpath/output.csv > final_output.csv
```

## Troubleshooting

### Common Issues

#### Command Not Found
```bash
# Ensure console script is executable
chmod +x bin/console
chmod +x bin/docker/console
```

#### Permission Errors
```bash
# Fix directory permissions
chmod -R 755 examples/
chmod -R 755 bin/
```

#### Memory Issues
```bash
# Increase PHP memory limit
php -d memory_limit=1G bin/console transformation [options]
```

### Debug Information

```bash
# Get detailed debug information
bin/console transformation -f transform.yaml -s sources -w workpath --debug 2>&1 | tee debug.log

# Analyze debug output
grep -i "error\|warning" debug.log
grep -i "memory" debug.log
```

## Integration Examples

### CI/CD Pipeline

```yaml
# .github/workflows/data-processing.yml
name: Data Processing
on:
  schedule:
    - cron: '0 2 * * *'  # Daily at 2 AM

jobs:
  process-data:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          
      - name: Install dependencies
        run: composer install
        
      - name: Process daily data
        run: |
          PROJECT=daily_import bin/docker/run_example.sh daily-process.yaml
          
      - name: Upload results
        uses: actions/upload-artifact@v2
        with:
          name: processed-data
          path: examples/daily_import/workpath/
```

### Cron Job Setup

```bash
# Add to crontab
0 2 * * * cd /path/to/project && PROJECT=daily_import bin/docker/run_example.sh daily-process.yaml >> /var/log/data-processing.log 2>&1
```

## Related Topics

- [Transformation Workflow Guide](../user-guide/transformations.md) - Understanding transformation concepts
- [Debugging Guide](../user-guide/debugging.md) - Troubleshooting and optimization
- [Getting Started](../getting-started/quick-start.md) - First steps with the tool
- [Configuration](../getting-started/configuration.md) - Setting up your environment
- [Examples](../examples/) - Practical usage examples

## Quick Reference

### Most Common Commands

```bash
# Basic transformation
bin/console transformation -f transform.yaml -s sources -w workpath

# Debug mode with limited records
bin/console transformation -f transform.yaml -s sources -w workpath --debug --try 100

# File comparison
bin/console compare -m old.csv -b new.csv -r id

# Project-based processing
PROJECT=my_project bin/docker/run_example.sh transform.yaml
```

### Command Cheat Sheet

| Task | Command |
|------|---------|
| Run transformation | `bin/console transformation -f FILE -s SOURCE -w WORKPATH` |
| Debug transformation | `bin/console transformation -f FILE -s SOURCE -w WORKPATH --debug` |
| Test with sample data | `bin/console transformation -f FILE -s SOURCE -w WORKPATH --try 10` |
| Compare files | `bin/console compare -m FILE1 -b FILE2 -r REFERENCE` |
| Project workflow | `PROJECT=NAME bin/docker/run_example.sh FILE.yaml` |

---

*Last updated: 2024-12-19*
*Category: reference*
*Tags: cli, commands, automation, scripting*