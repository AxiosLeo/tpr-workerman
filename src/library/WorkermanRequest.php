<?php

declare(strict_types=1);

namespace tpr\server\library;

use tpr\core\request\RequestAbstract;
use tpr\core\request\RequestInterface;
use tpr\Event;
use Workerman\Protocols\Http\Request;
use Workerman\Worker;

class WorkermanRequest extends RequestAbstract implements RequestInterface
{
    private Request $request;

    private ConfigModel $config;

    public function __construct(Request $request, ConfigModel $config)
    {
        $this->request = $request;
        $this->config  = $config;
    }

    public function post($name = null, $default = null)
    {
        return $this->request->post($name, $default);
    }

    public function put($name = null, $default = null)
    {
        $put = $this->getRequestData('put', function () {
            if ('json' === $this->contentType()) {
                $put = (array) json_decode($this->content(), true);
            } else {
                parse_str($this->content(), $put);
            }

            return $this->setRequestData('put', $put);
        });

        return $this->input($put, $name, $default);
    }

    public function content(): string
    {
        return $this->request->rawBody();
    }

    public function contentType(): string
    {
        return $this->getRequestData('content_type', function () {
            $mimes       = new \Mimey\MimeTypes();
            $contentType = $this->request->header('content-type');
            if ($contentType) {
                if (strpos($contentType, ';')) {
                    $tmp  = explode(';', $contentType);
                    $type = $tmp[0];
                    unset($tmp);
                } else {
                    $type = $contentType;
                }

                $contentType = $mimes->getExtension(trim($type));
                unset($type);
            }
            unset($mimes);

            return $this->setRequestData('content_type', $contentType);
        });
    }

    public function get($name = null, $default = null)
    {
        return $this->request->get();
    }

    public function time($format = null, $micro = false)
    {
        return $this->getRequestData('time', function () use ($micro, $format) {
            $time = $micro ? microtime(true) : time();

            return null === $format ? $time : date($format, $time);
        });
    }

    /**
     * @param null $name
     *
     * @deprecated
     */
    public function server($name = null): string
    {
        return '';
    }

    public function pathInfo(): string
    {
        return $this->getRequestData('path_info', function () {
            return parse_url($this->request->uri(), \PHP_URL_PATH);
        });
    }

    public function input($array, $name = null, $default = null)
    {
        if (null === $name) {
            return $array;
        }
        $value = isset($array[$name]) ? $array[$name] : $default;
        $data  = ['name' => $name, 'value' => $value];
        Event::listen('filter_request_data', $data);

        return $data['value'];
    }

    public function request($name = null, $default = null)
    {
        $request = $this->getRequestData('request', function () {
            return $this->setRequestData('request', $_REQUEST);
        });

        return $this->input($request, $name, $default);
    }

    public function isHttps(): bool
    {
        return 'https' === $this->scheme();
    }

    public function scheme(): string
    {
        return $this->config->protocol;
    }

    public function header($name = null, $default = null)
    {
        return $this->input($this->request->header(), $name, $default);
    }

    public function file($name = null)
    {
        return $this->request->file($name);
    }

    public function query(): string
    {
        return $this->request->queryString();
    }

    public function env(): string
    {
        return 'Workerman/' . Worker::VERSION;
    }

    public function host(): string
    {
        return $this->getRequestData('host', function () {
            $host = $this->request->host();
            $tmp  = explode(':', $host, 2);

            return $tmp[0];
        });
    }

    public function port(): int
    {
        return $this->getRequestData('port', function () {
            $host = $this->request->host();
            $tmp  = explode(':', $host, 2);

            return (int) $tmp[1];
        });
    }

    public function indexFile(): string
    {
        return $this->getRequestData('index_file', function () {
            $uri = $this->request->uri();

            return parse_url($uri, \PHP_URL_PATH);
        });
    }

    public function userAgent(): string
    {
        return $this->header('user-agent');
    }

    public function accept(): string
    {
        return $this->header('accept');
    }

    public function lang(): string
    {
        return $this->header('lang');
    }

    public function encoding(): string
    {
        return $this->header('encoding');
    }

    public function method(): string
    {
        return $this->request->method();
    }

    public function url($is_whole = false): string
    {
        if ($is_whole) {
            $url = $this->scheme() . '://' . $this->host();
            $url .= ':' . (string) ($this->port());

            return $url . $this->request->uri();
        }

        return $this->request->uri();
    }
}
