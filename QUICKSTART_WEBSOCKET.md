# ⚡ QUICK START - WebSocket en 5 Minutos

## 🚀 Inicio Rápido

### Opción 1: Con Docker (RECOMENDADO)
```bash
# Desde la raíz del proyecto
docker-compose up -d --build

# Esperar ~30 segundos a que todo se inicie
# ✅ PostgreSQL iniciado
# ✅ Backend API iniciado
# ✅ WebSocket Server iniciado
# ✅ Frontend iniciado

# Ir a http://localhost:5173
# Inicia sesión
# 🎉 ¡WebSocket está activo!
```

### Opción 2: Sin Docker
```bash
# Terminal 1: Backend
cd backend
composer install
php -S localhost:8000 -t public public/index.php

# Terminal 2: WebSocket
cd backend
php bin/websocket-server.php

# Terminal 3: Frontend
cd frontend
npm install
npm run dev

# Ir a http://localhost:5173
```

---

## ✅ Verificación Rápida

### Paso 1: Login
```
1. Abre http://localhost:5173
2. Inicia sesión (usa credenciales test o registra cuenta)
3. Mira el Navbar
4. Deberías ver "Sincronizado" en verde ✅
```

### Paso 2: Abre Segunda Ventana
```
1. En otra pestaña: http://localhost:5173/tasks
2. O en otro navegador
3. Inicia sesión
4. Verás "Sincronizado" en verde ✅
```

### Paso 3: Crea una Tarea
```
Ventana A:
└─ Click "Nueva Tarea"
└─ Ingresa título
└─ Click Guardar

Resultado:
✅ Aparece en Ventana A instantáneamente
✅ Aparece en Ventana B < 100ms
✅ Ambas muestran Toast "Tarea creada"
```

### Paso 4: Prueba Cambios
```
Ventana B:
└─ Click Editar tarea
└─ Cambia estado a "En Progreso"
└─ Click Guardar

Resultado:
✅ Estado cambia a azul en Ventana B
✅ Estado cambia a azul en Ventana A
✅ Gráficos se actualizan en Dashboard
```

---

## 🔍 Ver que Funciona (DevTools)

Abre DevTools (F12) y mira la consola:

```javascript
// Verás logs como estos:
WebSocket conectado
Tarea creada: {...}
Tarea actualizada: {...}
```

O verifica en Network:
```
WebSocket - ws://localhost:8080
Status: 101 Switching Protocols (Conectado ✅)
```

---

## 🚨 Si No Funciona

### Error: "WebSocket Connection Failed"
```bash
# Puerto 8080 ocupado
netstat -ano | findstr :8080

# O cambiar puerto en backend/.env
WEBSOCKET_PORT=8081
```

### Error: "Cannot GET /tasks"
```bash
# Backend no inició
docker logs task-manager-api

# Verificar que backend esté en puerto 8000
curl http://localhost:8000/health
```

### Indicador Rojo "Offline"
```
Espera 5-10 segundos (reconexión automática)
O recarga página F5
```

---

## 📊 Monitoreo

### Ver que está funcionando
```bash
# Terminal 1: Ver WebSocket
docker logs task-manager-websocket -f

# Terminal 2: Ver API
docker logs task-manager-api -f

# Deberías ver logs cuando:
# - Te conectas
# - Creas tareas
# - Cambias estados
# - Te desconectas
```

---

## 🎯 Casos de Uso

### 1️⃣ Equipo Colaborativo
```
User1 crea tarea "Backend"
User2 crea tarea "Frontend"
User3 crea tarea "Testing"

Todos ven las 3 tareas en tiempo real ✅
```

### 2️⃣ Actualización de Estado
```
Task: "Implementar Login"
Status: pending → in_progress

User1 ve el cambio instantáneamente ✅
User2 ve el cambio instantáneamente ✅
Dashboard se actualiza ✅
```

### 3️⃣ Seguimiento en Vivo
```
Abre Dashboard en Pantalla Grande
Crea/edita tareas en computadora normal
Gráficos se actualizan en vivo ✅
```

---

## 📱 Testing en Móvil

```bash
# Obtén tu IP local
ipconfig getifaddr en0  # Mac
hostname -I              # Linux
ipconfig                 # Windows

# En móvil abre:
http://TU_IP:5173

# Sincronización funciona igual ✅
```

---

## 🔧 Cambiar Puertos

### Si ports están ocupados:

**Backend** (`.env`)
```env
# Cambiar puerto del API
APP_PORT=8001
```

**WebSocket** (`.env`)
```env
# Cambiar puerto WebSocket
WEBSOCKET_PORT=8081
```

**Frontend** (`vite.config.js`)
```javascript
server: {
  port: 5174  // cambiar aquí
}
```

---

## 💡 Tips

### Tip 1: Ver WebSocket en Network
```
DevTools → Network → WS
Filtra por "ws" para ver conexiones WebSocket
```

### Tip 2: Simular Desconexión
```javascript
// En console del navegador
socket.disconnect()

// Verás indicador rojo
// Se reconecta automáticamente
socket.connect()
```

### Tip 3: Test de Latencia
```javascript
// En console
let start = Date.now();
socket.emit('ping');
socket.on('pong', () => {
  console.log('Latencia:', Date.now() - start, 'ms')
})
```

---

## 📞 Soporte Rápido

| Problema | Solución |
|----------|----------|
| No conecta | Ver si puerto 8080 está libre |
| Tarda mucho | Esperar 5seg (reconexión) |
| Veo error CORS | Verificar .env VITE_WEBSOCKET_URL |
| No se sincroniza | Verificar que ambos tienen token válido |
| Browser antiguo | Usar Chrome/Firefox/Safari reciente |

---

## 🎉 ¡Listo!

Ahora tienes **sincronización en tiempo real completa** en tu Task Manager.

**Próximos pasos:**
1. ✅ Probar con múltiples usuarios
2. ✅ Revisar la documentación detallada
3. ✅ Deployar a producción (cambiar WEBSOCKET_URL)
4. ✅ Monitorear los logs

¡Disfruta de tu app moderna y en tiempo real! 🚀

