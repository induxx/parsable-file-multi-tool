# Running Transformations

This guide covers how to run transformations using the parsable-file-multi-tool, including configuration, execution, and troubleshooting.

## Overview

Transformations are the core functionality of the parsable-file-multi-tool. They allow you to process, transform, and convert data between different formats and systems.

## Prerequisites

- Tool installed and configured
- Source data files available
- Transformation configuration file prepared

## Basic Transformation Workflow

### 1. Prepare Your Data

Ensure your source data is in a supported format:
- CSV files
- JSON files
- XML files
- YAML files
- API endpoints

### 2. Create Transformation Configuration

Create a YAML configuration file defining your transformation:

```yaml
# transformation.yaml
sources:
  - type: csv
    path: input/data.csv

transformations:
  - actions:
      - action: rename
        from: old_field
        to: new_field
      - action: format
        field: price
        format: currency

targets:
  - type: json
    path: output/transformed.json
```

### 3. Run the Transformation

Execute the transformation using the CLI:

```bash
php bin/transformation transformation.yaml
```

## Advanced Configuration

### API Integration

Configure API data sources and targets:

```yaml
sources:
  - type: api
    url: https://api.example.com/data
    headers:
      Authorization: Bearer ${API_TOKEN}
    
targets:
  - type: api
    url: https://api.target.com/import
    method: POST
```

### Batch Processing

Process large datasets in batches:

```yaml
processing:
  batch_size: 1000
  memory_limit: 512M
  
sources:
  - type: csv
    path: large_dataset.csv
    batch_processing: true
```

### Streaming Processing

Handle real-time data streams:

```yaml
sources:
  - type: stream
    endpoint: tcp://localhost:9999
    
processing:
  mode: streaming
  buffer_size: 100
```

## Data Flow Patterns

### Sequential Processing
Process data in a linear sequence through multiple transformation steps.

### Parallel Processing
Process multiple data streams simultaneously for improved performance.

### Conditional Processing
Apply different transformations based on data conditions.

## Performance Optimization

### Memory Management

Optimize memory usage for large datasets:

```yaml
processing:
  memory_limit: 1G
  gc_probability: 1
  gc_divisor: 100
```

### Caching

Enable caching for frequently accessed data:

```yaml
caching:
  enabled: true
  driver: redis
  ttl: 3600
```

## Error Handling

### Validation

Validate data before processing:

```yaml
validation:
  strict_mode: true
  required_fields:
    - id
    - name
  
  rules:
    email:
      type: email
    age:
      type: integer
      min: 0
      max: 150
```

### Error Recovery

Configure error handling strategies:

```yaml
error_handling:
  strategy: continue
  log_errors: true
  max_errors: 100
  
  on_error:
    - action: log
      level: error
    - action: skip_record
```

## Security

### Credential Management

Securely manage API credentials and sensitive data:

```yaml
context:
  secrets:
    api_key: ${API_KEY}
    database_password: ${DB_PASSWORD}
```

### Data Sanitization

Sanitize sensitive data during transformation:

```yaml
transformations:
  - actions:
      - action: remove
        fields:
          - ssn
          - credit_card
      - action: hash
        field: email
        algorithm: sha256
```

## Troubleshooting

### Common Issues

#### File Not Found
- Verify file paths are correct
- Check file permissions
- Ensure files exist before transformation

#### Memory Errors
- Reduce batch size
- Increase memory limit
- Use streaming processing for large files

#### API Connection Issues
- Verify API endpoints are accessible
- Check authentication credentials
- Review rate limiting settings

### Debugging

Enable debug mode for detailed logging:

```bash
php bin/transformation transformation.yaml --debug
```

Use the debug action to inspect data:

```yaml
transformations:
  - actions:
      - action: debug
        message: "Data at this point"
        fields: [id, name, status]
```

## Related Topics

- [Configuration Guide](../getting-started/configuration.md)
- [Actions Reference](../reference/actions/)
- [Debugging Guide](./debugging.md)
- [Examples](../examples/)