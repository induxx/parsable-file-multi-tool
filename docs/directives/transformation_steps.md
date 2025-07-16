# Transformation Steps

Transformation steps are the core components of the parsable-file-multi-tool project, defining the sequence of operations applied to data during processing. Each step represents a specific action or transformation, enabling flexible and modular workflows.

## Overview
Transformation steps are defined in YAML files and executed sequentially. They can include fetching data, processing it, converting formats, and pushing results to external systems. Steps can also be parameterized to handle specific endpoints, queries, or configurations.

## Step templates
Transformation steps can be predefined in a template file, which are preconfigured transformation steps that can be reused in multiple transformation steps. For example pulling and pushing data from an Akeneo PIM system. 

@see [Transformation Templates](../helpers/transformation_templates.md)

## Use Cases
Below are common use cases for transformation steps, derived from the examples:


```yaml
transformation_steps:
  - run: akeneo/jsonl/query_akeneo-entities.yaml
    once_with:
      endpoint: products
      query: '%app_querystring%'
  - process-akeneo-product.yaml
  - run: akeneo/jsonl/push_akeneo-entities.yaml
    once_with:
      endpoint: products
```

## Example Configurable Transformation Steps
Here is an example of transformation steps, where we run some parametersized steps. The first step runs the akeneo/jsonl/query_akeneo-entities.yaml file with the endpoint set to categories and the query set to a specific value. 
The second step runs the akeneo/jsonl/pull_akeneo-reference-entities inside a with loop, where the endpoint is set to brands and color.

```yaml
transformation_steps:
  - run: akeneo/jsonl/query_akeneo-entities.yaml
    once_with:
      endpoint: categories
      query: '%s?search={"parent":[{"operator":"=","value":"master"}]}'

  - run: akeneo/jsonl/pull_akeneo-reference-entities.yaml
    with:
      endpoint:
        - brands
        - color
```

