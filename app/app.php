<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

/**
 * Add your routes here
 */
$app->get('/', function () {
    echo $this['view']->render('index');
});

/**
 * API
 */
$APICollection = new \Phalcon\Mvc\Micro\Collection();
$APICollection->setHandler('APIController', true);
$APICollection->setPrefix('/API');
$APICollection->get('/crawl', 'crawlAction');
$app->mount($APICollection);

/**
 * Not found handler
 */
$app->notFound(function () use($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
});
