import React, { useEffect, useState } from 'react'
import { useSelector, useDispatch } from 'react-redux'
import { fetchTasks, fetchTasksByStatus } from '../../features/tasks/tasksSlice'
import TaskForm from '../../components/TaskForm/TaskForm'
import TaskItem from '../../components/TaskItem/TaskItem'
import Layout from '../../components/Layout/Layout'
import styles from './TasksList.module.css'

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
    <Layout>
      <div className={styles.container}>
        <div className={styles.header}>
          <h2 className={styles.title}>Mis Tareas</h2>
          <button 
            onClick={handleNewTask}
            className={styles.newButton}
          >
            + Nueva Tarea
          </button>
        </div>

        {/* Filtros y contadores */}
        <div className={styles.filters}>
          <button 
            onClick={() => setFilter('all')}
            className={`${styles.filterButton} ${filter === 'all' ? styles.filterAll : styles.filterAllInactive}`}
          >
            Todas ({tasks.length})
          </button>
          <button 
            onClick={() => setFilter('pending')}
            className={`${styles.filterButton} ${filter === 'pending' ? styles.filterPending : styles.filterPendingInactive}`}
          >
            Pendientes ({count.pending})
          </button>
          <button 
            onClick={() => setFilter('in_progress')}
            className={`${styles.filterButton} ${filter === 'in_progress' ? styles.filterInProgress : styles.filterInProgressInactive}`}
          >
            En Progreso ({count.in_progress})
          </button>
          <button 
            onClick={() => setFilter('done')}
            className={`${styles.filterButton} ${filter === 'done' ? styles.filterDone : styles.filterDoneInactive}`}
          >
            Completadas ({count.done})
          </button>
        </div>

        {/* Modal/Form para crear o editar */}
        {showForm && (
          <div className={styles.formContainer}>
            <TaskForm task={editingTask} onClose={handleCloseForm} />
          </div>
        )}

        {/* Loading state */}
        {status === 'loading' && (
          <div className={styles.loading}>
            Cargando tareas...
          </div>
        )}

        {/* Lista de tareas */}
        {status !== 'loading' && tasks.length === 0 && (
          <div className={styles.empty}>
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
    </Layout>
  )
}
