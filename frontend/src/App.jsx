import React from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'
import Login from './pages/Login/Login'
import Register from './pages/Register/Register'
import TasksList from './pages/TasksList/TasksList'
import ProtectedRoute from './components/ProtectedRoute/ProtectedRoute'

function App() {
  return (
    <Routes>
      <Route path="/login" element={<Login />} />
      <Route path="/register" element={<Register />} />
      <Route path="/tasks" element={<ProtectedRoute><TasksList /></ProtectedRoute>} />
      <Route path="/" element={<Navigate to="/tasks" replace />} />
    </Routes>
  )
}

export default App
