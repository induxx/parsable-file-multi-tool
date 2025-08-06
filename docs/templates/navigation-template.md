# Navigation Template

This template provides consistent navigation elements for all documentation pages.

## Standard Navigation Header

Add this navigation header to the top of each documentation page (after the main title):

```markdown
---
**Navigation:** [🏠 Home](../index.md) | [📚 Getting Started](../getting-started/) | [👥 User Guide](../user-guide/) | [📖 Reference](../reference/) | [🔧 Developer Guide](../developer-guide/) | [💡 Examples](../examples/)

**Current Section:** [Getting Started](../getting-started/) > Current Page
---
```

## Breadcrumb Navigation

For deep sections, use breadcrumb navigation:

```markdown
**📍 You are here:** [Documentation Home](../index.md) > [Section](../index.md) > [User Guide](../user-guide/) > Current Page
```

## Section-Specific Navigation

### Getting Started Section
```markdown
**📚 Getting Started:** [Installation](../getting-started/installation.md) | [Quick Start](../getting-started/quick-start.md) | [Configuration](../getting-started/configuration.md)
```

### User Guide Section  
```markdown
**👥 User Guide:** [Transformations](../user-guide/transformations.md) | [Debugging](../user-guide/debugging.md) | [CLI Commands](../reference/cli-commands.md)
```

### Reference Section
```markdown
**📖 Reference:** [Actions](../reference/actions/) | [Directives](../reference/directives/) | [Converters](../reference/converters/) | [Tools](../reference/tools/) | [CLI Commands](../reference/cli-commands.md)
```

### Developer Guide Section
```markdown
**🔧 Developer Guide:** [Architecture](../developer-guide/architecture.md) | [Extending](../developer-guide/extending.md) | [Contributing](../developer-guide/contributing.md)
```

## Footer Navigation

Add this footer to each page:

```markdown
---

## Quick Navigation

- **🏠 [Documentation Home](../index.md)** - Main documentation index
- **🔍 [Search Tips](../index.md#search-tips)** - How to find information quickly
- **❓ [Getting Help](../user-guide/debugging.md#getting-help)** - Support and troubleshooting resources

### Related Sections
- [User Guide](../user-guide/) | [Reference](../reference/)
- [Documentation Home](../index.md)

---
*Last updated: YYYY-MM-DD*  
*Category: section-name*  
*Tags: tag1, tag2, tag3*
```

## Search-Friendly Heading Structure

Use this consistent heading hierarchy:

```markdown
# Page Title (H1 - Only one per page)

## Major Section (H2)

### Subsection (H3)

#### Details (H4)

##### Minor Details (H5)

###### Fine Details (H6)
```

## Cross-Reference Format

Use consistent cross-reference formatting:

```markdown
## Related Topics

- **[Actions Reference](../reference/actions/)** - Brief description of what this covers
- **[Directives Reference](../reference/directives/)** - Brief description
- **[External Resource](https://example.com)** - External link description

## See Also

- [User Guide](../user-guide/)
- [Reference Documentation](../reference/)
- [Examples](../examples/)
```

## Usage Instructions

1. Copy the appropriate navigation elements to your documentation page
2. Update the paths to match your file location
3. Customize the "Current Section" breadcrumb
4. Add relevant cross-references in the footer
5. Update the metadata (date, category, tags)