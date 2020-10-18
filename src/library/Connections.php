<?php

declare (strict_types = 1);

namespace tpr\server\library;

use Workerman\Connection\ConnectionInterface;

class Connections
{
    private static array $connections = [];

    public static function add(string $id, ConnectionInterface $connection)
    {
        self::$connections[$id] = $connection;
    }

    public static function count()
    {
        return count(self::$connections);
    }
}