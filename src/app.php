<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

use App\Application;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$cache = new FilesystemCache(__DIR__ . '/../var/cache');

$app = new Application([
    'cache' => function () use ($cache) {
        return $cache;
    },
]);

$app->before(function(Request $request, Application $app) {

    if (in_array($request->getPathInfo(), ['/login', '/login/renew']) || $request->isMethod(Request::METHOD_OPTIONS)) {

        return;
    }
    elseif (in_array($request->getPathInfo(), ['/books', '/books/{id}', '/authors', '/authors/{id}',  '/orders/{id}']) || $request->isMethod('GET')) {

        return;
    }
    elseif (in_array($request->getPathInfo(), ['/orders']) || $request->isMethod('POST')) {

        return;
    }
    else{
        if (!$request->headers->get('Authorization')) {

            return new Response('Permission denied', Response::HTTP_UNAUTHORIZED);
        } else {
            $jwtService = $app['jwt.service'];
            $token = str_replace('Bearer ', '', $request->headers->get('Authorization'));

            if (!$jwtService->validateToken($token)) {
                return new Response('Permission denied', Response::HTTP_UNAUTHORIZED);
            }
        }
    }

});

$app->error(function (\App\Exception\ApiProblemException $e, Request $request, $code) {
    return new \Symfony\Component\HttpFoundation\JsonResponse($e->toArray(), $e->getStatusCode(), [
        'Content-Type' => 'application/problem+json'
    ]);
});

return $app;
