# Introduction

## Installation

```bash
bin/docker/composer install
```

## Configuration

Some transformation files depend on API credentials that are not stored inside the transformations.

You can add these directives on your main-STEP or the transformation you are working on.

In this example "my-account" is used as the account name accross all transformations
```yaml
account:
   name: "my-account"
   username: "my-username"
   password: "my-password"
   domain: "my-domain"
   client_id: "my-client-id"
   client_secret: "my-client-secret"
```

You could also add context parameters to your transformation file the same way
```yaml
context:
    my-parameter: "my-value"
```

## Usage

Discover all options
```bash
bin/docker/console transformation --help
```

Here is a minimal example of a transformation file:
# Transform a file
```bash
bin/docker/console transformation --file example/project/transformations/transformation.yaml --source example/project/source --workpath example/project/workpath
```

### Debugging

Debug the first item that would be written
```bash
bin/docker/console transformation --file ... --source ... --workpath ... --debug
```

try the first 100 items
```bash
bin/docker/console transformation --file ... --source ... --workpath ... --try 100
```

In case you have dynamic mappings, you can show the mappings that would be used
```bash
bin/docker/console transformation --file ... --source ... --workpath ... --showMappings
```

Write only this line, skip the rest
```bash
bin/docker/console transformation --file ... --source ... --workpath ... --line 100
```

Write only this line, skip the rest
```bash
bin/docker/console transformation --file ... --source ... --workpath ... --line 100
```