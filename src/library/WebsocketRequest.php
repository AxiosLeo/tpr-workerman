<?php

declare (strict_types = 1);

namespace tpr\server\library;

use tpr\core\request\RequestInterface;
use tpr\Model;
use tpr\models\RouteInfoModel;

class WebsocketRequest extends Model implements RequestInterface
{
    public function uuid(): string
    {
        // TODO: Implement uuid() method.
    }

    public function url($is_whole = false): string
    {
        // TODO: Implement url() method.
    }

    public function contentType(): string
    {
        // TODO: Implement contentType() method.
    }

    public function query(): string
    {
        // TODO: Implement query() method.
    }

    public function param($name = null, $default = null)
    {
        // TODO: Implement param() method.
    }

    public function get($name = null, $default = null)
    {
        // TODO: Implement get() method.
    }

    public function post($name = null, $default = null)
    {
        // TODO: Implement post() method.
    }

    public function put($name = null, $default = null)
    {
        // TODO: Implement put() method.
    }

    public function request($name = null, $default = null)
    {
        // TODO: Implement request() method.
    }

    public function content(): string
    {
        // TODO: Implement content() method.
    }

    public function time($format = null, $micro = false)
    {
        // TODO: Implement time() method.
    }

    public function server($name = null)
    {
        // TODO: Implement server() method.
    }

    public function pathInfo(): string
    {
        // TODO: Implement pathInfo() method.
    }

    public function routeInfo(?RouteInfoModel $routeInfo = null)
    {
        // TODO: Implement routeInfo() method.
    }

    public function scheme(): string
    {
        // TODO: Implement scheme() method.
    }

    public function header($name = null, $default = null)
    {
        // TODO: Implement header() method.
    }

    public function file($name = null)
    {
        // TODO: Implement file() method.
    }

    public function method(): string
    {
        // TODO: Implement method() method.
    }

    public function env(): string
    {
        // TODO: Implement env() method.
    }

    public function host(): string
    {
        // TODO: Implement host() method.
    }

    public function port(): int
    {
        // TODO: Implement port() method.
    }

    public function indexFile(): string
    {
        // TODO: Implement indexFile() method.
    }

    public function userAgent(): string
    {
        // TODO: Implement userAgent() method.
    }

    public function accept(): string
    {
        // TODO: Implement accept() method.
    }

    public function lang(): string
    {
        // TODO: Implement lang() method.
    }

    public function encoding(): string
    {
        // TODO: Implement encoding() method.
    }

    public function isHttps(): bool
    {
        // TODO: Implement isHttps() method.
    }

    public function isGet(): bool
    {
        // TODO: Implement isGet() method.
    }

    public function isPost(): bool
    {
        // TODO: Implement isPost() method.
    }

    public function isPut(): bool
    {
        // TODO: Implement isPut() method.
    }

    public function isDelete(): bool
    {
        // TODO: Implement isDelete() method.
    }

    public function isHead(): bool
    {
        // TODO: Implement isHead() method.
    }

    public function isPatch(): bool
    {
        // TODO: Implement isPatch() method.
    }

    public function isOptions(): bool
    {
        // TODO: Implement isOptions() method.
    }
}