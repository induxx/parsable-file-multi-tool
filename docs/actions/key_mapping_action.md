
# Key Mapping Action

## Overview

The key_mapping action allows you to rename specific fields in an item being processed using predefined mapping lists. It's similar to the rename action but supports dynamic mapping configurations and external mapping definitions, making it essential for complex field transformation workflows.

## Syntax

```yaml
actions:
  - action: key_mapping
    list:
      OLD_FIELD_NAME: NEW_FIELD_NAME
      ANOTHER_OLD: ANOTHER_NEW

# Or using external mapping reference
actions:
  - action: key_mapping
    list: mapping_name
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| list | object/string | Yes | - | Dictionary of field mappings or reference to external mapping |

### Parameter Details

#### list
Defines the field mappings to apply. Can be either an inline dictionary or a reference to an external mapping configuration.

- **Format:** Object with key-value pairs or string reference
- **Example:** `{"SKU": "sku", "TITLE": "title"}` or `"mappings"`
- **Behavior:** All specified field mappings will be applied if the source fields exist

## Examples

### Inline Field Mapping

```yaml
actions:
  - action: key_mapping
    list:
      SKU: sku
      ERP_TITLE_NL: Title-nl_BE
      ERP_TITLE_FR: Title-fr_BE
      ERP_TITLE_EN: Title-en_GB
      ARTICLE_TYPE: Article_type
      TYPE: ERP_type
```

**Input:**
```json
{
  "SKU": "PROD001",
  "ERP_TITLE_NL": "Product Nederlandse Titel",
  "ERP_TITLE_FR": "Titre du Produit Français",
  "ERP_TITLE_EN": "Product English Title",
  "ARTICLE_TYPE": "widget",
  "TYPE": "physical"
}
```

**Output:**
```json
{
  "sku": "PROD001",
  "Title-nl_BE": "Product Nederlandse Titel",
  "Title-fr_BE": "Titre du Produit Français",
  "Title-en_GB": "Product English Title",
  "Article_type": "widget",
  "ERP_type": "physical"
}
```

### External Mapping Reference

```yaml
mapping:
  - name: product_mappings
    values:
      SKU: sku
      BRAND_ID: brand
      CATEGORY_ID: category
      PRICE_USD: price

actions:
  - action: key_mapping
    list: product_mappings
```

**Input:**
```json
{
  "SKU": "WIDGET001",
  "BRAND_ID": "ACME",
  "CATEGORY_ID": "electronics",
  "PRICE_USD": 29.99,
  "DESCRIPTION": "High-quality widget"
}
```

**Output:**
```json
{
  "sku": "WIDGET001",
  "brand": "ACME",
  "category": "electronics",
  "price": 29.99,
  "DESCRIPTION": "High-quality widget"
}
```

### Database Field Standardization

```yaml
actions:
  - action: key_mapping
    list:
      customer_first_name: firstName
      customer_last_name: lastName
      customer_email_address: email
      customer_phone_number: phone
      billing_address_line_1: billingAddress
      shipping_address_line_1: shippingAddress
```

**Input:**
```json
{
  "customer_first_name": "John",
  "customer_last_name": "Doe",
  "customer_email_address": "john@example.com",
  "customer_phone_number": "555-0123",
  "billing_address_line_1": "123 Main St",
  "shipping_address_line_1": "456 Oak Ave"
}
```

**Output:**
```json
{
  "firstName": "John",
  "lastName": "Doe",
  "email": "john@example.com",
  "phone": "555-0123",
  "billingAddress": "123 Main St",
  "shippingAddress": "456 Oak Ave"
}
```

## Use Cases

### Use Case 1: Legacy System Integration
Map field names from legacy systems to modern API standards during data migration.

### Use Case 2: Multi-language Field Mapping
Standardize field names across different language-specific data sources.

### Use Case 3: Dynamic Configuration Management
Use external mapping configurations that can be updated without changing transformation code.

## Common Issues and Solutions

### Issue: Source Fields Not Found

**Symptoms:** Expected field mappings are not applied.

**Cause:** Source field names in the mapping don't exist in the input data.

**Solution:** Verify that source field names match exactly with the data fields.

```yaml
# Debug input fields first
actions:
  - action: debug
  - action: key_mapping
    list:
      EXISTING_FIELD: new_field_name
```

### Issue: External Mapping Not Found

**Symptoms:** Key mapping action fails when using external mapping reference.

**Cause:** The referenced mapping name doesn't exist in the mapping configuration.

**Solution:** Ensure the mapping is properly defined and the name matches exactly.

```yaml
# Ensure mapping is defined
mapping:
  - name: correct_mapping_name
    values:
      OLD_FIELD: new_field

actions:
  - action: key_mapping
    list: correct_mapping_name  # Must match mapping name exactly
```

### Issue: Conflicting Field Names

**Symptoms:** Some field mappings are lost or overwritten unexpectedly.

**Cause:** Multiple source fields are mapped to the same target field name.

**Solution:** Ensure each target field name is unique in the mapping.

```yaml
# Avoid mapping multiple sources to same target
actions:
  - action: key_mapping
    list:
      SOURCE_1: unique_target_1
      SOURCE_2: unique_target_2  # Not the same as target_1
```

## Performance Considerations

- Key mapping operations are very fast as they work in memory
- External mapping references have minimal lookup overhead
- Large mapping dictionaries have negligible performance impact
- Consider organizing mappings logically for maintainability

## Related Actions

- [Rename Action](./rename_action.md) - Simple field renaming without external mappings
- [Value Mapping in List Action](./value_mapping_in_list_action.md) - Map field values instead of field names
- [Statement Action](./statement_action.md) - Add conditional logic around key mapping

## See Also

- [Transformation Steps](../directives/transformation_steps.md)
- [Mapping Configuration](../directives/mapping.md)
- [Field Management](../user-guide/field-management.md)

---

*Last updated: 2024-01-16*
*Category: reference*
*Action Type: transformation*