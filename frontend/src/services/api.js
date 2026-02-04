import axios from 'axios'

const baseURL = import.meta.env.VITE_API_URL || import.meta.env.VITE_API || 'http://localhost:8000/api'

const api = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Attach Authorization header from localStorage if present
api.interceptors.request.use((config) => {
  try {
    const saved = JSON.parse(localStorage.getItem('user') || 'null')
    const token = saved?.token
    if (token) {
      config.headers = config.headers || {}
      config.headers.Authorization = `Bearer ${token}`
    }
  } catch (e) {
    // ignore
  }
  return config
})

// Global response handler for auth errors (401/403)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status
    if (status === 401 || status === 403) {
      try {
        // Clear local storage
        localStorage.removeItem('user')
        // If main app exposed a dispatcher, call it to update Redux state
        if (typeof window !== 'undefined' && window.__DISPATCH) {
          window.__DISPATCH({ type: 'user/clearUser' })
        }
      } catch (e) {
        // ignore
      }
      // Redirect to login page so user can re-authenticate
      if (typeof window !== 'undefined') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
