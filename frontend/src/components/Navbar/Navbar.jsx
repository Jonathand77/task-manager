import React from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useSelector, useDispatch } from 'react-redux'
import { logout } from '../../features/auth/authSlice'
import styles from './Navbar.module.css'

export default function Navbar() {
  const user = useSelector(state => state.user.user)
  const token = useSelector(state => state.user.token)
  const dispatch = useDispatch()
  const navigate = useNavigate()

  const handleLogout = () => {
    dispatch(logout())
    navigate('/login')
  }

  return (
    <nav className={styles.navbar}>
      <div className={styles.container}>
        <Link to="/" className={styles.logo}>
          <span>✓</span>
          Task Manager
        </Link>

        <div className={styles.nav}>
          {token ? (
            <>
              <span className={styles.userName}>
                👤 {user?.name || user?.email}
              </span>
              <Link to="/tasks" className={styles.navLink}>
                Mis Tareas
              </Link>
              <button
                onClick={handleLogout}
                className={styles.logoutButton}
              >
                Cerrar Sesión
              </button>
            </>
          ) : (
            <>
              <Link to="/login" className={styles.authLink}>
                Iniciar Sesión
              </Link>
              <Link to="/register" className={styles.registerLink}>
                Registrarse
              </Link>
            </>
          )}
        </div>
      </div>
    </nav>
  )
}
