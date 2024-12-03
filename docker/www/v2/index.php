<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanKind;




require __DIR__ . '/../vendor/autoload.php';

require('dice.php');
require('instrumentation.php');

$logger = new Logger('dice-server');
$logger->pushHandler(new StreamHandler('php://stdout', Level::Info));

$app = AppFactory::create();

$dice = new Dice();
$tracer = Globals::tracerProvider()->getTracer('slim-trace');

$app->get('/{version}/[{anything}]', function (Request $request, Response $response) use ($tracer, $dice) {
    $context = TraceContextPropagator::getInstance()->extract($request->getHeaders());
    $root = $tracer->spanBuilder('HTTP ' . $request->getMethod())
//        ->setStartTimestamp((int) ($request->getServerParams()['REQUEST_TIME_FLOAT'] * 1e9))
        ->setParent($context)
        ->setSpanKind(SpanKind::KIND_SERVER)
        ->startSpan();

    $params = $request->getQueryParams();

    if(isset($params['rolls'])) {
        $result = $dice->roll($params['rolls']);
        $response->getBody()->write(json_encode($result));
    } else {
        $response->withStatus(400)->getBody()->write("Please enter a number of rolls");
    }

    $root->end();

    return $response;
});

$app->run();
