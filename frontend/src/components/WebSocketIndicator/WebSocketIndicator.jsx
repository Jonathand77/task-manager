import React, { useEffect, useState } from 'react'
import { useSelector } from 'react-redux'
import { useWebSocket } from '../../hooks/useWebSocket'
import styles from './WebSocketIndicator.module.css'

export default function WebSocketIndicator() {
  const user = useSelector(state => state.user.user)
  const token = useSelector(state => state.user.token)
  const { isConnected } = useWebSocket(user?.id, token)

  if (!user || !token) return null

  return (
    <div className={styles.wsIndicator}>
      <span className={`${styles.wsStatus} ${isConnected ? styles.wsConnected : styles.wsDisconnected}`} />
      {isConnected ? 'Sincronizado' : 'Offline'}
    </div>
  )
}
