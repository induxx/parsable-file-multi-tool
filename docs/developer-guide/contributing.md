# Contributing Guidelines

## Overview

Thank you for your interest in contributing to the parsable-file-multi-tool! This document provides comprehensive guidelines for contributing to the project, including development setup, coding standards, testing requirements, and the review process.

## Prerequisites

Before contributing, ensure you have:

- PHP 8.1 or higher
- Composer for dependency management
- Git for version control
- Docker (optional, for containerized development)
- Basic understanding of the [system architecture](architecture.md)

## Development Setup

### 1. Fork and Clone the Repository

```bash
# Fork the repository on GitHub, then clone your fork
git clone https://github.com/YOUR_USERNAME/parsable-file-multi-tool.git
cd parsable-file-multi-tool

# Add the upstream repository
git remote add upstream https://github.com/induxx/parsable-file-multi-tool.git
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Verify installation
composer validate
```

### 3. Set Up Development Environment

#### Option A: Local Development

Ensure you have PHP 8.1+ with required extensions:
- ext-json
- ext-iconv
- ext-xmlwriter
- ext-xmlreader
- ext-curl
- ext-zip
- ext-simplexml

#### Option B: Docker Development

```bash
# Build and start the development environment
docker-compose up -d

# Run commands inside the container
docker-compose exec app bash
```

### 4. Verify Setup

```bash
# Run the test suite
composer test

# Run static analysis
composer sa-test

# Run unit tests only
composer unit-test
```

## Development Workflow

### 1. Create a Feature Branch

```bash
# Update your main branch
git checkout main
git pull upstream main

# Create a new feature branch
git checkout -b feature/your-feature-name
```

### 2. Make Your Changes

Follow the coding standards and best practices outlined below.

### 3. Test Your Changes

```bash
# Run all tests
composer test

# Run specific test groups
composer unit-test
composer sa-test

# Run tests for GitHub CI (excludes performance tests)
composer github-test
```

### 4. Commit Your Changes

```bash
# Stage your changes
git add .

# Commit with a descriptive message
git commit -m "Add feature: brief description of changes"
```

### 5. Push and Create Pull Request

```bash
# Push to your fork
git push origin feature/your-feature-name

# Create a pull request on GitHub
```

## Coding Standards

### PHP Standards

We follow PSR-12 coding standards with additional project-specific conventions:

#### Class Structure

```php
<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

/**
 * Brief description of the class
 * 
 * Detailed description if needed, including:
 * - Purpose and functionality
 * - Usage examples
 * - Configuration options
 * 
 * @package Misery\Component\Action
 */
class ExampleAction implements ActionInterface, OptionsInterface
{
    use OptionsTrait;
    
    public const NAME = 'example_action';
    
    private array $errors = [];
    
    public function execute(array $item, array $config): array
    {
        $this->setOptions($config);
        
        // Implementation here
        
        return $item;
    }
    
    private function processItem(array $item): array
    {
        // Private method implementation
        return $item;
    }
}
```

#### Naming Conventions

- **Classes**: PascalCase (e.g., `CustomTransformAction`)
- **Methods**: camelCase (e.g., `executeTransformation`)
- **Properties**: camelCase (e.g., `$configOptions`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `DEFAULT_TIMEOUT`)
- **Namespaces**: Follow PSR-4 structure

#### Documentation

All public methods and classes must have PHPDoc comments:

```php
/**
 * Transforms product data according to configuration
 * 
 * @param array $item The data item to transform
 * @param array $config Configuration options
 * @return array The transformed item
 * @throws \InvalidArgumentException When required config is missing
 */
public function execute(array $item, array $config): array
{
    // Implementation
}
```

### Code Quality

#### Error Handling

Always implement proper error handling:

```php
public function execute(array $item, array $config): array
{
    try {
        $this->validateConfig($config);
        return $this->processItem($item, $config);
    } catch (\Exception $e) {
        // Log error for debugging
        error_log("Action error: " . $e->getMessage());
        
        // Add error to item for tracking
        $item['_errors'][] = $e->getMessage();
        return $item;
    }
}
```

#### Input Validation

Validate all inputs and configuration:

