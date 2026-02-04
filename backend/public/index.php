<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Load environment variables
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create Slim app
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

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
function generateToken($userId) {
    $key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
    $payload = [
        'iss' => 'task-manager',
        'aud' => 'task-manager-api',
        'iat' => time(),
        'exp' => time() + (86400 * 7),
        'user_id' => $userId
    ];
    return \Firebase\JWT\JWT::encode($payload, $key, 'HS256');
}

function verifyToken($token) {
    try {
        $key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
        $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
        return $decoded;
    } catch (\Exception $e) {
        error_log('Token Error: ' . $e->getMessage());
        return null;
    }
}

function sendJson(Response $response, $data, $status = 200) {
    $response->getBody()->write(json_encode($data));
    return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
}

// JWT Middleware
$jwtMiddleware = function (Request $request, \Closure $next) {
    $authHeader = $request->getHeaderLine('Authorization');
    if (empty($authHeader)) {
        $response = new \Slim\Psr7\Response();
        return sendJson($response, ['error' => 'Missing authorization token'], 401);
    }

    $parts = explode(' ', $authHeader);
    if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
        $response = new \Slim\Psr7\Response();
        return sendJson($response, ['error' => 'Invalid authorization header'], 401);
    }

    $decoded = verifyToken($parts[1]);
    if (!$decoded) {
        $response = new \Slim\Psr7\Response();
        return sendJson($response, ['error' => 'Invalid or expired token'], 401);
    }

    $request = $request->withAttribute('user', $decoded);
    return $next($request);
};

// Public Routes

// Register
$app->post('/api/register', function (Request $request, Response $response) {
    $data = json_decode($request->getBody(), true);

    if (!isset($data['email'], $data['password'], $data['name'])) {
        return sendJson($response, ['error' => 'Missing required fields'], 400);
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

        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $db->prepare('INSERT INTO users (email, password_hash, name, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$data['email'], $passwordHash, $data['name']]);

        $userId = $db->lastInsertId();
        $token = generateToken($userId);

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
    $data = json_decode($request->getBody(), true);

    if (!isset($data['email'], $data['password'])) {
        return sendJson($response, ['error' => 'Missing email or password'], 400);
    }

    $db = getDBConnection();
    if (!$db) {
        return sendJson($response, ['error' => 'Database connection failed'], 500);
    }

    try {
        $stmt = $db->prepare('SELECT id, password_hash, name FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            return sendJson($response, ['error' => 'Invalid credentials'], 401);
        }

        $token = generateToken($user['id']);

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
            $stmt = $db->prepare('SELECT id, title, description, status, created_at FROM tasks WHERE user_id = ? ORDER BY created_at DESC');
            $stmt->execute([$user->user_id]);
            $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return sendJson($response, ['data' => $tasks]);
        } catch (\Exception $e) {
            error_log('Get Tasks Error: ' . $e->getMessage());
            return sendJson($response, ['error' => 'Failed to fetch tasks'], 500);
        }
    });

    // Create task
    $group->post('', function (Request $request, Response $response) {
        $user = $request->getAttribute('user');
        $data = json_decode($request->getBody(), true);

        if (!isset($data['title'])) {
            return sendJson($response, ['error' => 'Missing title'], 400);
        }

        $db = getDBConnection();

        try {
            $status = $data['status'] ?? 'pending';
            $description = $data['description'] ?? '';

            $stmt = $db->prepare('INSERT INTO tasks (user_id, title, description, status, created_at) VALUES (?, ?, ?, ?, NOW())');
            $stmt->execute([$user->user_id, $data['title'], $description, $status]);

            $taskId = $db->lastInsertId();

            return sendJson($response, [
                'id' => (int)$taskId,
                'title' => $data['title'],
                'description' => $description,
                'status' => $status,
                'user_id' => $user->user_id
            ], 201);
        } catch (\Exception $e) {
            error_log('Create Task Error: ' . $e->getMessage());
            return sendJson($response, ['error' => 'Failed to create task'], 500);
        }
    });

    // Update task
    $group->put('/{id}', function (Request $request, Response $response, array $args) {
        $user = $request->getAttribute('user');
        $taskId = (int)$args['id'];
        $data = json_decode($request->getBody(), true);

        $db = getDBConnection();

        try {
            $stmt = $db->prepare('SELECT user_id FROM tasks WHERE id = ?');
            $stmt->execute([$taskId]);
            $task = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$task || $task['user_id'] != $user->user_id) {
                return sendJson($response, ['error' => 'Task not found or unauthorized'], 403);
            }

            $updates = [];
            $params = [];

            if (isset($data['title'])) {
                $updates[] = 'title = ?';
                $params[] = $data['title'];
            }
            if (isset($data['description'])) {
                $updates[] = 'description = ?';
                $params[] = $data['description'];
            }
            if (isset($data['status'])) {
                $updates[] = 'status = ?';
                $params[] = $data['status'];
            }

            if (empty($updates)) {
                return sendJson($response, ['error' => 'No fields to update'], 400);
            }

            $params[] = $taskId;
            $stmt = $db->prepare('UPDATE tasks SET ' . implode(', ', $updates) . ' WHERE id = ?');
            $stmt->execute($params);

            return sendJson($response, ['success' => true]);
        } catch (\Exception $e) {
            error_log('Update Task Error: ' . $e->getMessage());
            return sendJson($response, ['error' => 'Failed to update task'], 500);
        }
    });
})->add($jwtMiddleware);

$app->run();
