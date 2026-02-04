import React, { useEffect, useState } from 'react'
import api from '../services/api'
import { useSelector, useDispatch } from 'react-redux'
import { setTasks, addTask, updateTask } from '../features/tasks/tasksSlice'

export default function TasksList(){
  const [filter, setFilter] = useState('')
  const tasks = useSelector(state => state.tasks.list)
  const token = useSelector(state => state.auth.token)
  const dispatch = useDispatch()

  useEffect(()=>{
    const fetchTasks = async () =>{
      const res = await api.get('/api/tasks', { params: filter ? { status: filter } : {} , headers: { Authorization: `Bearer ${token}` } })
      dispatch(setTasks(res.data.data.tasks))
    }
    if(token) fetchTasks()
  },[filter, token, dispatch])

  return (
    <div>
      <h2>Tareas</h2>
      <div>
        <label>Filtrar:</label>
        <select value={filter} onChange={e=>setFilter(e.target.value)}>
          <option value="">Todas</option>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="done">Done</option>
        </select>
      </div>
      <ul>
        {tasks.map(t=> (
          <li key={t.id}>{t.title} - {t.status}</li>
        ))}
      </ul>
    </div>
  )
}
