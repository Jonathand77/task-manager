# 🧪 Testing WebSockets - Guía Práctica

## ✅ Cómo Probar la Sincronización en Tiempo Real

### Escenario 1: Test Básico (Una Pestaña)

1. **Abre DevTools** (F12)
2. **Consola** → Verás:
   ```
   WebSocket conectado
   ```
3. **Crear una tarea**
4. **Observa**:
   - ✅ Tarea aparece instantáneamente
   - ✅ Toast notifica "Tarea creada"
   - ✅ Indicador muestra 🟢 Sincronizado

---

### Escenario 2: Test Multi-Usuario (Dos Pestañas)

**PASO 1: Preparación**
```
Pestaña A: http://localhost:5173
    └─ Inicia sesión como user@example.com
    └─ Ve lista de tareas vacía
    
Pestaña B: http://localhost:5173
    └─ Inicia sesión con OTRA cuenta
    └ O simplemente refresca la página (mismo usuario)
    └─ Ve lista de tareas
```

**PASO 2: Test de Creación**
```
Pestaña A:
    └─ Click "Nueva Tarea"
    └─ Ingresa: "Tarea desde Pestaña A"
    └─ Click Guardar
    
Esperado:
    ✓ Tarea aparece en Pestaña A (inmediato)
    ✓ Pestaña B recibe evento WebSocket
    ✓ Tarea aparece en Pestaña B (< 100ms)
    ✓ Ambas ven el mismo estado
```

**PASO 3: Test de Actualización**
```
Pestaña B:
    └─ Click "Editar" en la tarea creada
    └─ Cambia estado a "En Progreso"
    └─ Click Guardar
    
Esperado:
    ✓ Badge cambia a azul en Pestaña B
    ✓ Badge cambia a azul en Pestaña A (inmediato)
    ✓ Dashboard actualiza contadores
    ✓ Gráficos se actualizan en tiempo real
```

**PASO 4: Test de Eliminación**
```
Pestaña A:
    └─ Click "Eliminar" en la tarea
    └─ Confirma eliminación
    
Esperado:
    ✓ Tarea desaparece de Pestaña A
    ✓ Tarea desaparece de Pestaña B (instantáneo)
    ✓ Contadores se actualizan
    ✓ Toast notifica en ambas pestañas
```

---

## 🔍 Verificación en Consola (DevTools)

### Ver logs de WebSocket
```javascript
// En consola del navegador F12

// Ver si está conectado
console.log('WebSocket conectado:', socket?.connected)

// Ver eventos que llegan
socket?.onAny((event, ...args) => {
  console.log('Evento:', event, args)
})

// Simular evento de test
socket?.emit('test', { mensaje: 'Hola servidor' })
```

### Output esperado
```
WebSocket conectado
Evento: task.created {...}
Evento: task.updated {...}
Evento: task.deleted {...}
```

---

## 📊 Monitoreo de Servidor

### Ver logs en tiempo real
```bash
# WebSocket
docker logs task-manager-websocket -f

# Backend API
docker logs task-manager-api -f

# Database
docker logs task-manager-postgres -f
```

### Logs esperados WebSocket
```
Conexión abierta: 1
Usuario 1 autenticado
Conexión cerrada: 1
```

---

## 🎯 Test Cases Detallados

### TC-001: Conexión Básica
```
Dado:  Usuario autenticado
Cuando: Accede a /dashboard
Entonces:
    ✓ Indicador muestra "Sincronizado" (verde)
    ✓ Console.log: "WebSocket conectado"
    ✓ Puerto 8080 tiene conexión activa
```

### TC-002: Crear Tarea Múltiples Usuarios
```
Dado:  Usuario A y Usuario B conectados
Cuando: Usuario A crea "Tarea Test"
Entonces:
    ✓ Usuario A ve tarea instantáneamente
    ✓ Usuario B ve tarea en < 100ms
    ✓ Ambos ven ID, título, fecha idénticos
    ✓ Contador de tareas se incrementa en ambos
```

### TC-003: Cambiar Estado Tarea
```
Dado:  Tarea en estado "pending"
Cuando: Cambio a "in_progress"
Entonces:
    ✓ Badge cambia color (naranja → azul)
    ✓ Otro usuario ve cambio inmediatamente
    ✓ Contador de tareas pendientes disminuye
    ✓ Dashboard se actualiza
```

### TC-004: Eliminar Tarea
```
Dado:  Tarea visible en ambas pestañas
Cuando: Elimino tarea
Entonces:
    ✓ Tarea desaparece en 2 pestañas simultáneamente
    ✓ Toast notifica "Tarea eliminada"
    ✓ Contador actualiza en ambas
```

### TC-005: Reconexión Automática
```
Dado:  Usuario conectado a WebSocket
Cuando: Se interrumpe conexión (desconectar internet)
Entonces:
    ✓ Indicador cambia a rojo "Offline"
    ✓ Usuario espera 2-5 segundos
    ✓ Reconexión automática se activa
    ✓ Indicador vuelve a verde
    ✓ Se sincronizan eventos faltantes
```

