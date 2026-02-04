# 📋 Task Manager - Aplicación Full Stack

Mini gestor de tareas donde usuarios pueden registrarse, crear, listar y actualizar sus tareas.

## 🏗️ Arquitectura

```
task-manager/
├── backend/              API REST con Phalcon PHP
├── frontend/             Aplicación React con Redux
├── docker-compose.yml    Orquestación de servicios
└── .env.example          Variables de entorno
```

## 🛠️ Tecnologías

### Backend
- **Framework**: Phalcon PHP
- **Base de Datos**: PostgreSQL
- **Autenticación**: JWT (JSON Web Tokens)
- **Validaciones**: Phalcon Validators

### Frontend
- **Framework**: React 18+
- **Estado Global**: Redux Toolkit
- **HTTP Client**: Axios
- **Build Tool**: Vite

### DevOps
- **Contenedorización**: Docker & Docker Compose

## 📋 Requisitos Previos

- Docker y Docker Compose instalados
- Node.js 18+ (para desarrollo local)
- PHP 8.1+ (para desarrollo local del backend)
- PostgreSQL 15+ (si ejecutas sin Docker)

## 🚀 Instalación Rápida con Docker

### 1. Clonar repositorio
```bash
git clone https://github.com/tu-usuario/task-manager.git
cd task-manager
```

### 2. Configurar variables de entorno
```bash
cp .env.example .env
# Editar .env con tus valores
```

### 3. Ejecutar con Docker Compose
```bash
docker-compose up -d
```

### 4. Acceder a la aplicación
- **Frontend**: http://localhost:5173
- **API**: http://localhost:8000/api

## 📚 API Endpoints

### Autenticación
- `POST /api/register` - Registrar nuevo usuario
- `POST /api/login` - Iniciar sesión (retorna JWT)

### Tareas (Requieren autenticación)
- `GET /api/tasks` - Listar tareas del usuario
- `POST /api/tasks` - Crear nueva tarea
- `PUT /api/tasks/{id}` - Actualizar tarea

## 🔐 Autenticación

Los endpoints de tareas requieren:
```
Authorization: Bearer <JWT_TOKEN>
```

## 📦 Estructura del Backend

```
backend/
├── app/
│   ├── controllers/      Controladores de lógica
│   ├── models/           Modelos de datos
│   ├── services/         Servicios de negocio
│   ├── middleware/       Middleware de autenticación
│   └── validators/       Validaciones personalizadas
├── config/               Configuración
├── database/
│   └── migrations/       Migraciones de BD
├── routes/               Definición de rutas
└── public/               Punto de entrada
```

## 📦 Estructura del Frontend

```
frontend/
├── src/
│   ├── components/       Componentes reutilizables
│   ├── pages/            Páginas principales
│   ├── store/            Redux store (slices)
│   ├── services/         Servicios API
│   ├── hooks/            Custom hooks
│   ├── styles/           Estilos globales
│   └── App.jsx           Componente raíz
└── public/               Archivos estáticos
```

## 🧪 Testing (Extras)

### Backend - PHPUnit
```bash
cd backend
./vendor/bin/phpunit
```

### Frontend - Jest
```bash
cd frontend
npm test
```

## 📝 Funcionalidades

### Autenticación
- ✅ Registro de usuarios
- ✅ Login con JWT
- ✅ Hash seguro de contraseñas (bcrypt)
- ✅ Validación de tokens

### Gestor de Tareas
- ✅ Crear tareas con título y descripción
- ✅ Listar tareas del usuario autenticado
- ✅ Actualizar estado (pending, in_progress, done)
- ✅ Filtrar tareas por estado
- ✅ Validaciones de entrada

### UI/UX
- ✅ Formularios con validación
- ✅ Feedback visual (loading, errores)
- ✅ Diseño responsivo
- ✅ Notificaciones de estado

## 🚀 Extras Implementados

- 🐳 Docker y Docker Compose
- ✅ Migraciones de BD
- 🧪 Tests unitarios
- ⚡ WebSockets para notificaciones (opcional)

## 📖 Documentación

Ver carpetas respectivas:
- [Backend Docs](./backend/README.md)
- [Frontend Docs](./frontend/README.md)

## 🤝 Contribuir

1. Fork el proyecto
2. Crear rama feature (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a rama (`git push origin feature/AmazingFeature`)
5. Abrir Pull Request

## 📄 Licencia

Este proyecto está bajo la licencia MIT.

## 👤 Autor

Tu nombre - [GitHub](https://github.com/tu-usuario)

---

**¡Feliz desarrollo! 🎉**