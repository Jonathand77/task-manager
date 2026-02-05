import React, { useEffect } from 'react'
import { useSelector } from 'react-redux'
import Navbar from '../Navbar/Navbar'
import Footer from '../Footer/Footer'
import { useWebSocket } from '../../hooks/useWebSocket'
import styles from './Layout.module.css'

export default function Layout({ children }) {
  const user = useSelector(state => state.user.user)
  const token = useSelector(state => state.user.token)

  // Conectar WebSocket cuando el usuario esté autenticado
  const { isConnected } = useWebSocket(user?.id, token)

  return (
    <div className={styles.layout}>
      <Navbar />
      <main className={styles.main}>
        {children}
      </main>
      <Footer />
    </div>
  )
}
