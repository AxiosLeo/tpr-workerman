<?php

declare(strict_types = 1);

namespace tpr\server\library;

use tpr\Model;

class ConfigModel extends Model
{
    /**
     * websocket | http | tcp | custom.
     */
    public string $protocol = 'http';

    public string $host = '0.0.0.0';

    public int $port = 2346;

    public int $port_global = 2207;

    public int $worker = 4;

    public array $options = [];

    public array $context = [];
}
