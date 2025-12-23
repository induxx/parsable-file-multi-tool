Here's some examples of how you might use the **`modify`** action in a YAML file:

### DecodeSpecial Cell Modifier

This modifier allows for html entity decoding of string values. 
HTML string values can be decoded for `quotes` or `xhtml`
This modifier expects and modifies the Cell value.

```yaml
actions:
  decode_special:
    action: modify
    modifier: decode_special
    keys: 'encoded_string'
    decoder: quotes
```

### FilterEmptyString Modifier

This modifier filters or removes any empty values.
This modifier expects and modifies the item array

```yaml
actions:
  filter_empty_string:
    action: modify
    modifier: filter_empty
```

### FilterWhiteSpaces Modifier

This modifier filters or removes any whitespaces from values.
This modifier expects and modifies the item array

```yaml
actions:
  filter_white_spaces:
    action: modify
    modifier: filter_whitespace
```

### IconvEncoding Cell Modifier

This modifier transforms a string value from one character encoding to another
This modifier expects and modifies the Cell value.
The option locale is optional and lets you set the locale before running iconv.

[# php.net](https://www.php.net/manual/en/function.iconv.php)

```yaml
actions:
  iconv_encoding:
    action: modify
    modifier: iconv
    keys: 'text_value,another_text_value'
    in_charset: 'UTF-8'
    out_charset: 'ISO-8859-1'
    locale: 'en_US.utf8'
```

### NullifyEmptyString Row Modifier

This modifier transforms a empty string values to `null`.
This modifier expects and modifies the item array

```yaml
actions:
  nullify_empty_string:
    action: modify
    modifier: nullify
```

### ReferenceCode Cell Modifier

This modifier transforms a string value into a reference compliant string based on the following rules.
The value response may contain only letters, numbers and underscores.
All other character will be replaced by an underscore.
This modifier expects and modifies the Cell value.

```yaml
actions:
  reference_code:
    action: modify
    modifier: reference_code
    keys: 'reference_field'
```

### ReplaceCharacter Cell Modifier

This modifier will replace any characters with any other complaint character in your system.
This modifier expects and modifies the Cell value.
 
```yaml
actions:
  replace_char:
    action: modify
    modifier: replace_char
    keys: 'reference_field'
    characters:
        Š: S
        š: s
        ™: TM
        ®: trademark
        ©: (C)
```

### SnakeCase Cell Modifier

This modifier transforms a string value into a snake_case compliant string based on the following rules.
The value response may contain only lowercase letters, numbers and underscores.
This modifier expects and modifies the Cell value.

```yaml
actions:
  snake_case:
    action: modify
    modifier: snake_case
    keys: 'text_values'
```

### StringToLower Cell Modifier

This modifier transforms a string value with upper cases into a lowercase value.
This modifier expects and modifies the Cell value.

```yaml
actions:
  string_to_lower:
    action: modify
    modifier: lower
    keys: 'text_values'
```

### StringToUpper Cell Modifier

This modifier transforms a string value with lower cases into an uppercase value.
This modifier expects and modifies the Cell value.

```yaml
actions:
  string_to_upper:
    action: modify
    modifier: upper
    keys: 'text_values'
```

### StripSlashes Cell Modifier

This modifier transforms a string value by stripping the unquoted string or remove the escaped character.
This modifier expects and modifies the Cell value.

> !WARNING!
> 
> stripslashes can be used if you aren't inserting this data into a place (such as a database) that requires escaping.

```yaml
actions:
  strip_slashes:
    action: modify
    modifier: stripslashes
    keys: 'text_values'
```

### StripTags Cell Modifier

This modifier transforms a string value by stripping the html elements from your string.
This modifier expects and modifies the Cell value.

```yaml
actions:
  strip_tags:
    action: modify
    modifier: strip_tags
    keys: '<p>html_content</p>'
```

### UrlEncode Cell Modifier

This modifier transforms a string value by raw url-encoding according to RFC 3986.
This modifier expects and modifies the Cell value.

```yaml
actions:
  url_encode:
    action: modify
    modifier: url_encode
    keys: 'url_values'
```

### UTF8Encode Cell Modifier

This modifier tries to autodetect and transform the encoded string to the UTF-8 standard.
Akeneo expects UTF-8 character encoding so this modifier is mostly used during import.
This modifier expects and modifies the Cell value.

```yaml
actions:
  utf8_encode:
    action: modify
    modifier: UTF-8
    keys: 'text_values'
```