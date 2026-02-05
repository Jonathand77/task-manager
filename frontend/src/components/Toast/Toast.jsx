import React, { useEffect } from 'react'
import styles from './Toast.module.css'

export default function Toast({ message, type = 'info', onClose, duration = 4000 }) {
  useEffect(() => {
    const timer = setTimeout(() => {
      onClose()
    }, duration)

    return () => clearTimeout(timer)
  }, [duration, onClose])

  const getIcon = () => {
    switch (type) {
      case 'success':
        return '✓'
      case 'error':
        return '✕'
      case 'warning':
        return '⚠'
      case 'info':
      default:
        return 'ℹ'
    }
  }

  const getClassName = () => {
    switch (type) {
      case 'success':
        return `${styles.toast} ${styles.success}`
      case 'error':
        return `${styles.toast} ${styles.error}`
      case 'warning':
        return `${styles.toast} ${styles.warning}`
      case 'info':
      default:
        return `${styles.toast} ${styles.info}`
    }
  }

  return (
    <div className={getClassName()}>
      <div className={styles.icon}>{getIcon()}</div>
      <div className={styles.message}>{message}</div>
      <button className={styles.closeButton} onClick={onClose}>
        ✕
      </button>
    </div>
  )
}
