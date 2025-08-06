# User Guide

---
**Navigation:** [🏠 Home](../index.md) | [📚 Getting Started](../getting-started/) | [👥 User Guide](./) | [📖 Reference](../reference/) | [🔧 Developer Guide](../developer-guide/) | [💡 Examples](../examples/)

**📍 You are here:** [Home](../index.md) > User Guide
---

The User Guide provides comprehensive documentation for day-to-day usage of the parsable-file-multi-tool. Whether you're running transformations, debugging issues, or optimizing performance, this section covers the essential workflows and techniques.

## Section Contents

### [🔄 Running Transformations](./transformations.md)
Complete guide to transformation workflows, pipeline concepts, and best practices.

**What you'll learn:**
- Understanding transformation pipelines
- Data flow and processing concepts
- Common transformation patterns
- Workflow optimization techniques

### [🐛 Debugging and Troubleshooting](./debugging.md)
Comprehensive debugging guide with error scenarios, performance optimization, and troubleshooting strategies.

**What you'll learn:**
- Debugging tools and techniques
- Common error scenarios and solutions
- Performance optimization guidelines
- Systematic debugging approaches

### [💻 CLI Commands](../reference/cli-commands.md)
Complete command-line reference with examples and usage patterns.

**What you'll learn:**
- All available command options
- Usage patterns for different scenarios
- Script examples and automation guidance
- Command-line best practices

## Quick Reference

### Common Tasks
- **Run a transformation:** `bin/console transformation -f transform.yaml -s sources -w workpath`
- **Debug with limited data:** `bin/console transformation -f transform.yaml -s sources -w workpath --debug --try 10`
- **Show mappings:** `bin/console transformation -f transform.yaml -s sources -w workpath --showMappings`

### Troubleshooting Quick Links
- [File and Path Issues](./debugging.md#file-and-path-issues)
- [Data Format Issues](./debugging.md#data-format-issues)
- [Memory and Performance Issues](./debugging.md#memory-and-performance-issues)
- [API and External Service Issues](./debugging.md#api-and-external-service-issues)

## User Workflows

### For New Users
1. Complete [Getting Started](../getting-started/) guides
2. Read [Running Transformations](./transformations.md) for workflow understanding
3. Practice with [Examples](../examples/)
4. Reference [Debugging Guide](./debugging.md) when issues arise

### For Regular Users
1. Use [CLI Commands](../reference/cli-commands.md) for daily operations
2. Reference [Actions](../reference/actions/) and [Directives](../reference/directives/) as needed
3. Apply [Debugging Techniques](./debugging.md) for troubleshooting
4. Optimize with [Performance Guidelines](./debugging.md#performance-optimization-guidelines)

### For Advanced Users
1. Explore [Developer Guide](../developer-guide/) for extensions
2. Study [Architecture](../developer-guide/architecture.md) for deep understanding
3. Contribute using [Contributing Guidelines](../developer-guide/contributing.md)

## Related Sections

- **[📖 Reference](../reference/)** - Complete technical reference for all components
- **[🔧 Developer Guide](../developer-guide/)** - Architecture and extension development
- **[💡 Examples](../examples/)** - Practical examples and tutorials

---

## Quick Navigation

- **🏠 [Documentation Home](../index.md)** - Main documentation index
- **🔍 [Search Tips](../index.md#search-tips)** - How to find information quickly
- **❓ [Getting Help](./debugging.md#getting-help)** - Support and troubleshooting resources

### User Guide Topics
- **[Running Transformations](./transformations.md)** - Complete workflow guide and best practices
- **[Debugging & Troubleshooting](./debugging.md)** - Solve common issues and optimize performance
- **[CLI Commands](../reference/cli-commands.md)** - Complete command-line reference

### Related Sections
- [Getting Started](../getting-started/) | [Reference Documentation](../reference/) | [Developer Guide](../developer-guide/)

---
*Last updated: 2024-01-16*  
*Category: user-guide*  
*Tags: user-guide, workflows, transformations, debugging, cli*