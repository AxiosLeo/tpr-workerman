<?php

namespace tpr\server\workerman;

use tpr\App;
use tpr\Config;
use tpr\Container;
use tpr\core\Dispatch;
use tpr\core\Response;
use tpr\Event;
use tpr\exception\HttpResponseException;
use tpr\Files;
use tpr\Path;
use tpr\server\library\ConfigModel;
use tpr\server\library\Connections;
use tpr\server\library\HttpRequest;
use tpr\server\ServerHandler;
use Workerman\Connection\ConnectionInterface;
use Workerman\Protocols\Http\Request;
use Workerman\Timer;
use Workerman\Worker;

class WorkermanServer extends ServerHandler
{
    private ?ConfigModel $config = null;

    public function receive(ConnectionInterface $connection, Request $request)
    {
        $req = new HttpRequest($request, $this->config);
        Container::bindWithObj('request', $req);
        Container::bind('response', Response::class);
        Event::trigger('app_response_before');

        try {
            Container::dispatch()->run();
        } catch (HttpResponseException $e) {
            $this->send($connection, $e);
        } catch (\Exception $e) {
            try {
                $response = Container::response();
                if (App::debugMode()) {
                    $response->response([
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile() . ':' . $e->getLine(),
                        'trace'   => $e->getTraceAsString(),
                    ], 500, 'server error');
                } else {
                    $response->response(['message' => $e->getMessage(), 'code' => $e->getCode()], 500, 'server error');
                }
            } catch (HttpResponseException $ex) {
                $this->send($connection, $ex);
            }
        }
        Container::delete('request');
        Container::delete('response');
    }

    protected function cgi(): void
    {
        throw new \Exception('starting the worker server on CGI mode is not supported.');
    }

    protected function cli(string $command_name = null): void
    {
        // init worker
        $url           = $this->config->protocol . '://' . $this->config->host . ':' . (string)($this->config->port);
        $worker        = new Worker($url, $this->config->context);
        $worker->count = $this->config->worker;
        if (!empty($this->config->options)) {
            foreach ($this->config->options as $k => $v) {
                $worker->{$k} = $v;
            }
        }
        // GlobalData Server
        new Server($this->config->host, $this->config->port_global);
        Event::trigger('worker_init', $worker);

        // clear cache
        Files::remove(Path::cache());

        $dispatch = new Dispatch($this->app->namespace);
        Container::bindNXWithObj('cgi_dispatch', $dispatch);

        $worker->onWorkerStart = function (Worker $worker) {
            Event::trigger('worker_start', $worker);
            global $global;
            $global = new \GlobalData\Client($this->config->host . ':' . $this->config->port_global);
        };

        $worker->onWorkerReload = function (Worker $worker) {
            Event::trigger('worker_reload', $worker);
        };

        $worker->onConnect = function (ConnectionInterface $connection) {
            Event::trigger('worker_connect', $connection);
        };

        $worker->onClose = function (ConnectionInterface $connection) {
            Event::trigger('worker_close', $connection);
        };

        $worker->onBufferFull = function (ConnectionInterface $connection) {
            Event::trigger('worker_buffer_full', $connection);
        };

        $worker->onBufferDrain = function (ConnectionInterface $connection) {
            Event::trigger('worker_buffer_brain', $connection);
        };

        $worker->onError = function (ConnectionInterface $connection, $code, $msg) {
            Event::trigger('worker_error', $connection, $code, $msg);
        };

        // listen request
        switch ($this->config->protocol) {
            case 'http':
                Event::registerWithObj('worker_message', $this, 'receive');
                $worker->onMessage = function (ConnectionInterface $connection, Request $request) {
                    Event::trigger('worker_message_http', $connection, $request);
                };
                break;
            case 'websocket':
                $worker->onMessage = function (ConnectionInterface $connection, $request) {
                    Event::trigger('worker_message_ws', $connection, $request);
                };
                break;
            default:
                $worker->onMessage = function (ConnectionInterface $connection, $request) {
                    Event::trigger('worker_message_' . (string)$this->config->protocol, $connection, $request);
                };
        }


        // run worker
        Worker::runAll();
    }

    protected function begin(): void
    {
        Event::trigger('app_begin', $this->app);
        Config::load(Path::config());
        $this->config = new ConfigModel();
        if ($this->app->server_options) {
            $this->config->unmarshall($this->app->server_options);
        }
        $this->app->cache_time = 0;
    }

    protected function end(): void
    {
        Event::trigger('app_end', $this->app);
    }

    private function send(ConnectionInterface $connection, HttpResponseException $e)
    {
        $response = new \Workerman\Protocols\Http\Response();
        $response->withHeaders($e->headers);
        $response->withBody($e->result);
        $response->withStatus($e->http_status);
        $connection->send($response);
        Event::trigger('app_response_after', $e->result);
    }
}
