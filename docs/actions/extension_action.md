
The **`extension`** action allows you to extend the action formula outside the shipped action set.
In this case an extension can be anything you want as long you follow the interface that is provided.
You can create options or fetch readonly data from the configuration.

This action manipulates **fields** and **values**.

Here's an example of how you might use the **`extension`** action in a YAML file:

### When to use or not use an extension
Extensions are very powerful, and yet very dangerous when used wrong.
Here are some guidelines you should follow.

#### When NOT to use an extension
- making external calls (use a converter)
- combining lots of regular actions into one

#### When to use an extension
- when you to target some specific fields
- when you need to combine and create a single field with multiple sources

```yaml
actions:
  my_custom_extension:
    action: extension
    extension: MyCustomExtension
    source: other_products.csv
    # your options
    my_option: my_value
```

Here is an extension template.

```php
<?php

namespace Extensions;

use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Configurator\ReadOnlyConfiguration;
use Misery\Component\Extension\ExtensionInterface;
use Misery\Component\Reader\ItemReaderInterface;

class MyCustomExtension implements ExtensionInterface
{
    use OptionsTrait;
    private ReadOnlyConfiguration $configuration;

    private $options = [
        'my_option' => null,
    ];

    public function __construct(ReadOnlyConfiguration $configuration, array $options)
    {
        $this->configuration = $configuration;
        $this->setOptions($options);
    }

    public function apply($item): array
    {
        $option = $this->getOption('my_option');
        # manipulate here
        return $item;
    }
}
```

### ReadOnlyConfiguration
The ReadOnlyConfiguration allows you to read most of the data like lists, sources, or mapping data.

### Example
Let's make a small example to make more clear.

Input:

```yaml
item:
  street: 123 Main Street
  city: Anytown
  state: CA
  country: USA
```

YAML file:

```yaml
actions:
    store_address_line:
        action: extension
        extension: MakeAddressLineExtension
        fields: ['street', 'city', 'state', 'country']
        store_field: address_line
```

Output:

```yaml
item:
    - store_field: "street: 123 Main Street, city: Anytown, state: CA, country: USA"
```
