
title: "Key-mapping Action"
date: 2022-12-23T11:02:05+06:00
# type don't remove or customize
draft: false
---

The **`key_mapping`** action allows you to rename specific fields in an item being processed, similar to the **`rename`** action. It takes a single argument, **`list`**, which is a dictionary of field mappings in the form **`{ OLD_FIELD_NAME: NEW_FIELD_NAME }`**.

Here's an example of how you might use the **`key_mapping`** action in a YAML file:

```yaml
actions:
  key_mapping:
    action: key_mapping
    list:
      SKU: sku
      ERP_TITLE_NL: Title-nl_BE
      ERP_TITLE_FR: Title-fr_BE
      ERP_TITLE_EN: Title-en_GB
      ARTICLE_TYPE: Article_type
      TYPE: ERP_type
```

In this example, the **`key_mapping`** action will rename the specified fields in the item being processed. If any of the fields do not exist in the item, they will not be created.

Here's an example of how you might use the **`key_mapping`** action in this way:

```yaml
mapping:
  - name: mappings
    values:
      SKU: sku
      BRAND_ID: BRAND
      CALLNAME_ID: family

pipeline:
  actions:
    key_mapping:
      action: key_mapping
      list: mappings
```

In this example, the **`key_mapping`** action will use the field mappings specified in the **`mapping`** object to rename the fields in the item being processed. If any of the fields do not exist in the item, they will not be created.