The aliases directive is used to define aliases for file paths or filenames that are used in a pipeline configuration. This can be useful when you want to reuse the same file or filename in multiple places in the pipeline configuration, or when you want to make the pipeline configuration more flexible by using placeholder values.

The aliases directive is a dictionary where each key is an alias name, and each value is a file path or filename.

Here's an example:

```yaml
aliases:
  input_file: 'input_data.csv'
  output_file: 'output_data.csv'
```

In this example, the aliases directive defines two aliases: input_file and output_file. The values of these aliases are file paths that contain placeholder values. These placeholder values are %sources% and %workpath%, which can be replaced with values from other parts of the pipeline configuration.

To use an alias in the pipeline configuration, you can simply reference the alias name, like this:

```yaml
pipeline:
  input:
    reader:
      type: csv
      filename: 'input_file'
  output:
    writer:
      type: csv
      filename: 'output_file'
```

In this example, the filename property of the reader and writer directives reference the input_file and output_file aliases, respectively.

### Wildcards
Aliases directive also support wildcards when you have a file that uses for example a timestamp.
Note that you can't make a match with multiple files yet. Each alias must reference to a single file.

```yaml
# possible input file
/sources/input_data_20231112.csv

aliases:
  input_file: 'input_data_*.csv'
```