# AkeneoProductConverter

@file src/Component/Converter/Akeneo/Api/Product.php

**Purpose:**
Converts Akeneo API product data into a structured format for further processing or integration. The data it returns is flattened and ready easier to use in consecutive actions.

**Typical Use Case:**
- Importing product or catalog data from Akeneo.
- Parsing and transforming Akeneo product data into arrays or objects for downstream actions.

**Related Files:**
- `src/Component/Converter/Akeneo/Api/Product.php`

**YAML Example:**
```yaml
converter:
  - name: akeneo/product/api
    options:
      list: 'attributes_list'
```

**Options:**

- `list`: List of attributes to extract from the product data.
