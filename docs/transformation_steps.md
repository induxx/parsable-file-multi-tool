# Transformation Steps YAML Documentation

This document describes the structure and usage of transformation steps YAML files in the parsable-file-multi-tool project. It covers the main sections: `transformation_steps`, `context`, and the use of `secrets.yaml`.

---

## 1. Overview
A transformation steps YAML file defines the sequence of data processing steps, context variables, and configuration for a transformation run. It is typically referenced via the `--file` argument when running the transformation command.

---

## 2. Structure
A typical transformation steps YAML file contains the following sections:
  
```yaml
context:
# Optional: key-value pairs for runtime context

aliases:
  xml_file: INTRA_2025-example.xml

transformation_steps:
  - read-xml-data.yaml
  - write-xml-data-intra.yaml
```

### Section Details

#### `context`
- Used to define variables and configuration available to all steps.
- Can include API credentials, file paths, or any runtime data.
- Example:
  ```yaml
  context:
    api_url: https://example.com/api
    user: myuser
  ```

#### `converter`
- Defines data conversion or extraction logic.
- Each entry specifies a converter name and its options.

#### `aliases`
- Used to define file or variable aliases for use in steps.

#### `transformation_steps`
- Ordered list of YAML files (relative to the transformation file) that define each step in the process.
- Each referenced file contains step-specific configuration.

---

## 3. Using `secrets.yaml`
  
If a `secrets.yaml` file exists in the same directory as the main transformation steps YAML, it is automatically loaded and merged into the configuration at runtime. This allows you to keep sensitive data (like API keys or passwords) separate from the main configuration.

- Place `secrets.yaml` alongside your main transformation YAML.
- Any keys in `secrets.yaml` will override or extend the main file's configuration.
- Example `secrets.yaml`:
  ```yaml
  context:
    api_key: my-secret-key
    password: supersecret
  
  accounts:
    - name: akeneo_api_account
      domain: https://your-akeneo/
      client_id: 1_12345
      secret: XXXX
      username: induxx_app_5759
      password: my-secret-password
  ```

### `accounts` directive
- The `accounts` section in `secrets.yaml` is used to define one or more external service accounts (such as API credentials) that can be referenced in your transformation steps.
- Each account entry typically includes a `name` and the required authentication fields for the service.
- These accounts can be referenced in the `context` or by steps that require authentication.

---

## 4. Runtime Context Injection

At runtime, additional context variables are injected automatically, such as:
  - `operation_create_datetime`: Current datetime
  - `last_completed_operation_datetime`: Datetime two hours ago
  - `transformation_file`: Path to the transformation YAML
  - `sources`, `workpath`, `debug`, etc.
  
  These are available in the `context` section for use in steps.

---

## 5. Best Practices
- Keep secrets and sensitive data in `secrets.yaml`.
- Use the `context` section for variables needed across multiple steps.
- Reference step files in `transformation_steps` in the order they should be executed.
- Use `aliases` to simplify file or variable references in your steps.

---

