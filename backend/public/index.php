<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\AuthService;
use App\Services\InputValidator;
use App\Middleware\JwtMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Models\Task;

// Load environment variables
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create Slim app
$app = AppFactory::create();

// Add rate limiting middleware (100 requests per 60 seconds)
$app->add(new RateLimitMiddleware(100, 60));

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// CORS: respond to preflight and add CORS headers
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    $origin = $_ENV['CORS_ORIGIN'] ?? '*';
    return $response
        ->withHeader('Access-Control-Allow-Origin', $origin)
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// Database connection helper
function getDBConnection() {
    try {
        $dsn = sprintf(
            'pgsql:host=%s;dbname=%s;port=%s',
            $_ENV['DB_HOST'] ?? 'localhost',
            $_ENV['DB_NAME'] ?? 'task_manager_db',
            $_ENV['DB_PORT'] ?? '5432'
        );
        return new PDO($dsn, $_ENV['DB_USER'] ?? 'task_manager_user', $_ENV['DB_PASSWORD'] ?? '');
    } catch (PDOException $e) {
        error_log('DB Connection Error: ' . $e->getMessage());
        return null;
    }
}

// JWT helper functions
// JWT helpers are provided by App\Services\AuthService

function sendJson(Response $response, $data, $status = 200) {
    $response->getBody()->write(json_encode($data));
    return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
}

// Public Routes

// Register
$app->post('/api/register', function (Request $request, Response $response) {
    $bodyValidation = InputValidator::validateJsonBody((string)$request->getBody());
    if (!$bodyValidation['valid']) {
        return sendJson($response, ['error' => $bodyValidation['error']], 400);
    }

    $data = $bodyValidation['data'];

    // Validate required fields
    $requiredCheck = InputValidator::validateRequiredFields($data, ['email', 'password', 'name']);
    if (!$requiredCheck['valid']) {
        return sendJson($response, ['error' => 'Missing required fields: ' . implode(', ', $requiredCheck['missing'])], 400);
    }

    // Validate email
    if (!InputValidator::isValidEmail($data['email'])) {
        return sendJson($response, ['error' => 'Invalid email format'], 400);
    }

    // Validate name
    $nameValidation = InputValidator::validateName($data['name']);
    if (!$nameValidation['valid']) {
        return sendJson($response, ['error' => $nameValidation['error']], 400);
    }

    // Validate password strength
    $passwordValidation = InputValidator::validatePassword($data['password']);
    if (!$passwordValidation['valid']) {
        return sendJson($response, ['errors' => $passwordValidation['errors']], 400);
    }

    $db = getDBConnection();
    if (!$db) {
        return sendJson($response, ['error' => 'Database connection failed'], 500);
    }

    try {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return sendJson($response, ['error' => 'Email already registered'], 409);
        }

        $passwordHash = AuthService::hashPassword($data['password']);
        $stmt = $db->prepare('INSERT INTO users (email, password_hash, name, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$data['email'], $passwordHash, $data['name']]);

        $userId = $db->lastInsertId();
        $token = AuthService::generateToken($userId);

        return sendJson($response, [
            'id' => (int)$userId,
            'email' => $data['email'],
            'name' => $data['name'],
            'token' => $token
        ], 201);
    } catch (\Exception $e) {
        error_log('Register Error: ' . $e->getMessage());
        return sendJson($response, ['error' => 'Registration failed'], 500);
    }
});

// Login
$app->post('/api/login', function (Request $request, Response $response) {
    $bodyValidation = InputValidator::validateJsonBody((string)$request->getBody());
    if (!$bodyValidation['valid']) {
        return sendJson($response, ['error' => $bodyValidation['error']], 400);
    }

    $data = $bodyValidation['data'];

    // Validate required fields
    $requiredCheck = InputValidator::validateRequiredFields($data, ['email', 'password']);
    if (!$requiredCheck['valid']) {
        return sendJson($response, ['error' => 'Missing required fields: ' . implode(', ', $requiredCheck['missing'])], 400);
    }

    $db = getDBConnection();
    if (!$db) {
        return sendJson($response, ['error' => 'Database connection failed'], 500);
    }

    try {
        $stmt = $db->prepare('SELECT id, password_hash, name FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !AuthService::verifyPassword($data['password'], $user['password_hash'])) {
            return sendJson($response, ['error' => 'Invalid credentials'], 401);
        }

        $token = AuthService::generateToken($user['id']);

        return sendJson($response, [
            'id' => (int)$user['id'],
            'email' => $data['email'],
            'name' => $user['name'],
            'token' => $token
        ], 200);
    } catch (\Exception $e) {
        error_log('Login Error: ' . $e->getMessage());
        return sendJson($response, ['error' => 'Login failed'], 500);
    }
});

