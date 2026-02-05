import React, { useState } from 'react'
import { useDispatch } from 'react-redux'
import { loginUser } from '../../features/auth/authSlice'
import { useNavigate, Link } from 'react-router-dom'
import { useSelector } from 'react-redux'
import Layout from '../../components/Layout/Layout'
import logo from '../../assets/user-logo.svg'
import styles from './Login.module.css'

export default function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState(null)
  const dispatch = useDispatch()
  const navigate = useNavigate()
  const authStatus = useSelector(state => state.auth.status)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(null)
    try {
      const resultAction = await dispatch(loginUser({ email, password }))
      if (loginUser.fulfilled.match(resultAction)) {
        navigate('/tasks')
      } else {
        setError(resultAction.payload?.error || 'Login failed')
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Login failed')
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
