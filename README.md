# tpr-workerman

> Use workerman with tpr framework

## Require

- PHP >= 7.4

## Install

```bash
composer require axios/tpr-workman
```

## Usage

```php
<?php

declare(strict_types = 1);

namespace demo;

require_once __DIR__ . '/vendor/autoload.php';

use tpr\App;
use tpr\Event;
use tpr\Path;
use tpr\server\WorkermanServer;
use Workerman\Worker;

Path::configurate([
    'root' => __DIR__,
]);

App::debugMode(false);

App::registerServer('workerman', WorkermanServer::class);

Event::on('worker_init', function (Worker $worker) {
    // handle worker object in here
});

App::drive('workerman')
    ->config([
        'namespace'       => 'app',           // app base namespace, ### this is required ###
        'lang'            => 'zh-cn',         // default language set name
        'cache_time'      => 60,              // global cache time for config&route data
        'force_route'     => false,           // forces use routing
        'remove_headers'  => [],              // remove some header before send response
        // for ServerHandler custom config.
        'server_options'  => [
            'protocol' => 'http', // support websocket | http | tcp | other custom protocol
            'host'     => '0.0.0.0',
            'port'     => 2346,
            'worker'   => 4,      // the number of worker process
            'context'  => [],     // for enable ssl : https://github.com/walkor/Workerman#enable-ssl
            'options'  => [],     // properties of worker. https://github.com/walkor/workerman-manual/blob/master/english/src/worker-development/name.md
        ],
        'response_config' => [],              // response config, see detail on 	pr\models\ResponseModel.

        'default_content_type_cgi'  => 'html', // default content-type on cgi mode
        'default_content_type_ajax' => 'json', // default content-type on api request
        'default_content_type_cli'  => 'text', // default content-type on command line mode

        'dispatch_rule' => '{app_namespace}\{module}\controller\{controller}',  // controller namespace spelling rule
    ])
    ->run();

```

## Events

|event name | params |
|:---:|:---:|
|worker_init|(Workerman\Worker $worker)|
|worker_start|(Workerman\Worker $worker)|
|worker_reload|(Workerman\Worker $worker)|
|worker_connect|(ConnectionInterface $connection)|
|worker_close|(ConnectionInterface $connection)|
|worker_buffer_full|(ConnectionInterface $connection)|
|worker_buffer_brain|(ConnectionInterface $connection) |
|worker_error|(ConnectionInterface $connection, $code, $msg)|
|worker_message|(ConnectionInterface $connection, Request $request)|

## License

The TPR framework is open-sourced software licensed under the [MIT](LICENSE).