```php
private function validateConfig(array $config): void
{
    $required = ['api_url', 'timeout'];
    
    foreach ($required as $field) {
        if (!isset($config[$field])) {
            throw new \InvalidArgumentException("Missing required config: {$field}");
        }
    }
    
    if (!is_numeric($config['timeout']) || $config['timeout'] <= 0) {
        throw new \InvalidArgumentException("Timeout must be a positive number");
    }
}
```

#### Memory Efficiency

Use generators for large datasets:

```php
public function read(array $config): iterable
{
    $handle = fopen($config['filename'], 'r');
    
    try {
        while (($line = fgets($handle)) !== false) {
            yield json_decode($line, true);
        }
    } finally {
        fclose($handle);
    }
}
```

## Testing Requirements

### Test Structure

All contributions must include appropriate tests:

```
tests/
├── Component/
│   ├── Action/
│   │   └── YourActionTest.php
│   ├── Reader/
│   │   └── YourReaderTest.php
│   └── ...
└── Integration/
    └── YourFeatureIntegrationTest.php
```

### Unit Tests

Write comprehensive unit tests for all new functionality:

```php
<?php

namespace Tests\Misery\Component\Action;

use PHPUnit\Framework\TestCase;
use Misery\Component\Action\YourAction;

class YourActionTest extends TestCase
{
    private YourAction $action;
    
    protected function setUp(): void
    {
        $this->action = new YourAction();
    }
    
    public function testExecuteWithValidInput(): void
    {
        $item = ['name' => 'Test Product'];
        $config = ['enabled' => true];
        
        $result = $this->action->execute($item, $config);
        
        $this->assertArrayHasKey('processed', $result);
        $this->assertTrue($result['processed']);
    }
    
    public function testExecuteWithInvalidConfig(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required config');
        
        $item = ['name' => 'Test'];
        $config = []; // Missing required config
        
        $this->action->execute($item, $config);
    }
    
    /**
     * @dataProvider validInputProvider
     */
    public function testExecuteWithVariousInputs($input, $expected): void
    {
        $result = $this->action->execute($input, []);
        $this->assertEquals($expected, $result['output']);
    }
    
    public function validInputProvider(): array
    {
        return [
            'basic input' => [['value' => 10], 20],
            'string input' => [['value' => '10'], 20],
            'zero input' => [['value' => 0], 0],
        ];
    }
}
```

### Integration Tests

Create integration tests for complex functionality:

```php
<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Misery\Component\Pipeline\Pipeline;

class YourFeatureIntegrationTest extends TestCase
{
    public function testCompleteTransformationPipeline(): void
    {
        $config = [
            'input' => [
                'reader' => 'array',
                'data' => [
                    ['name' => 'Product 1', 'price' => 100],
                    ['name' => 'Product 2', 'price' => 200]
                ]
            ],
            'actions' => [
                ['action' => 'your_action', 'multiplier' => 1.5]
            ],
            'output' => [
                'writer' => 'array'
            ]
        ];
        
        $pipeline = new Pipeline();
        $results = $pipeline->process($config);
        
        $this->assertCount(2, $results);
        $this->assertEquals(150, $results[0]['price']);
        $this->assertEquals(300, $results[1]['price']);
    }
}
```

### Test Coverage

Maintain high test coverage:

```bash
# Generate coverage report
vendor/bin/phpunit --coverage-html coverage/

# View coverage in browser
open coverage/index.html
```

## Static Analysis

We use PHPStan for static analysis. Ensure your code passes level 5 analysis:

```bash
# Run static analysis
vendor/bin/phpstan analyse --no-progress --ansi -l 5 src
vendor/bin/phpstan analyse --no-progress --ansi -l 5 tests

# Or use composer script
composer sa-test
```

### Common PHPStan Issues

Fix common static analysis issues:

```php
// Bad: Undefined array key
$value = $item['key'];

// Good: Check if key exists
$value = $item['key'] ?? null;
if (isset($item['key'])) {
    $value = $item['key'];
}

// Bad: Mixed return types
public function getValue($item)
{
    return $item['value'] ?? false;
}

// Good: Specific return types
public function getValue(array $item): ?string
{
    return $item['value'] ?? null;
}
```

## Documentation Standards

### Code Documentation

Document all public APIs:

```php
/**
 * Processes customer data for CRM integration
 * 
 * This action transforms customer data by:
 * - Normalizing phone numbers to international format
 * - Validating email addresses
 * - Calculating customer lifetime value
 * 
 * Configuration options:
 * - phone_format: International phone format (default: E164)
 * - validate_email: Enable email validation (default: true)
 * - currency: Currency for value calculations (default: USD)
 * 
 * @param array $item Customer data item
 * @param array $config Action configuration
 * @return array Processed customer data
 * @throws \InvalidArgumentException When required fields are missing
 * 
 * @example
 * ```yaml
 * actions:
 *   - action: customer_processor
 *     phone_format: E164
 *     validate_email: true
 *     currency: EUR
 * ```
 */
public function execute(array $item, array $config): array
{
    // Implementation
}
```

### README Updates

Update relevant documentation when adding features:

- Update main README.md if adding new capabilities
- Add examples to docs/examples/ for new features
- Update reference documentation for new actions/components

## Pull Request Process

### Before Submitting

1. **Run all tests**: `composer test`
2. **Check static analysis**: `composer sa-test`
3. **Update documentation**: Add or update relevant docs
4. **Add tests**: Ensure new code has appropriate test coverage
5. **Rebase on main**: Keep your branch up to date

### Pull Request Template

Use this template for your pull request description:

```markdown
## Description
Brief description of the changes and their purpose.

## Type of Change
- [ ] Bug fix (non-breaking change that fixes an issue)
- [ ] New feature (non-breaking change that adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## Changes Made
- List specific changes made
- Include any new files or modified files
- Mention any configuration changes

## Testing
- [ ] Unit tests added/updated
- [ ] Integration tests added/updated
- [ ] All tests pass locally
- [ ] Static analysis passes

## Documentation
- [ ] Code is documented
- [ ] README updated (if applicable)
- [ ] Examples added (if applicable)

## Checklist
- [ ] Code follows project coding standards
- [ ] Self-review completed
- [ ] No merge conflicts
- [ ] Commit messages are clear and descriptive
```

### Review Process

1. **Automated Checks**: CI will run tests and static analysis
2. **Code Review**: Maintainers will review your code
3. **Feedback**: Address any requested changes
4. **Approval**: Once approved, your PR will be merged

### Review Criteria

Reviewers will check for:

- **Functionality**: Does the code work as intended?
- **Code Quality**: Is the code clean, readable, and maintainable?
- **Testing**: Are there adequate tests with good coverage?
- **Documentation**: Is the code properly documented?
- **Standards**: Does the code follow project conventions?
- **Performance**: Are there any performance concerns?
- **Security**: Are there any security implications?

## Release Process

### Versioning

We follow Semantic Versioning (SemVer):

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Changelog

Update CHANGELOG.md for significant changes:

```markdown
## [1.2.0] - 2024-01-15

### Added
- New customer processing action
- Support for international phone number formatting
- Email validation capabilities

### Changed
- Improved error handling in transformation pipeline
- Updated documentation structure

### Fixed
- Memory leak in large file processing
- Configuration validation edge cases

### Deprecated
- Old phone formatting method (use new customer_processor action)
```

## Community Guidelines

### Code of Conduct

- Be respectful and inclusive
- Provide constructive feedback
- Help others learn and grow
- Focus on the code, not the person

### Communication

- **Issues**: Use GitHub issues for bug reports and feature requests
- **Discussions**: Use GitHub discussions for questions and ideas
- **Pull Requests**: Use PR comments for code-specific discussions

### Getting Help

- Check existing documentation first
- Search existing issues and discussions
- Provide minimal reproducible examples
- Be specific about your environment and use case

## Common Contribution Types

### Bug Fixes

1. Create an issue describing the bug
2. Write a failing test that reproduces the bug
3. Fix the bug
4. Ensure the test passes
5. Submit a pull request

### New Features

1. Discuss the feature in an issue first
2. Get feedback from maintainers
3. Implement the feature with tests
4. Update documentation
5. Submit a pull request

### Documentation Improvements

1. Identify areas needing better documentation
2. Write clear, comprehensive documentation
3. Include examples where helpful
4. Test any code examples
5. Submit a pull request

### Performance Improvements

1. Identify performance bottlenecks
2. Write benchmarks to measure improvement
3. Implement optimizations
4. Verify improvements with benchmarks
5. Submit a pull request with performance data

## Troubleshooting

### Common Development Issues

