<?php

namespace App\Controllers;

use Phalcon\Mvc\Controller;

class HealthController extends Controller
{
    /**
     * Health check endpoint
     */
    public function checkAction()
    {
        try {
            // Verificar conexión a BD
            $db = $this->di->get('db');
            $db->query('SELECT 1');

            $this->response->setStatusCode(200);
            $this->response->setContentType('application/json');
            $this->response->setContent(json_encode([
                'status'  => 'ok',
                'message' => 'API is healthy',
                'timestamp' => date('Y-m-d H:i:s')
            ]));
        } catch (\Exception $e) {
            $this->response->setStatusCode(503);
            $this->response->setContentType('application/json');
            $this->response->setContent(json_encode([
                'status'  => 'error',
                'message' => 'Service unavailable'
            ]));
        }

        return $this->response;
    }
}
