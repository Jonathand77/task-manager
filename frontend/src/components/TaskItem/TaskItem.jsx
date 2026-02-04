import React from 'react'
import { useDispatch } from 'react-redux'
import { deleteTask } from '../../features/tasks/tasksSlice'
import styles from './TaskItem.module.css'

const statusBadges = {
  pending: { label: 'Pendiente', className: styles.badgePending },
  in_progress: { label: 'En Progreso', className: styles.badgeInProgress },
  done: { label: 'Completada', className: styles.badgeDone }
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
    <div className={styles.taskItem}>
      <div className={styles.content}>
        <div className={styles.details}>
          <h4 className={styles.title}>{task.title}</h4>
          {task.description && (
            <p className={styles.description}>
              {task.description}
            </p>
          )}
          <span className={`${styles.badge} ${badge.className}`}>
            {badge.label}
          </span>
        </div>
        
        <div className={styles.actions}>
          <button 
            onClick={() => onEdit(task)}
            className={styles.editButton}
          >
            Editar
          </button>
          <button 
            onClick={handleDelete}
            className={styles.deleteButton}
          >
            Eliminar
          </button>
        </div>
      </div>
    </div>
  )
}
