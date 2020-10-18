<?php

declare (strict_types = 1);

namespace tpr\server\library;

use Workerman\Connection\ConnectionInterface;
use Workerman\Worker;

class GlobalServer
{
    /**
     * Worker instance.
     *
     * @var worker
     */
    protected worker $_worker;

    /**
     * All data.
     *
     * @var array
     */
    protected array $_dataArray = [];

    public function data($key = null, $default = null)
    {

    }

    /**
     * Construct.
     *
     * @param string $ip
     * @param int    $port
     */
    public function __construct($ip = '0.0.0.0', $port = 2207)
    {
        $worker             = new Worker("frame://$ip:$port");
        $worker->count      = 1;
        $worker->name       = 'globalDataServer';
        $worker->onMessage  = array($this, 'onMessage');
        $worker->reloadable = false;
        $this->_worker      = $worker;
    }

    /**
     * onMessage.
     *
     * @param ConnectionInterface $connection
     * @param string              $buffer
     */
    public function onMessage(ConnectionInterface $connection, string $buffer): void
    {
        if ($buffer === 'ping') {
            return;
        }
        $data = unserialize($buffer);
        if (!$buffer || !isset($data['cmd']) || !isset($data['key'])) {
            $connection->close(serialize('bad request'));
            return;
        }
        $cmd = $data['cmd'];
        $key = $data['key'];
        switch ($cmd) {
            case 'get':
                if (!isset($this->_dataArray[$key])) {
                    $connection->send('N;');
                    return;
                }
                $connection->send(serialize($this->_dataArray[$key]));
                return;
            case 'set':
                $this->_dataArray[$key] = $data['value'];
                $connection->send('b:1;');
                break;
            case 'add':
                if (isset($this->_dataArray[$key])) {
                    $connection->send('b:0;');
                    return;
                }
                $this->_dataArray[$key] = $data['value'];
                $connection->send('b:1;');
                return;
            case 'increment':
                if (!isset($this->_dataArray[$key])) {
                    $connection->send('b:0;');
                    return;
                }
                if (!is_numeric($this->_dataArray[$key])) {
                    $this->_dataArray[$key] = 0;
                }
                $this->_dataArray[$key] = $this->_dataArray[$key] + $data['step'];
                $connection->send(serialize($this->_dataArray[$key]));
                return;
            case 'cas':
                $old_value = !isset($this->_dataArray[$key]) ? null : $this->_dataArray[$key];
                if (md5(serialize($old_value)) === $data['md5']) {
                    $this->_dataArray[$key] = $data['value'];
                    $connection->send('b:1;');
                    return;
                }
                $connection->send('b:0;');
                break;
            case 'delete':
                unset($this->_dataArray[$key]);
                $connection->send('b:1;');
                break;
            default:
                $connection->close(serialize('bad cmd ' . $cmd));
        }
    }
}