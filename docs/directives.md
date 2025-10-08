# Directives Overview

Directives are essential components in the configuration files of this project. They define the structure, behavior, and processing logic for data transformations and pipelines. Below is an overview of the key directives:

## Aliases
Aliases provide a way to define reusable placeholders for file paths or filenames, making pipeline configurations more flexible and easier to maintain.

## Context
The context directive sets up shared configurations or variables that are accessible throughout the transformation process. It ensures consistency and facilitates integration with external systems.

## Converters
Converters are used to transform unstructured, non-normalized data into structured, normalized formats. They are particularly useful for handling complex API data and can be linked to streamline data processing.

## Lists
Lists define multi-dimensional arrays of values, which are useful for storing data that will be processed in pipeline actions.

## Mapping
Mappings specify key-value data sets that can be referenced in pipeline actions, enabling efficient data transformation and lookup.

## Pipelines
Pipelines represent a series of processing steps applied to data, including input, actions, and output. They provide a flexible framework for transforming and manipulating data.

This document serves as a high-level introduction to directives. For detailed information, refer to the individual directive files in the `docs/directives/` folder.

