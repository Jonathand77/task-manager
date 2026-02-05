import React, { useState, useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { createTask, updateTask } from '../../features/tasks/tasksSlice'
import { useToast } from '../../contexts/ToastContext'
import styles from './TaskForm.module.css'

export default function TaskForm({ task, onClose }) {
  const [title, setTitle] = useState('')
  const [description, setDescription] = useState('')
  const [status, setStatus] = useState('pending')
  const [error, setError] = useState(null)
  const dispatch = useDispatch()
  const taskStatus = useSelector(state => state.tasks.status)
  const { showSuccess, showError, showWarning } = useToast()

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
      showWarning('El título es obligatorio')
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
          showSuccess('¡Tarea actualizada exitosamente!')
          if (onClose) onClose()
        } else {
          const errorMsg = resultAction.payload?.error || 'Error al actualizar tarea'
          setError(errorMsg)
          showError(errorMsg)
        }
      } else {
        // Create mode
        const resultAction = await dispatch(createTask({ title, description, status }))
        if (createTask.fulfilled.match(resultAction)) {
          showSuccess('¡Tarea creada exitosamente!')
          setTitle('')
          setDescription('')
          setStatus('pending')
          if (onClose) onClose()
        } else {
          const errorMsg = resultAction.payload?.error || 'Error al crear tarea'
          setError(errorMsg)
          showError(errorMsg)
        }
      }
    } catch (err) {
      const errorMsg = err.message || 'Error inesperado'
      setError(errorMsg)
      showError(errorMsg)
    }
  }

  return (
    <form onSubmit={handleSubmit} className={styles.form}>
      <h3 className={styles.title}>{task ? 'Editar Tarea' : 'Nueva Tarea'}</h3>
      
      {error && <div className={styles.error}>{error}</div>}
      
      <div className={styles.formGroup}>
        <label className={styles.label}>Título *</label>
        <input 
          type="text"
          value={title}
          onChange={e => setTitle(e.target.value)}
          placeholder="Título de la tarea"
          className={styles.input}
          required
        />
      </div>

      <div className={styles.formGroup}>
        <label className={styles.label}>Descripción</label>
        <textarea 
          value={description}
          onChange={e => setDescription(e.target.value)}
          placeholder="Descripción opcional"
          rows="4"
          className={styles.textarea}
        />
      </div>

      <div className={styles.formGroup}>
        <label className={styles.label}>Estado</label>
        <select 
          value={status}
          onChange={e => setStatus(e.target.value)}
          className={styles.select}
        >
          <option value="pending">Pendiente</option>
          <option value="in_progress">En Progreso</option>
          <option value="done">Completada</option>
        </select>
      </div>

      <div className={styles.actions}>
        <button 
          type="submit" 
          disabled={taskStatus === 'loading'}
          className={styles.submitButton}
        >
          {taskStatus === 'loading' ? 'Guardando...' : (task ? 'Actualizar' : 'Crear')}
        </button>
        
        {onClose && (
          <button 
            type="button"
            onClick={onClose}
            className={styles.cancelButton}
          >
            Cancelar
          </button>
        )}
      </div>
    </form>
  )
}