### TC-006: Dashboard en Tiempo Real
```
Dado:  Dashboard abierto en Pestaña A
Cuando: Creo/edito/elimino tareas en Pestaña B
Entonces:
    ✓ Contadores se actualizan en tiempo real
    ✓ Gráficos se actualizan
    ✓ Estadísticas cambian instantáneamente
    ✓ "Tareas hoy" se incrementa
```

---

## 🔧 Debugging Avanzado

### Interceptar todos los eventos
```javascript
// En DevTools Console
const originalEmit = socket.emit;
socket.emit = function(event, ...args) {
  console.log(`📤 EMIT: ${event}`, args);
  return originalEmit.apply(socket, arguments);
};

const originalOn = socket.on;
socket.on = function(event, callback) {
  return originalOn.apply(socket, [event, function(...args) {
    console.log(`📥 RECEIVED: ${event}`, args);
    return callback.apply(this, args);
  }]);
};
```

### Ver todas las conexiones WebSocket
```bash
# Windows
netstat -ano | findstr :8080

# Linux/Mac
lsof -i :8080
ss -tuln | grep 8080
```

---

## 📈 Métricas de Performance

### Medir latencia
```javascript
// En consola
let startTime = performance.now();
socket.emit('task.created', {title: 'Test'});
socket.on('task.created', () => {
  let latency = performance.now() - startTime;
  console.log(`⏱️ Latencia: ${latency}ms`);
});
```

### Memoria utilizada
```javascript
// En Chrome DevTools
// Performance → Memory → Tomar snapshot
// Conexión WebSocket debe usar < 2MB
```

---

## ❌ Troubleshooting

### Problema: "WebSocket no conecta"
```
Soluciones:
1. Verifica puerto 8080: netstat -ano | findstr :8080
2. Verifica firewall no bloquea puerto 8080
3. Revisa logs: docker logs task-manager-websocket
4. Confirma URL: .env VITE_WEBSOCKET_URL
```

### Problema: "Eventos no llegan a otros usuarios"
```
Soluciones:
1. Verifica que ambos están autenticados
2. Abre DevTools en ambas pestañas
3. Confirma que socket.connected = true en ambas
4. Verifica que userId es diferente (si son usuarios distintos)
5. Revisa logs del servidor WebSocket
```

### Problema: "Indicador muestra rojo (desconectado)"
```
Soluciones:
1. Espera 5-10 segundos (reconexión automática)
2. Recarga página (F5)
3. Verifica conexión a internet
4. Verifica que servidor WebSocket está corriendo
```

---

## 🚀 Stress Testing

### Test con múltiples creaciones rápidas
```javascript
// Crear 10 tareas rapidamente
for (let i = 0; i < 10; i++) {
  fetch('/api/tasks', {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` },
    body: JSON.stringify({
      title: `Tarea ${i}`,
      description: `Test ${i}`
    })
  });
}

// Observar que se sincronizan todas
// Sin perder ninguna
// Sin lag en UI
```

### Test con larga inactividad
```
1. Crear tarea
2. Cerrar pestaña por 2 minutos
3. Abrir nueva pestaña
4. Verificar que tarea aparece
5. Crear nueva tarea en otra ventana
6. Verificar sincronización
```

---

## ✅ Checklist de Testing Completo

```
□ Conexión WebSocket
  □ Indicador verde en navbar
  □ Console muestra "WebSocket conectado"
  □ Network DevTools muestra conexión WS
  
□ Crear Tarea
  □ Aparece en misma ventana instantáneo
  □ Aparece en otra ventana < 100ms
  □ Toast notificación aparece
  □ Contadores actualizan
  
□ Editar Tarea
  □ Cambios visibles inmediatamente
  □ Otro usuario ve cambios
  □ Dashboard actualiza
  
□ Eliminar Tarea
  □ Desaparece en ambas ventanas
  □ Contadores decrecen
  □ Toast notificación
  
□ Estados
  □ Cambios de estado se sincronizan
  □ Colores de badges actualizan
  □ Contadores correctos
  
□ Desconexión
  □ Indicador cambia a rojo
  □ Reconecta automáticamente
  □ Sincroniza eventos faltantes
  
□ Dashboard
  □ Estadísticas actualizan en tiempo real
  □ Gráficos cambian
  □ Contadores precisos
  
□ Performance
  □ Sin lag evidente
  □ Sin memory leaks
  □ Latencia < 100ms
```

---

## 📚 Recursos Útiles

- [Socket.io Documentation](https://socket.io/docs/)
- [Ratchet WebSocket PHP](http://socketo.me/)
- [WebSocket MDN](https://developer.mozilla.org/en-US/docs/Web/API/WebSocket)

