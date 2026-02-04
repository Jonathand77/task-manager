# Task Manager Backend - API REST con Phalcon PHP

API REST para gestionar tareas con autenticación JWT y base de datos PostgreSQL.

## 🏗️ Estructura del Proyecto

```
backend/
├── app/
│   ├── controllers/      Controladores de lógica
│   ├── models/           Modelos de datos (User, Task)
│   ├── services/         Servicios de negocio
│   ├── middleware/       Middleware de autenticación
│   └── validators/       Validaciones personalizadas
├── config/
│   ├── config.php        Configuración principal
│   └── database.php      Configuración de BD
├── database/
│   └── migrations/       Migraciones SQL
├── public/
│   └── index.php         Punto de entrada
├── routes/               Definición de rutas
├── composer.json         Dependencias PHP
├── .env                  Variables de entorno
└── Dockerfile            Configuración para Docker
```

## 🛠️ Dependencias

```json
{
  "phalcon/cphalcon": "^5.0",
  "firebase/php-jwt": "^6.8",
  "vlucas/phpdotenv": "^5.5"
}
```

## 🚀 Instalación Local

### Requisitos
- PHP 8.1+
- PostgreSQL 12+
- Composer

### Pasos

1. **Instalar dependencias**
```bash
cd backend
composer install
```

2. **Configurar variables de entorno**
```bash
cp .env.example .env
# Editar .env con tus datos
```

3. **Crear base de datos**
```bash
# Ejecutar migraciones
psql -U postgres -d postgres -f database/migrations/001_create_users_table.sql
psql -U postgres -d postgres -f database/migrations/002_create_tasks_table.sql
```

4. **Ejecutar servidor**
```bash
php -S localhost:8000 -t public/
```

El API estará disponible en `http://localhost:8000/api`

## 📚 API Endpoints

### Autenticación

#### Registrar Usuario
```http
POST /api/register
Content-Type: application/x-www-form-urlencoded

email=usuario@example.com&password=123456&name=Juan
```

**Response (201)**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "email": "usuario@example.com",
      "name": "Juan"
    }
  }
}
```

#### Login
```http
POST /api/login
Content-Type: application/x-www-form-urlencoded

email=usuario@example.com&password=123456
```

**Response (200)**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "email": "usuario@example.com",
      "name": "Juan"
    }
  }
}
```

### Tareas (Requieren JWT)

#### Listar Tareas
```http
GET /api/tasks
Authorization: Bearer <JWT_TOKEN>
```

**Query Parameters**
- `status`: Filtrar por estado (pending, in_progress, done)

**Response (200)**
```json
{
  "success": true,
  "message": "Tasks retrieved successfully",
  "data": {
    "tasks": [
      {
        "id": 1,
        "user_id": 1,
        "title": "Tarea 1",
        "description": "Descripción de la tarea",
        "status": "pending",
        "created_at": "2024-01-15 10:30:00",
        "updated_at": "2024-01-15 10:30:00"
      }
    ]
  }
}
```

#### Crear Tarea
```http
POST /api/tasks
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/x-www-form-urlencoded

title=Nueva Tarea&description=Descripción&status=pending
```

**Response (201)**
```json
{
  "success": true,
  "message": "Task created successfully",
  "data": {
    "task": {
      "id": 2,
      "user_id": 1,
      "title": "Nueva Tarea",
      "description": "Descripción",
      "status": "pending",
      "created_at": "2024-01-15 11:00:00",
      "updated_at": "2024-01-15 11:00:00"
    }
  }
}
```

#### Actualizar Tarea
```http
PUT /api/tasks/1
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/x-www-form-urlencoded

title=Tarea Actualizada&status=in_progress
```

**Response (200)**
```json
{
  "success": true,
  "message": "Task updated successfully",
  "data": {
    "task": {
      "id": 1,
      "user_id": 1,
      "title": "Tarea Actualizada",
      "description": "Descripción de la tarea",
      "status": "in_progress",
      "created_at": "2024-01-15 10:30:00",
      "updated_at": "2024-01-15 11:15:00"
    }
  }
}
```

### Health Check
```http
GET /api/health
```

**Response (200)**
```json
{
  "status": "ok",
  "message": "API is healthy",
  "timestamp": "2024-01-15 10:30:00"
}
```

## 🔐 Autenticación JWT

Todos los endpoints de tareas requieren enviar el token JWT en el header:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Estructura del JWT:**
- **Header**: `{ "typ": "JWT", "alg": "HS256" }`
- **Payload**: 
  ```json
  {
    "iat": 1674571234,
    "exp": 1675176034,
    "sub": 1,
    "email": "usuario@example.com"
  }
  ```
- **Signature**: Firmado con JWT_SECRET

**Expiración**: 7 días desde la emisión

## 🧪 Testing

```bash
# Ejecutar tests
composer test

# Análisis estático
composer stan

# Code style
composer lint
```

## 🔒 Seguridad

- ✅ Hash de contraseñas con bcrypt
- ✅ Validación de JWT en endpoints protegidos
- ✅ Validaciones de entrada
- ✅ Autorización (usuarios solo ven sus tareas)
- ✅ CORS configurado

## 📝 Validaciones

### User
- Email debe ser único
- Email debe ser válido
- Contraseña mínimo 6 caracteres

### Task
- Título: 3-255 caracteres (obligatorio)
- Estado: pending, in_progress, done
- Status cambia automáticamente a 'pending' si no se especifica

## 🚀 Deployment

### Con Docker
```bash
docker-compose up -d backend
```

### Variables de entorno necesarias
```
DB_HOST=postgres
DB_PORT=5432
DB_NAME=task_manager_db
DB_USER=task_manager_user
DB_PASSWORD=secure_password
JWT_SECRET=your_secret_key
APP_ENV=production
```

## 📖 Referencias

- [Documentación Phalcon 5](https://docs.phalcon.io/)
- [Firebase JWT](https://github.com/firebase/php-jwt)
- [PHP Dotenv](https://github.com/vlucas/phpdotenv)

---

**Creado con ❤️ para la prueba técnica**
