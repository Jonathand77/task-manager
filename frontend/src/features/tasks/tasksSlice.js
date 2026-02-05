import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import api from '../../services/api'

// Async thunks for CRUD operations
export const fetchTasks = createAsyncThunk('tasks/fetchTasks', async (_, { rejectWithValue }) => {
  try {
    const res = await api.get('/tasks')
    return res.data.data || res.data
  } catch (err) {
    return rejectWithValue(err.response?.data || { error: 'Failed to fetch tasks' })
  }
})

export const createTask = createAsyncThunk('tasks/createTask', async (payload, { rejectWithValue }) => {
  try {
    const res = await api.post('/tasks', payload)
    return res.data
  } catch (err) {
    return rejectWithValue(err.response?.data || { error: 'Failed to create task' })
  }
})

export const updateTask = createAsyncThunk('tasks/updateTask', async ({ id, data }, { rejectWithValue }) => {
  try {
    const res = await api.put(`/tasks/${id}`, data)
    // Return updated task data (merge with original data sent)
    return { id, ...data }
  } catch (err) {
    return rejectWithValue(err.response?.data || { error: 'Failed to update task' })
  }
})

export const deleteTask = createAsyncThunk('tasks/deleteTask', async (id, { rejectWithValue }) => {
  try {
    await api.delete(`/tasks/${id}`)
    return id
  } catch (err) {
    return rejectWithValue(err.response?.data || { error: 'Failed to delete task' })
  }
})

export const fetchTasksByStatus = createAsyncThunk('tasks/fetchTasksByStatus', async (status, { rejectWithValue }) => {
  try {
    const res = await api.get(`/tasks/filter/${status}`)
    return res.data.data || res.data
  } catch (err) {
    return rejectWithValue(err.response?.data || { error: 'Failed to filter tasks' })
  }
})

const initialState = {
  list: [],
  status: 'idle',
  error: null,
  filter: 'all', // 'all', 'pending', 'in_progress', 'done'
  count: { pending: 0, in_progress: 0, done: 0 }
}

const tasksSlice = createSlice({
  name: 'tasks',
  initialState,
  reducers: {
    setFilter(state, action) {
      state.filter = action.payload
    },
    clearTasks(state) {
      state.list = []
      state.error = null
    },
    // Reducers para sincronización WebSocket
    addTask(state, action) {
      const task = action.payload
      // Evitar duplicados
      if (!state.list.find(t => t.id === task.id)) {
        state.list.unshift(task)
        if (task.status === 'pending') state.count.pending++
        else if (task.status === 'in_progress') state.count.in_progress++
        else if (task.status === 'done') state.count.done++
      }
    },
    updateTaskInState(state, action) {
      const task = action.payload
      const idx = state.list.findIndex(t => t.id === task.id)
      if (idx !== -1) {
        const oldStatus = state.list[idx].status
        const newStatus = task.status || oldStatus
        state.list[idx] = { ...state.list[idx], ...task }
        
        // Actualizar counts si el estado cambió
        if (oldStatus !== newStatus) {
          if (oldStatus === 'pending') state.count.pending--
          else if (oldStatus === 'in_progress') state.count.in_progress--
          else if (oldStatus === 'done') state.count.done--
          
          if (newStatus === 'pending') state.count.pending++
          else if (newStatus === 'in_progress') state.count.in_progress++
          else if (newStatus === 'done') state.count.done++
        }
      }
    },
    removeTask(state, action) {
      const taskId = action.payload
      const idx = state.list.findIndex(t => t.id === taskId)
      if (idx !== -1) {
        const status = state.list[idx].status
        state.list.splice(idx, 1)
        
        // Actualizar counts
        if (status === 'pending') state.count.pending--
        else if (status === 'in_progress') state.count.in_progress--
        else if (status === 'done') state.count.done--
      }
    }
  },
  extraReducers: (builder) => {
    builder
      // Fetch all tasks
      .addCase(fetchTasks.pending, (state) => {
        state.status = 'loading'
        state.error = null
      })
      .addCase(fetchTasks.fulfilled, (state, action) => {
        state.status = 'succeeded'
        state.list = Array.isArray(action.payload) ? action.payload : []
        // Calculate counts
        state.count = {
          pending: state.list.filter(t => t.status === 'pending').length,
          in_progress: state.list.filter(t => t.status === 'in_progress').length,
          done: state.list.filter(t => t.status === 'done').length
        }
      })
      .addCase(fetchTasks.rejected, (state, action) => {
        state.status = 'failed'
        state.error = action.payload
      })
      // Create task
      .addCase(createTask.pending, (state) => {
        state.status = 'loading'
        state.error = null
      })
      .addCase(createTask.fulfilled, (state, action) => {
        state.status = 'succeeded'
        state.list.unshift(action.payload)
        if (action.payload.status === 'pending') state.count.pending++
      })
      .addCase(createTask.rejected, (state, action) => {
        state.status = 'failed'
        state.error = action.payload
      })
      // Update task
      .addCase(updateTask.pending, (state) => {
        state.error = null
      })
      .addCase(updateTask.fulfilled, (state, action) => {
        const idx = state.list.findIndex(t => t.id === action.payload.id)
        if (idx !== -1) {
          const oldStatus = state.list[idx].status
          const newStatus = action.payload.status || oldStatus
          state.list[idx] = { ...state.list[idx], ...action.payload }
          // Update counts if status changed
          if (oldStatus !== newStatus) {
            if (oldStatus === 'pending') state.count.pending--
            else if (oldStatus === 'in_progress') state.count.in_progress--
            else if (oldStatus === 'done') state.count.done--
            if (newStatus === 'pending') state.count.pending++
            else if (newStatus === 'in_progress') state.count.in_progress++
            else if (newStatus === 'done') state.count.done++
          }
        }
      })
      .addCase(updateTask.rejected, (state, action) => {
        state.error = action.payload
      })
      // Delete task
      .addCase(deleteTask.pending, (state) => {
        state.error = null
      })
      .addCase(deleteTask.fulfilled, (state, action) => {
        const idx = state.list.findIndex(t => t.id === action.payload)
        if (idx !== -1) {
          const status = state.list[idx].status
          state.list.splice(idx, 1)
          // Update counts
          if (status === 'pending') state.count.pending--
          else if (status === 'in_progress') state.count.in_progress--
          else if (status === 'done') state.count.done--
        }
      })
      .addCase(deleteTask.rejected, (state, action) => {
        state.error = action.payload
      })
      // Fetch by status/filter
      .addCase(fetchTasksByStatus.pending, (state) => {
        state.status = 'loading'
        state.error = null
      })
      .addCase(fetchTasksByStatus.fulfilled, (state, action) => {
        state.status = 'succeeded'
        state.list = Array.isArray(action.payload) ? action.payload : []
      })
      .addCase(fetchTasksByStatus.rejected, (state, action) => {
        state.status = 'failed'
        state.error = action.payload
      })
  }
})

export const { setFilter, clearTasks, addTask, updateTaskInState, removeTask } = tasksSlice.actions
export default tasksSlice.reducer
