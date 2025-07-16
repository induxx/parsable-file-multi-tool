
The **`rename`** action allows you to rename specific fields in an item being processed. It takes two arguments: **`from`** and **`to`**, or a dictionary of field mappings in the form **`fields: { OLD_FIELD_NAME: NEW_FIELD_NAME }`**.

Here's an example of how you might use the **`rename`** action in a YAML file, using the **`from`** and **`to`** arguments:

```yaml
actions:
  rename_unit:
    action: rename
    from: ATTRIBUTE_UNIT
    to: unit
```

This **`rename`** action will rename the **`ATTRIBUTE_UNIT`** field to **`unit`**.

Here's an example of how you might use the **`rename`** action with the **`fields`** argument:

```yaml
actions:
  rename_fields:
    action: rename
    fields:
      ATTRIBUTE_UNIT: unit
      OLD_FIELD_NAME: NEW_FIELD_NAME
      OLD_FIELD_NAME_2: NEW_FIELD_NAME_2
```

This **`rename`** action will rename the **`ATTRIBUTE_UNIT`** field to **`unit`**, the **`OLD_FIELD_NAME`** field to **`NEW_FIELD_NAME`**, and the **`OLD_FIELD_NAME_2`** field to **`NEW_FIELD_NAME_2`**.