import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import api from '../../services/api'
import { setUser, clearUser } from '../user/userSlice'

// Async thunks for login and register
export const registerUser = createAsyncThunk('auth/register', async (payload, { dispatch, rejectWithValue }) => {
  try {
    const res = await api.post('/register', payload)
    // store user via user slice
    dispatch(setUser({ user: { id: res.data.id, email: res.data.email, name: res.data.name }, token: res.data.token }))
    return res.data
  } catch (err) {
    return rejectWithValue(err.response?.data || { error: 'Registration failed' })
  }
})

export const loginUser = createAsyncThunk('auth/login', async (payload, { dispatch, rejectWithValue }) => {
  try {
    const res = await api.post('/login', payload)
    dispatch(setUser({ user: { id: res.data.id, email: res.data.email, name: res.data.name }, token: res.data.token }))
    return res.data
  } catch (err) {
    return rejectWithValue(err.response?.data || { error: 'Login failed' })
  }
})

export const logout = createAsyncThunk('auth/logout', async (_, { dispatch }) => {
  dispatch(clearUser())
  return true
})

const initialState = {
  status: 'idle',
  error: null
}

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(registerUser.pending, (state) => {
        state.status = 'loading'
        state.error = null
      })
      .addCase(registerUser.fulfilled, (state) => {
        state.status = 'succeeded'
      })
      .addCase(registerUser.rejected, (state, action) => {
        state.status = 'failed'
        state.error = action.payload || action.error
      })
      .addCase(loginUser.pending, (state) => {
        state.status = 'loading'
        state.error = null
      })
      .addCase(loginUser.fulfilled, (state) => {
        state.status = 'succeeded'
      })
      .addCase(loginUser.rejected, (state, action) => {
        state.status = 'failed'
        state.error = action.payload || action.error
      })
      .addCase(logout.fulfilled, (state) => {
        state.status = 'idle'
        state.error = null
      })
  }
})

export default authSlice.reducer
