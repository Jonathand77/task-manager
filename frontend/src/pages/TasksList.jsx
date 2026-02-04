import React, { useEffect, useState } from 'react'
import { useSelector, useDispatch } from 'react-redux'
import { fetchTasks, fetchTasksByStatus } from '../features/tasks/tasksSlice'
import TaskForm from '../components/TaskForm'
import TaskItem from '../components/TaskItem'

export default function TasksList(){
  const [filter, setFilter] = useState('all')
  const [showForm, setShowForm] = useState(false)
  const [editingTask, setEditingTask] = useState(null)
  
  const tasks = useSelector(state => state.tasks.list)
  const status = useSelector(state => state.tasks.status)
  const count = useSelector(state => state.tasks.count)
  const dispatch = useDispatch()

  useEffect(()=>{
    if (filter === 'all') {
      dispatch(fetchTasks())
    } else {
      dispatch(fetchTasksByStatus(filter))
    }
  },[filter, dispatch])

  const handleEdit = (task) => {
    setEditingTask(task)
    setShowForm(true)
  }

  const handleCloseForm = () => {
    setShowForm(false)
    setEditingTask(null)
  }

  const handleNewTask = () => {
    setEditingTask(null)
    setShowForm(true)
  }

  return (
    <div className="container" style={{ maxWidth: '900px', margin: '24px auto', padding: '0 16px' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
        <h2 style={{ margin: 0 }}>Mis Tareas</h2>
        <button 
          onClick={handleNewTask}
          style={{ 
            padding: '10px 20px', 
            background: '#2563eb', 
            color: 'white', 
            border: 'none', 
            borderRadius: '6px', 
            cursor: 'pointer',
            fontWeight: '600'
          }}
        >
          + Nueva Tarea
        </button>
      </div>

      {/* Filtros y contadores */}
      <div style={{ 
        display: 'flex', 
        gap: '12px', 
        marginBottom: '24px',
        flexWrap: 'wrap'
      }}>
        <button 
          onClick={() => setFilter('all')}
          style={{ 
            padding: '8px 16px', 
            background: filter === 'all' ? '#2563eb' : '#f3f4f6',
            color: filter === 'all' ? 'white' : '#374151',
            border: 'none',
            borderRadius: '6px',
            cursor: 'pointer',
            fontWeight: '600'
          }}
        >
          Todas ({tasks.length})
        </button>
        <button 
          onClick={() => setFilter('pending')}
          style={{ 
            padding: '8px 16px', 
            background: filter === 'pending' ? '#f59e0b' : '#fef3c7',
            color: filter === 'pending' ? 'white' : '#92400e',
            border: 'none',
            borderRadius: '6px',
            cursor: 'pointer',
            fontWeight: '600'
          }}
        >
          Pendientes ({count.pending})
        </button>
        <button 
          onClick={() => setFilter('in_progress')}
          style={{ 
            padding: '8px 16px', 
            background: filter === 'in_progress' ? '#3b82f6' : '#dbeafe',
            color: filter === 'in_progress' ? 'white' : '#1e40af',
            border: 'none',
            borderRadius: '6px',
            cursor: 'pointer',
            fontWeight: '600'
          }}
        >
          En Progreso ({count.in_progress})
        </button>
        <button 
          onClick={() => setFilter('done')}
          style={{ 
            padding: '8px 16px', 
            background: filter === 'done' ? '#10b981' : '#d1fae5',
            color: filter === 'done' ? 'white' : '#065f46',
            border: 'none',
            borderRadius: '6px',
            cursor: 'pointer',
            fontWeight: '600'
          }}
        >
          Completadas ({count.done})
        </button>
      </div>

      {/* Modal/Form para crear o editar */}
      {showForm && (
        <div style={{ marginBottom: '24px' }}>
          <TaskForm task={editingTask} onClose={handleCloseForm} />
        </div>
      )}

      {/* Loading state */}
      {status === 'loading' && (
        <div style={{ textAlign: 'center', padding: '40px', color: '#6b7280' }}>
          Cargando tareas...
        </div>
      )}

      {/* Lista de tareas */}
      {status !== 'loading' && tasks.length === 0 && (
        <div style={{ 
          textAlign: 'center', 
          padding: '40px', 
          background: '#f9fafb',
          borderRadius: '8px',
          color: '#6b7280'
        }}>
          No hay tareas. ¡Crea una nueva!
        </div>
      )}

      {status !== 'loading' && tasks.length > 0 && (
        <div>
          {tasks.map(task => (
            <TaskItem key={task.id} task={task} onEdit={handleEdit} />
          ))}
        </div>
      )}
    </div>
  )
}
