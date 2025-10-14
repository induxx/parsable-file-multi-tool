# Integration Patterns

This guide covers common integration patterns for connecting the parsable-file-multi-tool with external systems and APIs.

## Overview

Integration patterns help you connect your data transformation workflows with various external systems, APIs, and data sources.

## API Integration Patterns

### REST API Integration

```yaml
sources:
  - type: api
    url: https://api.example.com/data
    method: GET
    headers:
      Authorization: Bearer ${API_TOKEN}
      Content-Type: application/json

targets:
  - type: api
    url: https://api.target.com/import
    method: POST
    headers:
      Authorization: Bearer ${TARGET_API_TOKEN}
```

### Pagination Handling

```yaml
sources:
  - type: api
    url: https://api.example.com/data
    pagination:
      type: offset
      limit: 100
      offset_param: offset
      limit_param: limit
```

## Database Integration

### Direct Database Connection

```yaml
sources:
  - type: database
    connection:
      driver: mysql
      host: localhost
      database: source_db
      username: ${DB_USER}
      password: ${DB_PASSWORD}
    query: "SELECT * FROM products WHERE updated_at > ?"
    parameters:
      - ${LAST_UPDATE}
```

## File System Integration

### Network File Systems

```yaml
sources:
  - type: file
    path: /mnt/network/data/*.csv
    pattern: "*.csv"
    recursive: true
```

### Cloud Storage

```yaml
sources:
  - type: s3
    bucket: my-data-bucket
    prefix: exports/
    credentials:
      access_key: ${AWS_ACCESS_KEY}
      secret_key: ${AWS_SECRET_KEY}
```

## Related Topics

- [Configuration Guide](../getting-started/configuration.md)
- [API Integration](../user-guide/transformations.md#api-integration)
- [Security Best Practices](../getting-started/configuration.md#security-best-practices)