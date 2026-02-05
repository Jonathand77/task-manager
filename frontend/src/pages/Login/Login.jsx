import React, { useState } from 'react'
import { useDispatch } from 'react-redux'
import { loginUser } from '../../features/auth/authSlice'
import { useNavigate, Link } from 'react-router-dom'
import { useSelector } from 'react-redux'
import Layout from '../../components/Layout/Layout'
import { useToast } from '../../contexts/ToastContext'
import logo from '../../assets/user-logo.svg'
import styles from './Login.module.css'

export default function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState(null)
  const dispatch = useDispatch()
  const navigate = useNavigate()
  const authStatus = useSelector(state => state.auth.status)
  const { showSuccess, showError, showWarning } = useToast()

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(null)

    // Validación de campos
    if (!email.trim()) {
      showWarning('El email es obligatorio')
      return
    }
    if (!password.trim()) {
      showWarning('La contraseña es obligatoria')
      return
    }

    try {
      const resultAction = await dispatch(loginUser({ email, password }))
      if (loginUser.fulfilled.match(resultAction)) {
        showSuccess('¡Inicio de sesión exitoso! Bienvenido')
        setTimeout(() => navigate('/dashboard'), 500)
      } else {
        const errorMsg = resultAction.payload?.error || 'Error al iniciar sesión'
        setError(errorMsg)
        showError(errorMsg)
      }
    } catch (err) {
      const errorMsg = err.response?.data?.message || 'Error al iniciar sesión'
      setError(errorMsg)
      showError(errorMsg)
    }
  }

  return (
    <Layout>
      <div className={styles.authCard}>
        <img src={logo} alt="Usuario" className={styles.logo} />
        <h2 className={styles.title}>Iniciar Sesión</h2>
        
        <form onSubmit={handleSubmit}>
          <div className={styles.formGroup}>
            <label className={styles.label}>Email</label>
            <input 
              type="email"
              value={email} 
              onChange={e => setEmail(e.target.value)}
              placeholder="tu@email.com"
              required
              className={styles.input}
            />
          </div>
          
          <div className={styles.formGroup}>
            <label className={styles.label}>Contraseña</label>
            <input 
              type="password" 
              value={password} 
              onChange={e => setPassword(e.target.value)}
              placeholder="••••••••"
              required
              className={styles.input}
            />
          </div>
          
          {error && (
            <div className={styles.error}>
              {error}
            </div>
          )}
          
          <button 
            type="submit"
            disabled={authStatus === 'loading'}
            className={styles.submitButton}
          >
            {authStatus === 'loading' ? 'Ingresando...' : 'Iniciar Sesión'}
          </button>
        </form>
        
        <p className={styles.footer}>
          ¿No tienes cuenta?{' '}
          <Link to="/register" className={styles.link}>
            Regístrate aquí
          </Link>
        </p>
      </div>
    </Layout>
  )
}
