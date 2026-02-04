import { createSlice } from '@reduxjs/toolkit'

const saved = JSON.parse(localStorage.getItem('user') || 'null') || { user: null, token: null }

const initialState = {
  user: saved.user,
  token: saved.token,
}

const userSlice = createSlice({
  name: 'user',
  initialState,
  reducers: {
    setUser(state, action) {
      state.user = action.payload.user
      state.token = action.payload.token
      localStorage.setItem('user', JSON.stringify({ user: state.user, token: state.token }))
    },
    clearUser(state) {
      state.user = null
      state.token = null
      localStorage.removeItem('user')
    }
  }
})

export const { setUser, clearUser } = userSlice.actions
export default userSlice.reducer