**Composer Install Fails**
```bash
# Clear composer cache
composer clear-cache

# Install with platform requirements ignored
composer install --ignore-platform-reqs
```

**Tests Fail Locally**
```bash
# Update dependencies
composer update

# Clear any caches
rm -rf var/cache/*

# Run tests with verbose output
vendor/bin/phpunit --verbose
```

**Static Analysis Errors**
```bash
# Run PHPStan with more details
vendor/bin/phpstan analyse --no-progress -l 5 src --error-format=table

# Check specific files
vendor/bin/phpstan analyse src/Component/Action/YourAction.php
```

**Docker Issues**
```bash
# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Check container logs
docker-compose logs app
```

### Getting Support

If you encounter issues:

1. Check the [troubleshooting guide](../user-guide/troubleshooting.md)
2. Search existing GitHub issues
3. Create a new issue with:
   - Clear description of the problem
   - Steps to reproduce
   - Expected vs actual behavior
   - Environment details (PHP version, OS, etc.)
   - Relevant code snippets or configuration

## Related Topics

### Core Development Resources
- **[Architecture Overview](./architecture.md)** - Understanding the system structure, components, and design patterns
- **[Extension Development Guide](./extending.md)** - Creating custom components, actions, and system extensions
- **[Development Setup](./contributing.md#development-setup)** - Environment configuration and tooling setup
- **[Code Quality Standards](./contributing.md#coding-standards)** - Coding conventions and best practices

### User and Reference Documentation
- **[User Guide](../user-guide/)** - Using the tool effectively and understanding workflows
- **[API Reference](../reference/)** - Complete API documentation and technical specifications
- **[Actions Reference](../reference/actions/)** - All available actions and their implementation details
- **[Directives Reference](../reference/directives/)** - Configuration directives and system options

### Testing and Quality Assurance
- **[Testing Requirements](./contributing.md#testing-requirements)** - Unit testing, integration testing, and quality standards
- **[Debugging Guide](../user-guide/debugging.md)** - Debug development workflows and troubleshoot issues
- **[Performance Testing](./contributing.md#performance-considerations)** - Performance testing and optimization guidelines
- **[Static Analysis](./contributing.md#static-analysis)** - Code quality analysis and automated checks

### Configuration and Setup
- **[Configuration Guide](../getting-started/configuration.md)** - Environment setup and configuration management
- **[Installation Guide](../getting-started/installation.md)** - Development environment installation and setup
- **[CLI Commands](../reference/cli-commands.md)** - Command-line tools for development and testing
- **[Context Directive](../directives/context.md)** - Environment variables and development configuration

### Development Workflows
- **[Transformation Examples](../examples/)** - Practical examples for testing and development
- **[Pipeline Development](../directives/pipelines.md)** - Develop and test transformation pipelines
- **[Action Development](./extending.md#custom-actions)** - Create custom actions and transformations
- **[Extension Patterns](./extending.md#extension-patterns)** - Common extension development patterns

### Community and Collaboration
- **[Pull Request Process](./contributing.md#pull-request-process)** - Code review and collaboration workflows
- **[Issue Reporting](./contributing.md#issue-reporting)** - Bug reports and feature requests
- **[Documentation Standards](./contributing.md#documentation-standards)** - Documentation contribution guidelines
- **[Community Guidelines](./contributing.md#community-guidelines)** - Code of conduct and communication standards

### Advanced Development Topics
- **[Custom Converters](../converters/)** - Develop custom data format converters
- **[Tool Integration](../tools/)** - Integrate external tools and utilities
- **[Performance Optimization](../user-guide/debugging.md#performance-optimization-guidelines)** - Optimize code performance and resource usage
- **[Security Considerations](./contributing.md#security-guidelines)** - Security best practices and vulnerability prevention

## See Also

- **[Getting Started](../getting-started/)** - Quick setup for new contributors and developers
- **[Transformation Examples](../examples/)** - Practical examples and development patterns
- **[System Architecture](./architecture.md)** - Deep dive into system design and component relationships
- **[Extension Development](./extending.md)** - Advanced customization and extension development

## Acknowledgments

Thank you to all contributors who help make this project better! Your contributions, whether code, documentation, bug reports, or feature suggestions, are greatly appreciated.

---

*This document is a living guide that evolves with the project. If you have suggestions for improvements, please submit a pull request or create an issue.*