#!/usr/bin/php
<?php

/////////////////////////////////////////////////
// Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';


/////////////////////////////////////////////////
// Util functions
require_once __DIR__ . '/PhpCoinD/Protocol/Util/functions.php';


/////////////////////////////////////////////////
// Coind config
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PhpCoinD\Exception\PeerNotReadyException;
use PhpCoinD\Protocol\Network\DogeCoin;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Storage\Store;
use PhpCoinD\Network\AsyncSocket;
use PhpCoinD\Network\CoinNetworkSocketManager;

$GLOBALS['coind'] = array(
    'networks' => array(
        array(
            'network' => new DogeCoin(),
            'store' => array(
                'class' => 'PhpCoinD\Storage\Impl\MongoStore',
                'url' => 'mongodb://localhost:27017/dogecoin',
                'db' => 'dogecoin',
            ),
        )
    ),
    'logger' => array(
        array(
            'type' => 'StreamHandler',
            'filename' => 'coind.log',
            'level' => Logger::INFO,
        ),
        array(
            'type' => 'StreamHandler',
            'filename' => 'php://output',
            'level' => Logger::DEBUG,
        ),
    ),
);


/////////////////////////////////////////////////
// Init logger
$logger = new Logger('name');

// Add log methods
foreach($GLOBALS['coind']['logger'] as $logger_config) {
    switch($logger_config['type']) {
        case 'StreamHandler':
            $logger->pushHandler(new StreamHandler($logger_config['filename'], $logger_config['level']));
            break;

        default:
    }
}


/////////////////////////////////////////////////
// Startup
$logger->addInfo("System startup...");


/** @var $coin_networks CoinNetworkSocketManager[] */
$coin_networks = array();

foreach($GLOBALS['coind']['networks'] as $coin_network_def) {
    /** @var $store Store Build a store object */
    $store = new $coin_network_def['store']['class']($coin_network_def['store']);

    /** @var $network Network */
    $network = $coin_network_def['network'];
    $network->setStore($store);

    $coin_networks[] = new CoinNetworkSocketManager($network, $logger);
}


/////////////////////////////////////////////////
// Signal handler
declare(ticks=1);
$GLOBALS['shutdown'] = false;
pcntl_signal(SIGTERM, function($signo) {
    $GLOBALS['shutdown'] = true;
});

/////////////////////////////////////////////////
// Main loop
$logger->addInfo("Connecting to bootstrap peers...");
foreach($coin_networks as $coin_network) {
    // TODO : Static bootstrap for dev only
    $coin_network->bootstrap(array(
        'tcp://127.0.0.1:22556',
        'tcp://192.168.42.128:22556',
    ));
}


$logger->addInfo("Entering main loop...");
while(!$GLOBALS['shutdown']) {
    // Prepare sockets arrays for stream_select
    $read_sockets = array();
    $write_sockets = array();
    $close_sockets = array();


    /** @var $network_sockets AsyncSocket[] */
    $sockets_objects = array();
    /** @var $all_sockets resource[] */
    $all_sockets = array();
    /** @var $all_sockets resource[] */
    $all_write_sockets = array();

    // We manage all coin networks
    foreach($coin_networks as $coin_network) {
        /** @var $all_sockets AsyncSocket[] */
        $network_sockets = array_merge($coin_network->getServerSockets(), $coin_network->getPeers());

        // Translate sockets structure for stream_select
        foreach($network_sockets as $async_socket) {
            $sockets_objects[] = $async_socket;
            $all_sockets[] = $async_socket->getSocketResource();

            // We check for write only socket with writes pending ! Else stream_select will go nuts !
            if ($async_socket->hasWritePending()) {
                $all_write_sockets[] = $async_socket->getSocketResource();
            }
        }
    }

    // Duplicates array (because stream_select change arrays)
    $sockets = array(
        'read' => $all_sockets,
        'write' => $all_write_sockets,
        'close' => $all_sockets,
    );

    // Check all sockets for read, write or error (wait .5 seconds max)
    $nb_socks = stream_select($sockets['read'], $sockets['write'], $sockets['close'], 0, 500);

    // Sockets have moved !
    if ($nb_socks > 0) {
        /////////////////////////////////////////
        // Read events
        foreach($sockets['read'] as $socket) {
            $socket_id = array_search($socket, $all_sockets);

            if ($socket_id === false) {
                $logger->addWarning("Can't find a socket after stream_select : " . $socket);
            } else {
                /** @var $sockets_object AsyncSocket */
                $sockets_object = $sockets_objects[$socket_id];
                $sockets_object->onRead();
            }
        }

        /////////////////////////////////////////
        // Write events
        foreach($sockets['write'] as $socket) {
            $socket_id = array_search($socket, $all_sockets);

            if ($socket_id === false) {
                $logger->addWarning("Can't find a socket after stream_select : " . $socket);
            } else {
                /** @var $sockets_object AsyncSocket */
                $sockets_object = $sockets_objects[$socket_id];
                $sockets_object->onWrite();
            }
        }


        /////////////////////////////////////////
        // Close events
        foreach($sockets['close'] as $socket) {
            $socket_id = array_search($socket, $all_sockets);

            if ($socket_id === false) {
                $logger->addWarning("Can't find a socket after stream_select : " . $socket);
            } else {
                /** @var $sockets_object AsyncSocket */
                $sockets_object = $sockets_objects[$socket_id];
                $sockets_object->onClose();
            }
        }
    }


    // Launch timed actions
    foreach($coin_networks as $coin_network) {
        try {
            $coin_network->doTimedActions();
        } catch (PeerNotReadyException $e) {
            /* Nothing to do except waiting */
        } catch (Exception $e) {
            // Log the exception
            $logger->addWarning($e);
        }
    }
}


/////////////////////////////////////////////////
// Shutdown procedure
$logger->addInfo("Shutdown...");

// Close sockets
foreach($coin_networks as $coin_network) {
    try {
        $coin_network->shutdown();
    } catch (Exception $e) {
        $logger->addWarning($e);
    }
}
exit();