// Health check
$app->get('/api/health', function (Request $request, Response $response) {
    $db = getDBConnection();
    if (!$db) {
        return sendJson($response, ['status' => 'unhealthy', 'message' => 'Database connection failed'], 503);
    }

    try {
        $db->query('SELECT 1');
        return sendJson($response, ['status' => 'healthy']);
    } catch (\Exception $e) {
        return sendJson($response, ['status' => 'unhealthy', 'message' => $e->getMessage()], 503);
    }
});

// Protected Routes
$app->group('/api/tasks', function (RouteCollectorProxy $group) {
    // Get all tasks
    $group->get('', function (Request $request, Response $response) {
        $user = $request->getAttribute('user');
        $db = getDBConnection();

        try {
            $taskModel = new Task($db);
            $tasks = $taskModel->getByUserId($user->user_id);
            return sendJson($response, ['data' => $tasks]);
        } catch (\Exception $e) {
            error_log('Get Tasks Error: ' . $e->getMessage());
            return sendJson($response, ['error' => 'Failed to fetch tasks'], 500);
        }
    });

    // Create task
    $group->post('', function (Request $request, Response $response) {
        $user = $request->getAttribute('user');
        
        $bodyValidation = InputValidator::validateJsonBody((string)$request->getBody());
        if (!$bodyValidation['valid']) {
            return sendJson($response, ['error' => $bodyValidation['error']], 400);
        }

        $data = $bodyValidation['data'];

        // Validate required fields
        $requiredCheck = InputValidator::validateRequiredFields($data, ['title']);
        if (!$requiredCheck['valid']) {
            return sendJson($response, ['error' => 'Missing required fields: ' . implode(', ', $requiredCheck['missing'])], 400);
        }

        // Validate title
        $titleValidation = InputValidator::validateTaskTitle($data['title']);
        if (!$titleValidation['valid']) {
            return sendJson($response, ['error' => $titleValidation['error']], 400);
        }

        // Validate description if provided
        if (isset($data['description'])) {
            $descValidation = InputValidator::validateTaskDescription($data['description']);
            if (!$descValidation['valid']) {
                return sendJson($response, ['error' => $descValidation['error']], 400);
            }
        }

        // Validate status if provided
        if (isset($data['status'])) {
            $statusValidation = InputValidator::validateStatus($data['status'], Task::getValidStatuses());
            if (!$statusValidation['valid']) {
                return sendJson($response, ['error' => $statusValidation['error']], 400);
            }
        }

        // Validate task data
        $validation = Task::validate($data);
        if (!$validation['valid']) {
            return sendJson($response, ['errors' => $validation['errors']], 400);
        }

        $db = getDBConnection();
        try {
            $taskModel = new Task($db);
            $taskId = $taskModel->create(
                (int)$user->user_id,
                $data['title'],
                $data['description'] ?? '',
                $data['status'] ?? Task::STATUS_PENDING
            );

            if ($taskId === false) {
                return sendJson($response, ['error' => 'Failed to create task'], 500);
            }

            return sendJson($response, [
                'id' => $taskId,
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'status' => $data['status'] ?? Task::STATUS_PENDING,
                'user_id' => (int)$user->user_id
            ], 201);
        } catch (\Exception $e) {
            error_log('Create Task Error: ' . $e->getMessage());
            return sendJson($response, ['error' => 'Failed to create task'], 500);
        }
    });

    // Get task by ID
    $group->get('/{id}', function (Request $request, Response $response, array $args) {
        $user = $request->getAttribute('user');
        
        // Validate ID
        if (!InputValidator::isValidId($args['id'])) {
            return sendJson($response, ['error' => 'Invalid task ID'], 400);
        }

        $taskId = (int)$args['id'];
        $db = getDBConnection();

        try {
            $taskModel = new Task($db);
            $task = $taskModel->getById($taskId, (int)$user->user_id);

            if (!$task) {
                return sendJson($response, ['error' => 'Task not found'], 404);
            }

            return sendJson($response, $task);
        } catch (\Exception $e) {
            error_log('Get Task Error: ' . $e->getMessage());
            return sendJson($response, ['error' => 'Failed to fetch task'], 500);
        }
    });

    // Update task
    $group->put('/{id}', function (Request $request, Response $response, array $args) {
        $user = $request->getAttribute('user');
        
        // Validate ID
        if (!InputValidator::isValidId($args['id'])) {
            return sendJson($response, ['error' => 'Invalid task ID'], 400);
        }

        $bodyValidation = InputValidator::validateJsonBody((string)$request->getBody());
        if (!$bodyValidation['valid']) {
            return sendJson($response, ['error' => $bodyValidation['error']], 400);
        }

        $data = $bodyValidation['data'];
        $taskId = (int)$args['id'];

        // Validate title if provided
        if (isset($data['title'])) {
            $titleValidation = InputValidator::validateTaskTitle($data['title']);
            if (!$titleValidation['valid']) {
                return sendJson($response, ['error' => $titleValidation['error']], 400);
            }
        }

        // Validate description if provided
        if (isset($data['description'])) {
            $descValidation = InputValidator::validateTaskDescription($data['description']);
            if (!$descValidation['valid']) {
                return sendJson($response, ['error' => $descValidation['error']], 400);
            }
        }

        // Validate status if provided
        if (isset($data['status'])) {
            $statusValidation = InputValidator::validateStatus($data['status'], Task::getValidStatuses());
            if (!$statusValidation['valid']) {
                return sendJson($response, ['error' => $statusValidation['error']], 400);
            }
        }

        $db = getDBConnection();
        try {
            $taskModel = new Task($db);
            $success = $taskModel->update($taskId, (int)$user->user_id, $data);

            if (!$success) {
                return sendJson($response, ['error' => 'Task not found or unauthorized'], 403);
            }

            return sendJson($response, ['success' => true]);
        } catch (\Exception $e) {
            error_log('Update Task Error: ' . $e->getMessage());
            return sendJson($response, ['error' => 'Failed to update task'], 500);
        }
    });

    // Delete task
    $group->delete('/{id}', function (Request $request, Response $response, array $args) {
        $user = $request->getAttribute('user');
        
        // Validate ID
        if (!InputValidator::isValidId($args['id'])) {
            return sendJson($response, ['error' => 'Invalid task ID'], 400);
        }

        $taskId = (int)$args['id'];
        $db = getDBConnection();

        try {
            $taskModel = new Task($db);
            $success = $taskModel->delete($taskId, (int)$user->user_id);

            if (!$success) {
                return sendJson($response, ['error' => 'Task not found or unauthorized'], 403);
            }

            return sendJson($response, ['success' => true]);
        } catch (\Exception $e) {
            error_log('Delete Task Error: ' . $e->getMessage());
            return sendJson($response, ['error' => 'Failed to delete task'], 500);
        }
    });

    // Get tasks by status
    $group->get('/filter/{status}', function (Request $request, Response $response, array $args) {
        $user = $request->getAttribute('user');
        $status = $args['status'];

        // Validate status
        $statusValidation = InputValidator::validateStatus($status, Task::getValidStatuses());
        if (!$statusValidation['valid']) {
            return sendJson($response, ['error' => $statusValidation['error']], 400);
        }

        $db = getDBConnection();
        try {
            $taskModel = new Task($db);
            $tasks = $taskModel->getByStatus((int)$user->user_id, $status);
            return sendJson($response, ['data' => $tasks]);
        } catch (\Exception $e) {
            error_log('Get Tasks By Status Error: ' . $e->getMessage());
            return sendJson($response, ['error' => 'Failed to fetch tasks'], 500);
        }
    });
})->add(new JwtMiddleware());

if (PHP_SAPI !== 'cli' || (getenv('APP_ENV') ?? '') !== 'testing') {
    $app->run();
}

return $app;
