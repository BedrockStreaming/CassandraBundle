# CassandraBundle

[![Build Status](https://travis-ci.org/M6Web/CassandraBundle.svg?branch=master)](https://travis-ci.org/M6Web/CassandraBundle)

The CassandraBundle provide a Cassandra client as a Symfony service.

## Installation

**NOTE :** You need to [install the offical datastax php driver extension](https://github.com/datastax/php-driver)

**NOTE :** Bundle still in beta like the datastax component

Require the bundle in your composer.json file :

```json
{
    "require": {
        "m6web/cassandra-bundle": "~1.0.0-beta",
    }
}
```

Register the bundle in your kernel :

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        new M6Web\Bundle\CassandraBundle\M6WebCassandraBundle(),
    );
}
```

Then install the bundle :

```shell
$ composer update m6web/cassandra-bundle
```

## Usage

Add the `m6web_cassandra` section in your configuration file. Here is the minimal configuration required. 

```yaml
m6web_cassandra:
    clients:
        myclient:
            keyspace: "mykeyspace"
            contact_endpoints:
                - 127.0.0.1
                - 127.0.0.2
                - 127.0.0.3
        
```

Then you can ask container for your client :

```php
$cassandra = $this->get('m6web_cassandra.client.myclient');

$prepared = $cassandra->prepare("INSERT INTO test (id, title) VALUES(?, ?)");

$batch     = new Cassandra\BatchStatement(Cassandra::BATCH_LOGGED);
$batch->add($prepared, ['id' => 1, 'title' => 'my title']);
$batch->add($prepared, ['id' => 2, 'title' => 'my title 2']);

$cassandra->execute($batch);

$statement = new Cassandra\SimpleStatement('SELECT * FROM test');
$result = $cassandra->execute($statement);

foreach ($result as $row) {
    // do something with $row
}

$statement = new Cassandra\SimpleStatement('SELECT * FROM test');
$result = $cassandra->executeAsync($statement);

// do something while cassandra query running

foreach($result->get() as $row) {
    // do something with row
}
```

## DataCollector

Datacollector is available when the symfony profiler is enabled. The collector allows you to see the following Cassandra data :

- keyspace
- command name
- command arguments
- execution time
- execution options override (consistency, serial consistency, page size and timeout)

**NOTE :** The time reported in the data collector may not be the real execution time in case you use the async calls : `executeAsync` and `prepareAsync`

## Configuration reference

```yaml
m6web_cassandra:
    dispatch_events: true                 # By default event are triggered on each cassandra command
    clients:
        client_name:
            keyspace: "mykeyspace"        # required keyspace to connect
            load_balancing: "round-robin" # round-robin or dc-aware-round-robin
            dc_options:                   # required if load balancing is set to dc-aware-round-robin
                local_dc_name: "testdc"
                host_per_remote_dc: 3
                remote_dc_for_local_consistency: false
            default_consistency: "one"    # 'one', 'any', 'two', 'three', 'quorum', 'all', 'local_quorum', 'each_quorum', 'serial', 'local_serial', 'local_one'
            default_pagesize: 10000       # -1 to disable pagination
            contact_endpoints:            # required list of ip to contact
                - 127.0.0.1
            port_endpoint: 9042           # cassandra port
            token_aware_routing: true     # Enable or disable token aware routing
            credentials:                  # cassandra authentication
                username: ""              # username for authentication
                password: ""              # password for authentication
            ssl: false                    # set up ssl context
            default_timeout: null         # default is null, must be an integer if set
            timeout:
                connect: 5 
                request: 5 
            retries:
                sync_requests: 0          # Number of retries for synchronous requests. Default is 0, must be an integer if set

        client_name:
            ...
```

## Contributing

First of all, thank you for contributing !

Here are few rules to follow for a easier code review before the maintainers accept and merge your request.

- you MUST follow the Symfony2 coding standard : you can use `./bin/coke` to validate
- you MUST run the test
- you MUST write or update tests
- you MUST write or update  documentation

## Running the test

Install the composer dev dependencies

```shell
$ composer install --dev
```

Then run the test with [atoum](https://github.com/atoum/atoum) unit test framework

```shell
./bin/atoum
```

