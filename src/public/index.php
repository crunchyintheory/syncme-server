<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../classes/Mapper.php';
require __DIR__ . '/../classes/TabEntity.php';
require __DIR__ . '/../classes/TabMapper.php';

// Database auth credentials should be set here
require_once(__DIR__ . '/../classes/DBSetup.php');

$config['displayErrorDetails'] = true;

$app = new \Slim\App(['settings' => $config]);
$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['view'] = new \Slim\Views\PhpRenderer('../templates');

$app->get('/tabs/list[/{param}]', function (Request $request, Response $response, array $args) {
    $mapper = new TabMapper($this->db);
    $tabs = $mapper->getTabs();
    
    $lite = false;
    if(array_key_exists('param', $args)) {
        $lite = $args['param'] == 'lite'; 
    }

    $response = $this->view->render($response, 'tabs/list.phtml', ["tabs" => $tabs, "lite" => $lite]);

    return $response;
});

$app->post('/tabs/add', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $tab_data = [];

    $tab_data['url'] = filter_var($data['url'], FILTER_SANITIZE_STRING);
    $tab_data['date'] = filter_var($data['date'], FILTER_SANITIZE_STRING);
    $tab_data['host'] = filter_var($data['host'], FILTER_SANITIZE_STRING);
    $tab_data['timestamp'] = filter_var($data['timestamp'], FILTER_SANITIZE_STRING);
    $tab_data['icon'] = filter_var($data['icon'], FILTER_SANITIZE_STRING);
    $tab_data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);

    $tab = new TabEntity($tab_data, true);
    $tab_mapper = new TabMapper($this->db);
    $tab_mapper->save($tab);

    $response = $response->withStatus(201);
    return $response;
});

$app->post('/tabs/delete', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $url = filter_var($data['url'], FILTER_SANITIZE_STRING);

    $tab_mapper = new TabMapper($this->db);
    $tab_mapper->deleteURL($url);

    if(array_key_exists('lite', $data) && $data['lite']) {
        $response = $response->withRedirect('/tabs/list/lite');
    }
    else {
        $response = $response->withRedirect('/tabs/list');
    }
    return $response;
});

$app->run();
?>