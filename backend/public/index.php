<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Router;
use Phalcon\Http\Response;
use Phalcon\Db\Adapter\Pdo\Postgresql;

// Incluir autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Crear contenedor de inyección de dependencias
$container = new FactoryDefault();

// Cargar configuración
$config = require __DIR__ . '/../config/config.php';

// Registrar configuración en el contenedor
$container->set('config', $config);

// Registrar servicio de base de datos
$container->set('db', function () use ($config) {
    return new Postgresql($config['database']);
});

// Registrar router
$container->set('router', function () {
    $router = new Router(false);
    
    // Habilitar notFound como controlador
    $router->removeExtraSlashes(true);
    
    // API Routes - Autenticación
    $router->addPost('/api/register', [
        'controller' => 'auth',
        'action'     => 'register'
    ]);
    
    $router->addPost('/api/login', [
        'controller' => 'auth',
        'action'     => 'login'
    ]);
    
    // API Routes - Tareas (protegidas con JWT)
    $router->addGet('/api/tasks', [
        'controller' => 'tasks',
        'action'     => 'index'
    ]);
    
    $router->addPost('/api/tasks', [
        'controller' => 'tasks',
        'action'     => 'create'
    ]);
    
    $router->addPut('/api/tasks/{id}', [
        'controller' => 'tasks',
        'action'     => 'update'
    ]);
    
    // Health check
    $router->addGet('/api/health', [
        'controller' => 'health',
        'action'     => 'check'
    ]);
    
    return $router;
});

try {
    // Crear aplicación
    $application = new Application();
    $application->setDI($container);
    
    // Ejecutar
    $response = $application->handle($_SERVER['REQUEST_URI']);
    
    // Enviar respuesta
    $response->send();
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Internal Server Error',
        'details' => $_ENV['APP_DEBUG'] ? $e->getMessage() : null
    ]);
}
