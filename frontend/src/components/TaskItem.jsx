import React from 'react'
import { useDispatch } from 'react-redux'
import { deleteTask } from '../features/tasks/tasksSlice'

const statusBadges = {
  pending: { label: 'Pendiente', color: '#f59e0b', bg: '#fef3c7' },
  in_progress: { label: 'En Progreso', color: '#3b82f6', bg: '#dbeafe' },
  done: { label: 'Completada', color: '#10b981', bg: '#d1fae5' }
}

export default function TaskItem({ task, onEdit }) {
  const dispatch = useDispatch()
  const badge = statusBadges[task.status] || statusBadges.pending

  const handleDelete = async () => {
    if (window.confirm('¿Seguro que quieres eliminar esta tarea?')) {
      await dispatch(deleteTask(task.id))
    }
  }

  return (
    <div style={{ 
      padding: '16px', 
      border: '1px solid #e5e7eb', 
      borderRadius: '8px', 
      marginBottom: '12px',
      background: '#fff'
    }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start' }}>
        <div style={{ flex: 1 }}>
          <h4 style={{ margin: '0 0 8px 0', fontSize: '18px' }}>{task.title}</h4>
          {task.description && (
            <p style={{ margin: '0 0 8px 0', color: '#6b7280', fontSize: '14px' }}>
              {task.description}
            </p>
          )}
          <span 
            style={{ 
              display: 'inline-block',
              padding: '4px 12px', 
              borderRadius: '12px', 
              fontSize: '12px',
              fontWeight: '600',
              color: badge.color,
              background: badge.bg
            }}
          >
            {badge.label}
          </span>
        </div>
        
        <div style={{ display: 'flex', gap: '8px', marginLeft: '16px' }}>
          <button 
            onClick={() => onEdit(task)}
            style={{ 
              padding: '6px 12px', 
              background: '#f3f4f6', 
              border: '1px solid #d1d5db',
              borderRadius: '4px', 
              cursor: 'pointer',
              fontSize: '14px'
            }}
          >
            Editar
          </button>
          <button 
            onClick={handleDelete}
            style={{ 
              padding: '6px 12px', 
              background: '#fee2e2', 
              color: '#dc2626',
              border: '1px solid #fecaca',
              borderRadius: '4px', 
              cursor: 'pointer',
              fontSize: '14px'
            }}
          >
            Eliminar
          </button>
        </div>
      </div>
    </div>
  )
}
