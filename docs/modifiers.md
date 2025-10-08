### Modifier
The modifier Component is a destructive pattern, meaning that it's value is unrecoverable or irreversible to it's original state.
Modifiers are ALWAYS used as the last phase in the process.

## Modifiers
- Array Unflatten
- Decode Special
- Nullify Empty String
- Reference Code
- Snake Case
- Strip Slashes
- UTF8 Encode
- Replace Character
- Iconv Encoder

#### Array Unflatten
By array flatten we will take a multidimensional array and turn it into a regular "single" dimensional array. 

Array unflatten will reverse this.

#### Decode Special

Decode Special will decode every special character like '\&Oslash;' to something normal like 'Ø'

And in the attribute_option.yml you can give some options. So you can decode only what you want to decode.

|      option     |  description                                               | 
|-----------------|:----------------------------------------------------------:|
| "quotes"        |   will decode every special character and ' and " quotes   |
| "no quotes"     |   will decode every special character but no quotes        |
| "double quotes" |   will decode every special character and " quotes         |
| "xml 1"         |   will decode every  xml 1 special character               |
| "xhtml"         |   will decode every xhtml special character                |
| "html 4.01"     |   will decode every html 4.01 special character            |
| "html 5"        |   will decode every html 5 special character               |

#### Nullify Empty String
Nullify will change every empty string to an NULL. 

#### Reference Code
Will change the string so it is suitable to be used as a reference code. 

'@myNewValue   And His New Value' becomes 'myNewValue___And_His_New_Value'

#### Snake Case
Snake casing is the process of writing compound words so that the words are separated with an underscore symbol (_) instead of a space. 

The first letter is usually changed to lowercase. 
 
 Some examples of Snake case would be "foo_bar" or "hello_world"
#### Strip Slashes
Like the name implies this will strip slashes form a text or word.

#### UTF8 Encode
converts the string data from the ISO-8859-1 encoding to UTF-8.
Smart hint, encode first only the localized data.

#### Replace Character | replace_char
This modifier will replace your selected character into something else.
This is only required when dealing with dirty data, encoding mistakes should be dealt with different encoding options.

```yaml
options:
  characters:
    á: a
    é: e
```

#### Iconv Encoding | iconv
This modifier exposes iconv encoding options
This is only required when dealing with dirty data, encoding mistakes should be dealt with different encoding options.

default options
```yaml
options:
  in_charset: utf-8
  out_charset: null
  locale: en_US.utf8
```

example
```yaml
options:
  out_charset: ascii//TRANSLIT
```

```php
$modifier = new \Misery\Component\Modifier\IconvEncodingModifier();
$modifier->setOptions([
    'out_charset' => 'ascii//TRANSLIT',
]);
$result = $modifier->modify('Fóø Bår');
# 'Foo Bar'

```