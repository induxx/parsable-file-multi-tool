
The **`retain`** action allows you to keep only the keys or fields that you specify, while discarding all others. It takes a list of keys as an argument, and retains only those keys in the item being processed.

Here's an example of how you might use the **`retain`** action in a YAML file:

```yaml
actions:
  retain_important_fields:
    action: retain
    keys: [NAME, AGE, GENDER]
```

This **`retain`** action will keep only the keys **`NAME`**, **`AGE`**, and **`GENDER`** in the item being processed, discarding all other keys.

Here's an example of how the **`retain`** action might be used in a more complete processing pipeline:

```yaml
actions:
  retain_important_fields:
    action: retain
    keys: [NAME, AGE, GENDER]
  rename_fields:
    action: rename
    mapping:
			OLD_FIELD_NAME: NEW_FIELD_NAME
			OLD_FIELD_NAME_2: NEW_FIELD_NAME_
```

In this example, the **`retain`** action is used first to keep only the keys **`NAME`**, **`AGE`**, and **`GENDER`** in the item being processed. Then, the **`convert_fields`** action is used to convert the data type of the **`AGE`** and **`GENDER`** fields to **`INTEGER`** and **`STRING`**, respectively. Finally, the **`add_default_values`** action is used to add default values for the **`AGE`** and **`GENDER`** fields, in case they are not present in the item.

  rename_fields:
    action: rename
    mapping:
			OLD_FIELD_NAME: NEW_FIELD_NAME
			OLD_FIELD_NAME_2: NEW_FIELD_NAME_

  rename_fields:
    action: rename
    mapping:
			OLD_FIELD_NAME: NEW_FIELD_NAME
			OLD_FIELD_NAME_2: NEW_FIELD_NAME_