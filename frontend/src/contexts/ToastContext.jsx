import React, { createContext, useContext, useState, useCallback } from 'react'
import ToastContainer from '../components/ToastContainer/ToastContainer'

const ToastContext = createContext()

let toastId = 0

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([])

  const addToast = useCallback((message, type = 'info', duration = 4000) => {
    const id = toastId++
    setToasts(prev => [...prev, { id, message, type, duration }])
  }, [])

  const removeToast = useCallback((id) => {
    setToasts(prev => prev.filter(toast => toast.id !== id))
  }, [])

  const showSuccess = useCallback((message) => {
    addToast(message, 'success')
  }, [addToast])

  const showError = useCallback((message) => {
    addToast(message, 'error', 5000)
  }, [addToast])

  const showWarning = useCallback((message) => {
    addToast(message, 'warning')
  }, [addToast])

  const showInfo = useCallback((message) => {
    addToast(message, 'info')
  }, [addToast])

  return (
    <ToastContext.Provider value={{ addToast, showSuccess, showError, showWarning, showInfo }}>
      {children}
      <ToastContainer toasts={toasts} removeToast={removeToast} />
    </ToastContext.Provider>
  )
}

export function useToast() {
  const context = useContext(ToastContext)
  if (!context) {
    throw new Error('useToast must be used within a ToastProvider')
  }
  return context
}
