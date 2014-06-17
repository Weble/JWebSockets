<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// Load FOF
include_once JPATH_LIBRARIES.'/fof/include.php';
include_once dirname(__FILE__) . '/pusher.php';

class WsWorkerApp implements MessageComponentInterface {

    private static $application = null;

    private static $pusher = null;

    private static $connections = array();
    private static $activeConn = null;

	public function __construct($app = null) {
		static $wsdispatcher;
        
        self::$application = $app;
	}

    public function onOpen(ConnectionInterface $conn) {
        self::$connections[] = $conn;
    }

    public static function getConnections() {
        return self::$connections;
    }

    public static function getActiveConnection() {
        return self::$activeConn;
    }

    public static function getApplication() {
        return self::$application;
    }

    public function onMessage(ConnectionInterface $from, $msg) {

    	$input = new FOFInput(json_decode($msg));
        self::$activeConn = $from;

        try {
            FOFDispatcher::getTmpInstance(null, null, array('input' => $input))->dispatch();
    	} catch(Exception $e) {
            echo $e;
    	}

        self::$activeConn = null;
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}