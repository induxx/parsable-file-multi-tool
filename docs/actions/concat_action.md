
The **`concat`** action allows you to combine the values of multiple fields into a single field using a specified format string. It takes two arguments: **`key`**, which specifies the target field to which the concatenated value will be written, and **`format`**, which specifies the format string to use.

The format string can contain placeholders in the form **`%FIELD_NAME%`**, which will be replaced with the corresponding field values when the action is executed. For example, in the YAML file you provided:

```yaml
actions:
  label_formatting_nl_be:
    action: concat
    key: label-nl_BE
    format: '%label-nl_BE% (%code%)'
```

The **`concat`** action will combine the values of the **`label-nl_BE`** and **`code`** fields using the specified format string, and write the result to the **`label-nl_BE`** field.

Input:

```yaml
item:
  - label-nl_BE: "Product A"
  - code: "123456"
```

YAML file:

```yaml
actions:
  label_formatting_nl_be:
    action: concat
    key: label-nl_BE
    format: '%label-nl_BE% (%code%)'
```

Output:

```yaml
item:
  - label-nl_BE: "Product A (123456)"
  - code: "123456"
```

In this example, the **`concat`** action combines the values of the **`label-nl_BE`** and **`code`** fields using the specified format string, and writes the result to the **`label-nl_BE`** field. The original value of the **`code`** field is preserved.