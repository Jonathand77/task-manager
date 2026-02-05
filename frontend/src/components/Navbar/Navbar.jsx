import React from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useSelector, useDispatch } from 'react-redux'
import { logout } from '../../features/auth/authSlice'
import { useToast } from '../../contexts/ToastContext'
import WebSocketIndicator from '../WebSocketIndicator/WebSocketIndicator'
import logoBodyTech from '../../assets/LogoBodyTech.png'
import userLogo from '../../assets/user-logo.svg'
import styles from './Navbar.module.css'

export default function Navbar() {
  const user = useSelector(state => state.user.user)
  const token = useSelector(state => state.user.token)
  const dispatch = useDispatch()
  const navigate = useNavigate()
  const { showSuccess, showError } = useToast()

  const handleLogout = () => {
    try {
      dispatch(logout())
      showSuccess('¡Sesión cerrada exitosamente!')
      setTimeout(() => navigate('/login'), 500)
    } catch (err) {
      showError('Error al cerrar sesión')
    }
  }

  return (
    <nav className={styles.navbar}>
      <div className={styles.container}>
        <Link to="/" className={styles.logo}>
          <img src={logoBodyTech} alt="BodyTech" className={styles.brandLogo} />
          Task Manager
        </Link>

        <div className={styles.nav}>
          {token ? (
            <>
              <WebSocketIndicator />
              <span className={styles.userName}>
                <img src={userLogo} alt="Usuario" className={styles.userLogo} />
                {user?.name || user?.email}
              </span>
              <Link to="/dashboard" className={styles.navLink}>
                Dashboard
              </Link>
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
