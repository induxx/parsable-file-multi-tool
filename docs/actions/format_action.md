
Optional) the string to search for when using the **`replace`** function.

The **`format`** action allows you to apply a series of formatting functions to a field or fields in an item being processed. It takes the following arguments:

- **`field`**: the field or fields to be formatted. This can be a string (for a single field) or a list of strings (for multiple fields).
- **`functions`**: the list of formatting functions to be applied, in the order they should be applied. Each function is a PHP native function with its own set of arguments.
- **`search`**: (Optional) the string to search for when using the **`replace`**
 function.
- **`replace`**: (Optional) the string to replace **`search`** with when using the **`replace`** function.
- **`mille_sep`**: (Optional) the string to use as the thousands separator when using the **`number`** function.
- **`prefix`**: (Optional) the string to use as the prefix when using the **`prefix`** function.
- **`format`**: (Optional) the format string to use when using the **`number`** function.

Here's an example of how you might use the **`format`** action in a YAML file:

```yaml
actions:
  correct_the_size_formatting:
    action: format
    field: [ LENGTH, HEIGHT, WIDTH, WEIGHT ]
    functions: [ prefix, replace, number ]
    search: ','
    replace: '.'
    mille_sep: ''
    prefix: '0'
    format: '%04d'
```

In this example, the **`format`** action will apply the **`prefix`**, **`replace`**, and **`number`** functions to the **`LENGTH`**, **`HEIGHT`**, **`WIDTH`**, and **`WEIGHT`** fields, in that order. The **`prefix`** function will add a prefix of **`'0'`** to the field value, the **`replace`** function will replace all instances of **`','`** with **`'.'`**, and the **`number`** function will format the field value as a number with no thousands separator and a minimum of 4 digits, using the specified format string.