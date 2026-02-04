import React, { useState, useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { createTask, updateTask } from '../features/tasks/tasksSlice'

export default function TaskForm({ task, onClose }) {
  const [title, setTitle] = useState('')
  const [description, setDescription] = useState('')
  const [status, setStatus] = useState('pending')
  const [error, setError] = useState(null)
  const dispatch = useDispatch()
  const taskStatus = useSelector(state => state.tasks.status)

  useEffect(() => {
    if (task) {
      setTitle(task.title || '')
      setDescription(task.description || '')
      setStatus(task.status || 'pending')
    }
  }, [task])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(null)

    if (!title.trim()) {
      setError('El título es obligatorio')
      return
    }

    try {
      if (task) {
        // Edit mode
        const resultAction = await dispatch(updateTask({ 
          id: task.id, 
          data: { title, description, status } 
        }))
        if (updateTask.fulfilled.match(resultAction)) {
          if (onClose) onClose()
        } else {
          setError(resultAction.payload?.error || 'Error al actualizar tarea')
        }
      } else {
        // Create mode
        const resultAction = await dispatch(createTask({ title, description, status }))
        if (createTask.fulfilled.match(resultAction)) {
          setTitle('')
          setDescription('')
          setStatus('pending')
          if (onClose) onClose()
        } else {
          setError(resultAction.payload?.error || 'Error al crear tarea')
        }
      }
    } catch (err) {
      setError(err.message || 'Error inesperado')
    }
  }

  return (
    <form onSubmit={handleSubmit} style={{ padding: '20px', border: '1px solid #ddd', borderRadius: '8px', background: '#fff' }}>
      <h3>{task ? 'Editar Tarea' : 'Nueva Tarea'}</h3>
      
      {error && <div style={{ color: 'red', marginBottom: '10px' }}>{error}</div>}
      
      <div style={{ marginBottom: '15px' }}>
        <label style={{ display: 'block', marginBottom: '5px', fontWeight: 'bold' }}>Título *</label>
        <input 
          type="text"
          value={title}
          onChange={e => setTitle(e.target.value)}
          placeholder="Título de la tarea"
          style={{ width: '100%', padding: '8px', borderRadius: '4px', border: '1px solid #ccc' }}
          required
        />
      </div>

      <div style={{ marginBottom: '15px' }}>
        <label style={{ display: 'block', marginBottom: '5px', fontWeight: 'bold' }}>Descripción</label>
        <textarea 
          value={description}
          onChange={e => setDescription(e.target.value)}
          placeholder="Descripción opcional"
          rows="4"
          style={{ width: '100%', padding: '8px', borderRadius: '4px', border: '1px solid #ccc' }}
        />
      </div>

      <div style={{ marginBottom: '15px' }}>
        <label style={{ display: 'block', marginBottom: '5px', fontWeight: 'bold' }}>Estado</label>
        <select 
          value={status}
          onChange={e => setStatus(e.target.value)}
          style={{ width: '100%', padding: '8px', borderRadius: '4px', border: '1px solid #ccc' }}
        >
          <option value="pending">Pendiente</option>
          <option value="in_progress">En Progreso</option>
          <option value="done">Completada</option>
        </select>
      </div>

      <div style={{ display: 'flex', gap: '10px' }}>
        <button 
          type="submit" 
          disabled={taskStatus === 'loading'}
          style={{ 
            padding: '10px 20px', 
            background: '#2563eb', 
            color: 'white', 
            border: 'none', 
            borderRadius: '4px', 
            cursor: 'pointer' 
          }}
        >
          {taskStatus === 'loading' ? 'Guardando...' : (task ? 'Actualizar' : 'Crear')}
        </button>
        
        {onClose && (
          <button 
            type="button"
            onClick={onClose}
            style={{ 
              padding: '10px 20px', 
              background: '#6b7280', 
              color: 'white', 
              border: 'none', 
              borderRadius: '4px', 
              cursor: 'pointer' 
            }}
          >
            Cancelar
          </button>
        )}
      </div>
    </form>
  )
}
