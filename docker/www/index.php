<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LogLevel;
use Slim\Factory\AppFactory;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanKind;


require __DIR__ . '/vendor/autoload.php';

require('dice.php');
require('instrumentation.php');

$logger = new Logger('dice-server');
$logger->pushHandler(new StreamHandler('php://stdout', Level::Info));

$app = AppFactory::create();

$dice = new Dice();

$app->get('/[{anything}]', function (Request $request, Response $response) use ($logger, $dice) {
    $context = TraceContextPropagator::getInstance()->extract($request->getHeaders());
    $root = $dice->tracer->spanBuilder('HTTP ' . $request->getMethod())
        ->setStartTimestamp((int) ($request->getServerParams()['REQUEST_TIME_FLOAT'] * 1e9))
        ->setParent($context)
        ->setSpanKind(SpanKind::KIND_SERVER)
        ->startSpan();

    $scope = $root->activate();

    $params = $request->getQueryParams();

    if(isset($params['rolls'])) {
        $result = $dice->roll($params['rolls']);
        if (isset($params['player'])) {
          $logger->info("A player is rolling the dice.", ['player' => $params['player'], 'result' => $result]);
        } else {
          $logger->info("Anonymous player is rolling the dice.", ['result' => $result]);
        }
        $response->getBody()->write(json_encode($result));
    } else {
        $response->withStatus(400)->getBody()->write("Please enter a number of rolls");
    }

    $root->end();
    $scope->detach();

    return $response;
});

$app->run();
