# DebugAction

The `DebugAction` class is a utility designed to facilitate debugging during data transformation processes. It provides mechanisms to inspect and output specific parts of the data being processed, enabling developers to identify issues or verify the correctness of transformations.

## Features

### Options
The `DebugAction` supports the following options:

- **field**: Specifies a particular field in the data to debug. If set, the value of this field will be output.
- **until_field**: Specifies a field to debug until its value is encountered. Useful for inspecting data up to a certain point.
- **marker**: Allows debugging based on a file and line number. The format is `filename:line`. If provided, the content of the specified line in the file will be output.

### Methods

#### `applyAsItem`
This method takes an `ItemInterface` object and outputs its contents for debugging purposes.

#### `apply`
This method processes an array of data and applies debugging based on the configured options:

1. **Marker Debugging**: If the `marker` option is set, it reads the specified file and outputs the content of the given line.
2. **Until Field Debugging**: If the `until_field` option is set and the field exists in the data, it outputs the value of the field.
3. **Field Debugging**: If the `field` option is set, it outputs the value of the specified field.
4. **Default Debugging**: If no specific options are set, it outputs the entire data array.

## Usage

To use the `DebugAction`, configure it with the desired options and apply it to your data:

```php
$debugAction = new DebugAction();
$debugAction->setOptions([
    'field' => 'example_field',
    'marker' => 'example_file.txt:42',
]);

$debugAction->apply($data);
```

This example configures the `DebugAction` to debug the `example_field` and the content of line 42 in `example_file.txt`.

## Purpose

The `DebugAction` is particularly useful during development and testing phases, where understanding the state and structure of data at various points in the pipeline is crucial. It helps developers pinpoint issues and verify transformations effectively.
