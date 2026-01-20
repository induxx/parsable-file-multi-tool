# Parsable File Multi-Tool

A powerful PHP-based data transformation and processing tool designed for handling complex data pipelines, file format conversions, and integration workflows. This tool provides a flexible framework for transforming data between various formats with support for custom actions, directives, and extensible processing pipelines.

## Key Features

- **Multi-format Support**: Process CSV, XML, JSON, and other structured data formats
- **Flexible Transformations**: Chain multiple transformation steps with configurable actions
- **Extensible Architecture**: Create custom actions and extensions for specific use cases
- **Integration Ready**: Built-in support for Akeneo PIM and other e-commerce platforms
- **Debugging Tools**: Comprehensive debugging and validation capabilities
- **Docker Support**: Containerized environment for consistent deployments

## Quick Start

### Installation

```bash
bin/docker/composer install
```

### Basic Usage

Transform a file using a transformation configuration:

```bash
bin/docker/console transformation --file examples/transformation.yaml --source data/input --workpath data/output
PROJECT=project_name bin/docker/run_example.sh transformation.yaml
```

For detailed installation and setup instructions, see the [Quick Start Guide](docs/getting-started/quick-start.md).

## Documentation

### 📚 Getting Started
- [Quick Start Guide](docs/getting-started/quick-start.md) - Your first transformation
- [Configuration Guide](docs/getting-started/configuration.md) - Setting up accounts and contexts

### 👥 User Guide  
- [Running Transformations](docs/examples/running_transformations.md) - Complete workflow guide
- [Debug Action](docs/reference/actions/debug_action.md) - Debugging utilities
- [CLI Commands](docs/reference/cli-commands.md) - Command-line reference

### 📖 Reference Documentation
- [Actions](docs/reference/actions/index.md) - All available transformation actions
- [Directives](docs/reference/directives/index.md) - Configuration directives and options
- [Converters](docs/reference/converters/index.md) - Data format converters
- [Tools](docs/reference/tools/index.md) - Utility tools and helpers

### 💡 Examples
- [Basic Transformations](docs/examples/basic-transformation.md) - Common use cases
- [Advanced Workflows](docs/examples/running_transformations.md) - Complex transformation patterns

## Configuration

### Account Setup

Add API credentials and account information to your transformation files:

```yaml
# secrets.yaml
account:
   name: "my-account"
   username: "my-username"
   password: "my-password"
   domain: "my-domain"
   client_id: "my-client-id"
   client_secret: "my-client-secret"
```

### Context Parameters

Define reusable parameters across transformations:

```yaml
context:
    my-parameter: "my-value"
    environment: "production"
```

## Common Commands

### Basic Transformation
```bash
bin/docker/console transformation --file path/to/config.yaml --source input/dir --workpath output/dir
PROJECT=project_name bin/docker/run_example.sh config.yaml
```

### Debugging Options
```bash
# Debug first item
bin/docker/console transformation --file config.yaml --source input --workpath output --debug
PROJECT=project_name bin/docker/run_example.sh config.yaml --debug

# Test first 100 items
bin/docker/console transformation --file config.yaml --source input --workpath output --try 100
PROJECT=project_name bin/docker/run_example.sh config.yaml --try 100

# Show dynamic mappings
bin/docker/console transformation --file config.yaml --source input --workpath output --showMappings
PROJECT=project_name bin/docker/run_example.sh config.yaml --showMappings

# Process specific line
bin/docker/console transformation --file config.yaml --source input --workpath output --line 100
PROJECT=project_name bin/docker/run_example.sh config.yaml --line 100
```

### Help and Options
```bash
bin/docker/console transformation --help
PROJECT=project_name bin/docker/run_example.sh config.yaml --help
```

## Project Structure

```
├── docs/                    # Documentation
│   ├── getting-started/     # Installation and setup guides
│   ├── reference/          # API and component reference
│   └── examples/           # Tutorials and examples
├── src/                    # Source code
├── examples/               # Sample projects and data
├── config/                 # Configuration templates
└── tests/                  # Test suites
```

## Support

- **Documentation**: [Complete documentation](docs/index.md)
- **Examples**: Browse the [examples directory](examples/) for real-world use cases
- **Issues**: Report bugs and request features via the project issue tracker

## License

This project is licensed under the terms specified in the project license file.
