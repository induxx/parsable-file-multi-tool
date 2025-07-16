
The **`remove`** action allows you to remove unwanted keys or fields from an item being processed. It takes a list of keys as an argument, and removes those keys from the item.

Here's an example of how you might use the **`remove`** action in a YAML file:

```yaml
actions:
  remove_unwanted_fields:
    action: remove
    keys: [GROUP, TYPE, UID, ATTRIBUTE_CODE, DUMP_TIMESTAMP, MEASUREMENT_FAMILY]

```

This **`remove`** action will remove the keys **`GROUP`**, **`TYPE`**, **`UID`**, **`ATTRIBUTE_CODE`**, **`DUMP_TIMESTAMP`**, and **`MEASUREMENT_FAMILY`** from the item being processed.

Here's an example of how the **`remove`** action might be used in a more complete processing pipeline:

```yaml
actions:
  remove_unwanted_fields:
    action: remove
    keys: [GROUP, TYPE, UID, ATTRIBUTE_CODE, DUMP_TIMESTAMP, MEASUREMENT_FAMILY]
  rename_fields:
    action: rename
    mapping:
			OLD_FIELD_NAME: NEW_FIELD_NAME
			OLD_FIELD_NAME_2: NEW_FIELD_NAME_
```

In this example, the **`remove`** action is used first to remove unwanted fields from the item. Then, the **`add_timestamp`** action is used to add a new field called **`TIMESTAMP`** to the item, using the current date and time as the value. The **`rename`** action is used to rename specific fields in the item, and the **`convert_fields`** action is used to convert the data type of certain fields.