<?php

declare (strict_types = 1);

namespace tpr\server\workerman\model;

use tpr\Model;

class ConfigModel extends Model
{
    public string $app_listen = "http://0.0.0.0:2346";

    public array $worker_options = [];

    public int $worker = 4;

    public string $global_listen = "0.0.0.0:2207";

    public array $context = [];
}