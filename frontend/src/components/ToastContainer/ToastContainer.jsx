import React from 'react'
import Toast from '../Toast/Toast'
import styles from './ToastContainer.module.css'

export default function ToastContainer({ toasts, removeToast }) {
  return (
    <div className={styles.container}>
      {toasts.map(toast => (
        <Toast
          key={toast.id}
          message={toast.message}
          type={toast.type}
          onClose={() => removeToast(toast.id)}
          duration={toast.duration}
        />
      ))}
    </div>
  )
}
