import React from 'react'
import { Navigate } from 'react-router-dom'
import { useSelector } from 'react-redux'

export default function ProtectedRoute({ children }) {
  const userState = useSelector(state => state.user)
  if (!userState || !userState.token) {
    return <Navigate to="/login" replace />
  }
  return children
}
