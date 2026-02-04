import { createSlice } from '@reduxjs/toolkit'

const initialState = {
  list: [],
  status: 'idle'
}

const tasksSlice = createSlice({
  name: 'tasks',
  initialState,
  reducers: {
    setTasks(state, action) {
      state.list = action.payload
    },
    addTask(state, action) {
      state.list.unshift(action.payload)
    },
    updateTask(state, action) {
      const idx = state.list.findIndex(t => t.id === action.payload.id)
      if (idx !== -1) state.list[idx] = action.payload
    }
  }
})

export const { setTasks, addTask, updateTask } = tasksSlice.actions
export default tasksSlice.reducer
