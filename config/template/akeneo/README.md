## Templates

We support the following Akeneo endpoints.

- attributes
- family
- family_variants
- options
- products
- product-models
- categories
- reference-entities

If you like to add support to the template system, make sure you have the required converter(s) in place.

You can the API in 3 different Styles: JSON, CSV & as ITEM.

### [JSON]
Will not change anything, all Payloads are as is.
Multidimensional Payload Structure is kept (We don't use any converters).
The Style is not very flexible, to make structural changes or the read multidimensional data.

### [CSV]
Will convert the Payload to CSV style flat data.
This style is not very flexible to make structural changes.
The data is flat, so it's relatively easy to manipulate but looses on structural integrity, as you don't have contextual awareness.

### [ITEM] - [V3-edition]
Will convert the Payload into a easy parsable Item. 
The data becomes discoverable, searchable across other 
This type will give you the most flexibility to manipulate the data.
You can export to CSV and/or API from this point.

### Examples

```yaml
transformation_steps:
  - {'run': akeneo/json/pull_akeneo-reference-entities.yaml, 'with': {'endpoint': ['brand']}}
  - {'run': akeneo/json/pull_akeneo-entities.yaml, 'with': {'endpoint': ['attributes', 'families']}}

  - {'run': akeneo/json/push_akeneo-reference-entities.yaml, 'with': {'endpoint': ['brand']}}
  - {'run': akeneo/json/push_akeneo-entities.yaml, 'with': {'endpoint': ['attributes', 'families']}}
```

In this example the only custom file is change_other_attributes.yaml.

```yaml
context:
  akeneo_read_connection: '%source_resource%'
  akeneo_write_connection: '%target_resource%'

transformation_steps:
  - {'run': akeneo/json/pull_akeneo-entities.yaml, 'with': {'endpoint': ['attributes']}}
  # DO SOMETHING
  - {'run': change_other_attributes.yaml, 'with': {'endpoint': ['attributes']}}
  - {'run': akeneo/json/push_akeneo-entities.yaml, 'with': {'endpoint': ['attributes']}}
```

```yaml
# change_other_attributes.yaml

sources:
  - '%workpath%/akeneo_%endpoint%.jsonl'

pipeline:
  input:
    reader:
      type: jsonl
      filename: '%workpath%/akeneo_%endpoint%.jsonl'
      filter:
        group: 'other'

  actions:
    # DO SOMETHING

  output:
    writer:
      type: jsonl
      filename: '%workpath%/akeneo_%endpoint%.jsonl'
```
