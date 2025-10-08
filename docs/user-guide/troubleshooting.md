# Troubleshooting Guide

This guide helps you diagnose and resolve common issues when using the parsable-file-multi-tool.

## Common Issues

### Installation Problems

#### Dependencies Not Found
**Problem**: Missing PHP extensions or dependencies
**Solution**: 
```bash
# Install required PHP extensions
sudo apt-get install php-xml php-json php-mbstring

# Update Composer dependencies
composer install --no-dev
```

#### Permission Errors
**Problem**: File permission issues
**Solution**:
```bash
# Fix file permissions
chmod +x bin/transformation
chmod -R 755 var/
```

### Configuration Issues

#### Invalid YAML Syntax
**Problem**: YAML parsing errors
**Solution**: Validate your YAML syntax using online validators or:
```bash
php -r "yaml_parse_file('transformation.yaml');"
```

#### Missing Environment Variables
**Problem**: Undefined environment variables
**Solution**: Check your .env file and ensure all required variables are set:
```bash
# Check environment variables
env | grep API_
```

### Runtime Errors

#### Memory Limit Exceeded
**Problem**: PHP memory limit exceeded
**Solution**:
```bash
# Increase memory limit
php -d memory_limit=1G bin/transformation config.yaml
```

#### File Not Found
**Problem**: Source files not accessible
**Solution**: Verify file paths and permissions:
```bash
# Check file exists and is readable
ls -la path/to/file.csv
```

### Performance Issues

#### Slow Processing
**Problem**: Transformations taking too long
**Solutions**:
- Enable batch processing
- Increase batch size
- Use streaming for large files
- Enable caching

#### High Memory Usage
**Problem**: Excessive memory consumption
**Solutions**:
- Reduce batch size
- Enable streaming mode
- Use memory-efficient processing

## Debugging Techniques

### Enable Debug Mode
```bash
php bin/transformation config.yaml --debug
```

### Use Debug Actions
```yaml
transformations:
  - actions:
      - action: debug
        message: "Checking data structure"
        dump_data: true
```

### Check Logs
```bash
# View transformation logs
tail -f var/logs/transformation.log
```

## Getting Help

### Log Analysis
When reporting issues, include:
- Full error messages
- Configuration files (sanitized)
- Log files
- System information

### Community Support
- Check existing documentation
- Search for similar issues
- Provide minimal reproduction examples

## Related Topics

- [Debugging Guide](./debugging.md)
- [Configuration Guide](../getting-started/configuration.md)
- [Performance Optimization](../examples/performance-optimization.md)