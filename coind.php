#!/usr/bin/php
<?php
/**
 * Copyright (c) 2014 Aurélien RICHAUD
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Created 31/03/14 16:05 by Aurélien RICHAUD
 */

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
use PhpCoinD\Protocol\Network;
use PhpCoinD\Storage\Store;
use PhpCoinD\Network\CoinNetworkSocketManager;

$GLOBALS['coind'] = array(
    'networks' => array(
        array(
            'network' => 'PhpCoinD\Protocol\Network\DogeCoin',
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
global $logger;
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
    /** @var $network Network */
    $network = new $coin_network_def['network']($logger);

    /** @var $store Store Build a store object */
    $store = new $coin_network_def['store']['class']($coin_network_def['store'], $network);
    $store->initializeStore();

    // Set the store of the network
    $network->setStore($store);

    $coin_networks[] = $network;
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
$logger->addInfo("Entering main loop...");
while(!$GLOBALS['shutdown']) {
    $start = microtime(true);

    // Run all networks
    foreach($coin_networks as $network) {
        $network->run();
    }

    $end = microtime(true);

    // No more than 2 loops every seconds
    if ($end-$start < 0.5) {
        usleep(0.5 - ($end-$start));
    }





    // Launch timed actions
    /*foreach($coin_networks as $coin_network) {
        try {
            $coin_network->doTimedActions();
        } catch (PeerNotReadyException $e) {
            /* Nothing to do except waiting */
        /*} catch (Exception $e) {
            // Log the exception
            $logger->addWarning($e);
        }
    }*/
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