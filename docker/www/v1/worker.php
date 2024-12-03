<?php


require "vendor/autoload.php";
require "v1/dice.php";

use Spiral\RoadRunner;
use Nyholm\Psr7;

$worker = RoadRunner\Worker::create();
$psrFactory = new Psr7\Factory\Psr17Factory();

$worker = new RoadRunner\Http\PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

$dice = new Dice();

while ($request = $worker->waitRequest()) {
    try {
        $response = new Psr7\Response();

        $params = $request->getQueryParams();

        if(isset($params['rolls'])) {
            $result = $dice->roll($params['rolls']);
            $response->getBody()->write(json_encode($result));
        } else {
            $response->withStatus(400)->getBody()->write("Please enter a number of rolls");
        }
    
        $worker->respond($response);
    } catch (\Throwable $e) {
        $worker->getWorker()->error((string)$e);
    }
}