# Developer Guide

Welcome to the developer guide for the parsable-file-multi-tool. This section provides technical documentation for developers who want to understand the architecture, extend functionality, or contribute to the project.

## Contents

### Architecture & Design
- [Architecture Overview](./architecture.md) - System architecture and component relationships
- [Extending the Tool](./extending.md) - Guide for creating custom actions and extensions
- [Contributing](./contributing.md) - Development setup and contribution guidelines

## Getting Started with Development

### Prerequisites
- PHP 8.0 or higher
- Composer for dependency management
- Docker (optional, for containerized development)

### Development Setup
1. Clone the repository
2. Install dependencies with `composer install`
3. Set up your development environment
4. Run tests to verify setup

### Key Concepts
- **Actions**: Individual transformation operations
- **Directives**: Configuration and control structures
- **Converters**: Format transformation components
- **Pipeline**: Sequential processing workflow

## Extension Points
The tool provides several extension points for customization:
- Custom actions for specific transformations
- Custom converters for new data formats
- Custom validators for data quality checks
- Custom writers for output formats

## Related Topics
- [Getting Started](../getting-started/)
- [User Guide](../user-guide/)
- [Reference Documentation](../reference/)