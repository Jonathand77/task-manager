# Task Manager Backend - API REST con Phalcon PHP

API REST para gestionar tareas con autenticación JWT y base de datos PostgreSQL.

# Task Manager - Frontend

Frontend en React + Vite + Redux Toolkit.

## 📦 Estructura del Proyecto

```
task-manager/
│
├──  RAÍZ
│   ├── .env.example
│   ├── .gitignore
│   ├── .git/
│   ├── .github/
│   ├── docker-compose.yml
│   ├── README.md
│   └── QUICKSTART_PROYECT.md
│
├── 🔧 BACKEND/
│   ├── app/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── HealthController.php
│   │   │   └── TasksController.php
│   │   ├── Middleware/
│   │   │   ├── JwtMiddleware.php
│   │   │   └── RateLimitMiddleware.php
│   │   ├── Models/
│   │   │   ├── Task.php
│   │   │   └── User.php
│   │   ├── Services/
│   │   │   ├── AuthService.php
│   │   │   └── InputValidator.php
│   │   └── WebSocket/
│   │       └── TaskWebSocketHandler.php
│   ├── bin/
│   │   └── websocket-server.php
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── routes/
│   ├── vendor/
│   ├── .env
│   ├── .env.example
│   ├── composer.json
│   ├── composer.lock
│   ├── Dockerfile
│   ├── migrate.ps1
│   └── README.md
```