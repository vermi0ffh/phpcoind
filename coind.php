#!/usr/bin/php
<?php

/////////////////////////////////////////////////
// Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';


/////////////////////////////////////////////////
// Util functions
require_once __DIR__ . '/Vermi0ffh/Coin/Util/functions.php';


/////////////////////////////////////////////////
// Coind config
use Aza\Components\Socket\SocketStream;
use Monolog\Logger;
use Vermi0ffh\Coin\Peer;

$GLOBALS['coind'] = array(
    // Network binding : IPv4 + IPv6, allow everybody to connect
    'coin_network' => 'dogecoin',
    'coin_binds' => array(
        'tcp://0.0.0.0:2500',
        'tcp://[::1]:2500',
    ),
    'rpc_binds' => array(
        array(
            'domain' => AF_INET,
            'hostname' => '127.0.0.1',
            'port' => 2527,
        ),
    ),
    'bootstrap' => array(
        'tcp://127.0.0.1:22556',
    ),
    'logger' => array(
        array(
            'type' => 'StreamHandler',
            'filename' => 'coind.log',
            'level' => Logger::INFO,
        ),
    ),
);


/////////////////////////////////////////////////
// Magic Values of coin networks
$GLOBALS['magic_values'] = array(
    'main' => 0xd9b4bef9,
    'testnet' => 0xdab5bffa,
    'testnet3' => 0x0709110b,
    'amecoin' => 0xfeb4bef9,
    'bitcoin' => 0xd9b4bef9,
    'bitcoin_testnet' => 0xdab5bffa,
    'bitcoin_testnet3' => 0x0709110b,
    'namecoin' => 0xfeb4bef9,
    'litecoin' => 0xdbb6c0fb,
    'litecoin_testnet' => 0xdcb7c1fc,
    'dogecoin' => 0xc0c0c0c0,
);

$GLOBALS['genesis_block'] = array(
    'dogecoin' => 0x1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691,
);

$GLOBALS['protocol_version'] = array(
    'dogecoin' => 70002,
);

$GLOBALS['client_version'] = array(
    'dogecoin' => 1 * 1000000 + 6 * 10000 + 0 * 100 + 0,
);


/////////////////////////////////////////////////
// Init logger
$GLOBALS['logger'] = new Logger('name');

// Add log methods
foreach($GLOBALS['coind']['logger'] as $logger_config) {
    switch($logger_config['type']) {
        case 'StreamHandler':
            $GLOBALS['logger']->pushHandler(new \Monolog\Handler\StreamHandler($logger_config['filename'], $logger_config['level']));
            break;

        default:
    }
}



/////////////////////////////////////////////////
// Startup
$GLOBALS['logger']->addInfo("System startup...");

// Create nonce token
$GLOBALS['nonce'] = rand(0, 100000);

$GLOBALS['coin_sockets'] = array();
foreach($GLOBALS['coind']['coin_binds'] as $url) {
    $GLOBALS['coin_sockets'][] = SocketStream::server($url);
}

if (count($GLOBALS['coin_sockets']) == 0) {
    exit();
}


// Container for peers
$peers = array();


/////////////////////////////////////////////////
// Boucle principale
declare(ticks=1);
$GLOBALS['shutdown'] = false;
pcntl_signal(SIGTERM, function($signo) {
    $GLOBALS['shutdown'] = true;
});

$GLOBALS['logger']->addInfo("Connecting to bootstrap peers...");
foreach($GLOBALS['coind']['bootstrap'] as $url) {
    $GLOBALS['logger']->addInfo(" - " . $url);
    try {
        $peer = Peer::connect($url);
        $peer->run();

        $peers[] = $peer;
    } catch(Exception $e) {
        $GLOBALS['logger']->addWarning($e);
    }
}


$GLOBALS['logger']->addInfo("Entering main loop...");
while(!$GLOBALS['shutdown']) {
    $sockets = array(
        'read' => array(),
        'write' => array()
    );

    /** @var $socket SocketStream */
    foreach($GLOBALS['coin_sockets'] as $socket) {
        $sockets['read'][] = $socket->resource;
    }

    $nb_socks = stream_select($sockets['read'], $sockets['write'], $sockets['write'], 0, 100);

    // Sockets have moved !
    if ($nb_socks > 0) {
        /** @var $socket SocketStream */
        foreach($sockets['read'] as $socket) {
            $GLOBALS['logger']->addInfo("New connection detected");

            $clisock = $socket->accept();
            $peer = new Peer($clisock);
            $peer->run();

            $peers[] = $peer;
        }
    }
}


/////////////////////////////////////////////////
// Shutdown procedure
$GLOBALS['logger']->addInfo("Shutdown...");

// Close sockets
/** @var $socket SocketStream */
foreach($GLOBALS['coin_sockets'] as $socket) {
    $socket->close();
}

// Kill peers
/** @var $peer Peer */
foreach($peers as $peer) {
    posix_kill($peer->getPid(), SIGTERM);
    $status = 0;
    pcntl_waitpid($peer->getPid(), $status);
}
exit();