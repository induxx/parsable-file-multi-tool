
The `date_time` action facilitates the transformation of date and time formats from one structure to another. It requires three parameters:

- `field`: Denotes the target field where the formatted value is stored.
- `inputFormat`: Defines the current format of the date and time.
- `outputFormat`: Dictates the desired format for the date and time.

For example:

```yaml
actions:
    date_time_conversion:
        action: date_time
        field: event_date
        inputFormat: 'Y-m-d H:i:s'
        outputFormat: 'F j, Y, g:i a'
```

In this use case, the date_time_conversion action is used to convert the event_date field from the input format 'Y-m-d H:i:s' to the output format 'F j, Y, g:i a'. Adjust the field names and format strings according to your specific use case requirements.

Input:

```yaml
item:
  - event_date: "2023-10-18 15:30:00"
  - code: "123456"
```

YAML file:

```yaml
actions:
    date_time_conversion:
        action: date_time
        field: event_date
        inputFormat: 'Y-m-d H:i:s'
        outputFormat: 'F j, Y, g:i a'
```

Output:

```yaml
item:
    - event_date: "October 18, 2023, 3:30 pm"
    - code: "123456"
```

This demonstrates how the DateTimeAction would convert the date and time format from the input to the desired output format, as specified in the configuration. 

## Date Format Mapping

The following are the supported date format mappings for the `inputFormat` and `outputFormat` parameters:

- `ATOM`: Y-m-d\TH:i:sP
- `COOKIE`: l, d-M-Y H:i:s T
- `ISO8601`: Y-m-d\TH:i:sO
- `ISO8601_EXPANDED`: X-m-d\TH:i:sP
- `RFC822`: D, d M y H:i:s O
- `RFC850`: l, d-M-y H:i:s T
- `RFC1036`: D, d M y H:i:s O
- `RFC1123`: D, d M Y H:i:s O
- `RFC7231`: D, d M Y H:i:s \G\M\T
- `RFC2822`: D, d M Y H:i:s O
- `RFC3339`: Y-m-d\TH:i:sP
- `RFC3339_EXTENDED`: Y-m-d\TH:i:s.vP
- `RSS`: D, d M Y H:i:s O
- `W3C`: Y-m-d\TH:i:sP

Feel free to use these formats to customize your date and time conversions accordingly.

For more info you can check the [PHP date time format documentation](https://www.php.net/manual/en/class.datetime.php).