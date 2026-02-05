import { useEffect, useRef, useCallback, useState } from 'react'
import { useDispatch } from 'react-redux'
import { addTask, updateTaskInState, removeTask } from '../features/tasks/tasksSlice'

/**
 * Hook para conectar a WebSocket y sincronizar tareas en tiempo real
 */
export function useWebSocket(userId, token) {
  const socketRef = useRef(null)
  const dispatch = useDispatch()
  const [isConnected, setIsConnected] = useState(false)

  // Conectar al servidor WebSocket
  useEffect(() => {
    if (!userId || !token) return

    const wsUrl = import.meta.env.VITE_WEBSOCKET_URL || 'ws://localhost:8080'
    
    let reconnectAttempts = 0
    let reconnectTimeout = null
    let shouldReconnect = true

    const connect = () => {
      try {
        socketRef.current = new WebSocket(wsUrl)

        socketRef.current.onopen = () => {
          console.log('WebSocket conectado')
          setIsConnected(true)
          reconnectAttempts = 0

          socketRef.current?.send(JSON.stringify({
            event: 'auth',
            data: { userId, token }
          }))
        }

        socketRef.current.onmessage = (event) => {
          try {
            const message = JSON.parse(event.data)
            const eventName = message?.event
            const data = message?.data

            if (!eventName) return

            switch (eventName) {
              case 'task.created':
                dispatch(addTask(data.task))
                break
              case 'task.updated':
                dispatch(updateTaskInState(data.task))
                break
              case 'task.deleted':
                dispatch(removeTask(data.taskId))
                break
              case 'task.status_changed':
                dispatch(updateTaskInState(data.task))
                break
              case 'task.assigned':
                dispatch(updateTaskInState(data.task))
                break
              default:
                break
            }
          } catch (err) {
            console.error('Error al procesar mensaje WebSocket:', err)
          }
        }

        socketRef.current.onerror = (error) => {
          console.error('Error en WebSocket:', error)
        }

        socketRef.current.onclose = () => {
          console.log('WebSocket desconectado')
          setIsConnected(false)

          if (!shouldReconnect) return

          if (reconnectAttempts < 5) {
            const delay = Math.min(1000 * (reconnectAttempts + 1), 5000)
            reconnectAttempts += 1
            reconnectTimeout = setTimeout(connect, delay)
          }
        }
      } catch (error) {
        console.error('Error al conectar WebSocket:', error)
      }
    }

    connect()

    // Limpiar conexión al desmontar
    return () => {
      shouldReconnect = false
      if (reconnectTimeout) clearTimeout(reconnectTimeout)
      if (socketRef.current) {
        socketRef.current.close()
        socketRef.current = null
      }
      setIsConnected(false)
    }
  }, [userId, token, dispatch])

  // Función para emitir evento
  const emit = useCallback((eventName, data) => {
    if (socketRef.current?.readyState === WebSocket.OPEN) {
      socketRef.current.send(JSON.stringify({ event: eventName, data }))
    }
  }, [])

  return {
    socket: socketRef.current,
    emit,
    isConnected
  }
}
