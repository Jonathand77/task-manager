import { createSlice } from '@reduxjs/toolkit'

const initialState = {
  user: null,
  token: null,
}

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    setCredentials(state, action) {
      state.user = action.payload.user
      state.token = action.payload.token
    },
    clearCredentials(state) {
      state.user = null
      state.token = null
    }
  }
})

export const { setCredentials, clearCredentials } = authSlice.actions
export default authSlice.reducer
