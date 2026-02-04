<?php

namespace App\Controllers;

use Phalcon\Mvc\Controller;
use App\Models\Task;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TasksController extends Controller
{
    /**
     * Middleware para verificar JWT
     */
    public function beforeActionDispatch($dispatcher)
    {
        // Obtener token del header
        $token = null;
        $authHeader = $this->request->getHeader('Authorization');

        if (!empty($authHeader)) {
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        if (!$token) {
            return $this->sendError('Unauthorized - No token provided', 401);
        }

        try {
            $secret = $this->di->get('config')['jwt']['secret'];
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            
            // Guardar usuario en request
            $this->request->user_id = $decoded->sub;
            $this->request->user_email = $decoded->email;
            
            return true;
        } catch (\Exception $e) {
            return $this->sendError('Unauthorized - Invalid token', 401);
        }
    }

    /**
     * Listar tareas del usuario autenticado
     */
    public function indexAction()
    {
        try {
            $userId = $this->request->user_id;
            $status = $this->request->getQuery('status');

            $query = Task::query()
                ->where('user_id = :user_id:', ['user_id' => $userId])
                ->orderBy('created_at DESC');

            if (!empty($status) && in_array($status, ['pending', 'in_progress', 'done'])) {
                $query->andWhere('status = :status:', ['status' => $status]);
            }

            $tasks = $query->execute()->toArray();

            return $this->sendResponse(['tasks' => $tasks], 'Tasks retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Crear nueva tarea
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            return $this->sendError('Method not allowed', 405);
        }

        try {
            $userId = $this->request->user_id;
            $title = $this->request->getPost('title');
            $description = $this->request->getPost('description', 'string', '');
            $status = $this->request->getPost('status', 'string', 'pending');

            // Validaciones
            if (empty($title)) {
                return $this->sendError('Title is required', 400);
            }

            if (strlen($title) < 3 || strlen($title) > 255) {
                return $this->sendError('Title must be between 3 and 255 characters', 400);
            }

            if (!in_array($status, ['pending', 'in_progress', 'done'])) {
                return $this->sendError('Invalid status', 400);
            }

            // Crear tarea
            $task = new Task();
            $task->user_id = $userId;
            $task->title = $title;
            $task->description = $description;
            $task->status = $status;

            if (!$task->save()) {
                $messages = [];
                foreach ($task->getMessages() as $message) {
                    $messages[] = $message->getMessage();
                }
                return $this->sendError(implode(', ', $messages), 400);
            }

            return $this->sendResponse(['task' => $task], 'Task created successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Actualizar tarea
     */
    public function updateAction()
    {
        if (!$this->request->isPut()) {
            return $this->sendError('Method not allowed', 405);
        }

        try {
            $userId = $this->request->user_id;
            $taskId = $this->dispatcher->getParam('id');
            $title = $this->request->getPut('title');
            $description = $this->request->getPut('description');
            $status = $this->request->getPut('status');

            // Buscar tarea
            $task = Task::findFirst([
                'conditions' => 'id = ?1 AND user_id = ?2',
                'bind'       => [1 => $taskId, 2 => $userId]
            ]);

            if (!$task) {
                return $this->sendError('Task not found', 404);
            }

            // Actualizar campos si se proporcionan
            if (!empty($title)) {
                if (strlen($title) < 3 || strlen($title) > 255) {
                    return $this->sendError('Title must be between 3 and 255 characters', 400);
                }
                $task->title = $title;
            }

            if ($description !== null) {
                $task->description = $description;
            }

            if (!empty($status)) {
                if (!in_array($status, ['pending', 'in_progress', 'done'])) {
                    return $this->sendError('Invalid status', 400);
                }
                $task->status = $status;
            }

            if (!$task->update()) {
                $messages = [];
                foreach ($task->getMessages() as $message) {
                    $messages[] = $message->getMessage();
                }
                return $this->sendError(implode(', ', $messages), 400);
            }

            return $this->sendResponse(['task' => $task], 'Task updated successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Enviar respuesta de éxito
     */
    protected function sendResponse($data, $message = 'Success', $code = 200)
    {
        $this->response->setStatusCode($code);
        $this->response->setContentType('application/json');
        $this->response->setContent(json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ]));

        return $this->response;
    }

    /**
     * Enviar respuesta de error
     */
    protected function sendError($message, $code = 400)
    {
        $this->response->setStatusCode($code);
        $this->response->setContentType('application/json');
        $this->response->setContent(json_encode([
            'success' => false,
            'message' => $message
        ]));

        return $this->response;
    }
}
