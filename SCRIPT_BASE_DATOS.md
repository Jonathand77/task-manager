# Scripts de base de datos / migraciones

## Pasos Opcionales

### 1. Migraciones de Base de Datos

**Primera ejecución**
- Las migraciones se montan automáticamente en `/docker-entrypoint-initdb.d`.
- PostgreSQL las ejecuta al iniciar el contenedor por primera vez.

**Aplicación manual (si la base de datos ya existe)**

```bash
docker cp backend/database/migrations/001_create_users_table.sql task-manager-postgres:/tmp/001_create_users_table.sql

docker cp backend/database/migrations/002_create_tasks_table.sql task-manager-postgres:/tmp/002_create_tasks_table.sql
```

**Ejecutar migraciones**

```bash
docker exec -i task-manager-postgres psql -U task_manager_user -d task_manager_db -f /tmp/001_create_users_table.sql

docker exec -i task-manager-postgres psql -U task_manager_user -d task_manager_db -f /tmp/002_create_tasks_table.sql
```

**Verificar tablas**

```bash
docker exec -it task-manager-postgres psql -U task_manager_user -d task_manager_db -c "\dt"
```

### 2. Reiniciar y ver Logs del Backend

**Si se realizaron cambios en archivos del backend:**

```bash
docker restart task-manager-api
docker logs -f task-manager-api
```

### 3. Ejecutar el Frontend (Modo Desarrollo)

**Desde el host (no Docker):**

```bash
npm --prefix frontend install
npm --prefix frontend run dev -- --port 5174
```

**Ya puedes abrir en el navegador y utilizar la aplicación:**
http://localhost:5174/

### 4. Verificaciones rápidas de funcionamiento

**Health Check del Backend**

```bash
Invoke-RestMethod -Uri http://localhost:8000/api/health -Method GET
```

**Respuesta esperada:**
{"status":"healthy"}

**Registro de Usuario desde consola (API)**

```bash
Invoke-RestMethod -Uri http://localhost:8000/api/register -Method POST \
-Body (ConvertTo-Json @{email='you@example.com'; password='Secret123!'; name='You'}) \
-ContentType 'application/json'
```

**Login desde consola (API)**

```bash
Invoke-RestMethod -Uri http://localhost:8000/api/login -Method POST \
-Body (ConvertTo-Json @{email='you@example.com'; password='Secret123!'}) \
-ContentType 'application/json'o
```

**Luego, desde el navegador:**
Acceder a /
Registrarse o iniciar sesión
Navegar a /tasks

### 5. Inspección de Base de Datos

**Consultas rápidas**

**Consultar tablas de tareas y usuarios**

```bash
docker exec -i task-manager-postgres psql -U task_manager_user -d task_manager_db -c "SELECT * FROM users ORDER BY id DESC LIMIT 50;"

docker exec -i task-manager-postgres psql -U task_manager_user -d task_manager_db -c "SELECT * FROM tasks ORDER BY created_at DESC LIMIT 50;"
```

**Consultar Variables**

```bash
docker exec -it task-manager-postgres env | Select-String 'POSTGRES_'
```

**Consultar cantidad por tabla**

```bash
docker exec -i task-manager-postgres psql -U task_manager_user -d task_manager_db -c "SELECT COUNT(*) FROM users;"

docker exec -i task-manager-postgres psql -U task_manager_user -d task_manager_db -c "SELECT COUNT(*) FROM tasks;"
```

**Consola interactiva psql**

```bash
docker exec -it task-manager-postgres psql -U task_manager_user -d task_manager_db
```

### 6. Uso de Cliente Gráfico

**Se puede usar DBeaver, pgAdmin o HeidiSQL con los siguientes datos:**

```bash
Host: localhost
Port: 5432
Database: task_manager_db
User: task_manager_user
Password: valor definido en Paso 1.2
```

**Las migraciones están incluidas en:**
- [backend/database/migrations/001_create_users_table.sql](backend/database/migrations/001_create_users_table.sql)
- [backend/database/migrations/002_create_tasks_table.sql](backend/database/migrations/002_create_tasks_table.sql)

## ✅ Proyecto finalizado con éxito y listo para evaluación técnica.

Las migraciones están incluidas en:
- [backend/database/migrations/001_create_users_table.sql](backend/database/migrations/001_create_users_table.sql)
- [backend/database/migrations/002_create_tasks_table.sql](backend/database/migrations/002_create_tasks_table.sql)