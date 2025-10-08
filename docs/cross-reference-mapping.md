# Cross-Reference and Topic Relationship Mapping

This document defines the relationships between different documentation topics to ensure consistent cross-referencing across all documentation.

## Topic Categories and Relationships

### Getting Started
- **Installation** → Configuration, Quick Start, CLI Commands
- **Quick Start** → Configuration, Transformations, Actions, Examples
- **Configuration** → Context Directive, Environment Setup, Security

### User Guide
- **Transformations** → Pipelines, Actions, Debugging, Examples
- **Debugging** → Debug Action, CLI Commands, Performance, Error Handling
- **CLI Commands** → Configuration, Debugging, Examples

### Reference - Actions
- **Calculate** → Format, Copy, Statement, Number Functions
- **Debug** → Debugging Guide, CLI Commands, Statement
- **Format** → Calculate, Copy, String Functions, Data Types
- **Copy** → Format, Calculate, Statement, Field Operations
- **Statement** → Debug, Calculate, Format, Conditional Logic
- **All Actions** → Directives, Pipelines, Examples

### Reference - Directives
- **Context** → Configuration, Environment Variables, Pipelines
- **Mapping** → Key Mapping Action, Value Mapping, List Directive
- **Pipelines** → Actions, Transformations, Data Flow
- **Aliases** → Configuration, File Paths, Context

### Reference - Tools & Converters
- **Converters** → Pipelines, Data Formats, Integration
- **Tools** → CLI Commands, Data Processing, Utilities

### Developer Guide
- **Architecture** → Extending, Contributing, System Design
- **Extending** → Architecture, Contributing, Custom Actions
- **Contributing** → Architecture, Extending, Development Setup

### Examples
- **Basic Examples** → Getting Started, Actions, Simple Transformations
- **Advanced Examples** → Complex Workflows, Integration, Performance

## Cross-Reference Patterns

### Standard Related Topics Structure
```markdown
## Related Topics

### [Category Name]
- **[Actions Reference](./reference/actions/)** - Brief description of relationship
- **[Directives Reference](./reference/directives/)** - Brief description of relationship

### [Another Category]
- **[User Guide](./user-guide/)** - Brief description of relationship

## See Also
- [Getting Started](./getting-started/)
- [Implementation Examples](../examples/)
- [Reference Documentation](./reference/)
```

### Navigation Patterns
- Previous/Next within same category
- Up to parent section
- Cross-category relationships
- Examples and practical applications

## Implementation Guidelines

1. **Bidirectional References**: If A references B, B should reference A
2. **Contextual Relevance**: Only include truly relevant cross-references
3. **Consistent Descriptions**: Use consistent language for relationship descriptions
4. **Hierarchical Organization**: Group related topics by category
5. **Progressive Disclosure**: Link from basic to advanced topics