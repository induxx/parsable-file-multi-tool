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
```

For detailed installation and setup instructions, see the [Getting Started Guide](docs/getting-started/).

## Documentation

### 📚 Getting Started
- [Installation Guide](docs/getting-started/) - Step-by-step setup instructions
- [Quick Start Tutorial](docs/getting-started/) - Your first transformation
- [Configuration Guide](docs/getting-started/) - Setting up accounts and contexts

### 👥 User Guide  
- [Running Transformations](docs/running_transformations.md) - Complete workflow guide
- [Debugging & Troubleshooting](docs/user-guide/) - Solve common issues
- [CLI Commands](docs/user-guide/) - Command-line reference

### 📖 Reference Documentation
- [Actions](docs/actions/) - All available transformation actions
- [Directives](docs/directives/) - Configuration directives and options
- [Converters](docs/converters/) - Data format converters
- [Tools](docs/tools/) - Utility tools and helpers

### 🔧 Developer Guide
- [Architecture Overview](docs/developer-guide/) - System design and components
- [Creating Extensions](docs/developer-guide/) - Build custom actions
- [Contributing](docs/developer-guide/) - Development guidelines

### 💡 Examples
- [Basic Transformations](docs/examples/) - Common use cases
- [Advanced Workflows](docs/examples/) - Complex transformation patterns

## Configuration

### Account Setup

Add API credentials and account information to your transformation files:

```yaml
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
```

### Debugging Options
```bash
# Debug first item
bin/docker/console transformation --file config.yaml --source input --workpath output --debug

# Test first 100 items
bin/docker/console transformation --file config.yaml --source input --workpath output --try 100

# Show dynamic mappings
bin/docker/console transformation --file config.yaml --source input --workpath output --showMappings

# Process specific line
bin/docker/console transformation --file config.yaml --source input --workpath output --line 100
```

### Help and Options
```bash
bin/docker/console transformation --help
```

## Project Structure

```
├── docs/                    # Documentation
│   ├── getting-started/     # Installation and setup guides
│   ├── user-guide/         # User documentation
│   ├── reference/          # API and component reference
│   ├── developer-guide/    # Technical documentation
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