<?php

use Workerman\Worker;

require_once 'vendor/autoload.php';

// #### http worker ####
$http_worker = new Worker('http://0.0.0.0:80');

// 4 processes
$http_worker->count = 64;
//require('dice.php');

//$dice = new Dice();

// Emitted when data received
$http_worker->onMessage = function ($connection, $request) use ($dice) {
    pinba_reset();
    //$request->get();
    //$request->post();
    //$request->header();
    //$request->cookie();
    //$request->session();
    //$request->uri();
    //$request->path();
    //$request->method();
 //   $params = $request->get();
 //   $result = $dice->roll($params['rolls']);
    // Send data to client
 //   $connection->send(json_encode($result));
    $connection->send("hello world");
    pinba_flush();
};

// Run all workers
Worker::runAll();