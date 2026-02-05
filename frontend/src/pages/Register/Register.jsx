import React, { useState } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { useDispatch, useSelector } from 'react-redux'
import { registerUser } from '../../features/auth/authSlice'
import Layout from '../../components/Layout/Layout'
import logo from '../../assets/user-logo.svg'
import styles from './Register.module.css'

export default function Register() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [name, setName] = useState('')
  const [error, setError] = useState(null)
  const navigate = useNavigate()
  const dispatch = useDispatch()
  const authStatus = useSelector(state => state.auth.status)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(null)
    try {
      const resultAction = await dispatch(registerUser({ email, password, name }))
      if (registerUser.fulfilled.match(resultAction)) {
        navigate('/tasks')
      } else {
        setError(resultAction.payload?.error || 'Registration failed')
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Registration failed')
    }
  }

  return (
    <Layout>
      <div className={styles.authCard}>
        <img src={logo} alt="Usuario" className={styles.logo} />
        <h2 className={styles.title}>Crear Cuenta</h2>
        
        <form onSubmit={handleSubmit}>
          <div className={styles.formGroup}>
            <label className={styles.label}>Nombre</label>
            <input 
              type="text"
              value={name} 
              onChange={e => setName(e.target.value)}
              placeholder="Tu nombre"
              required
              className={styles.input}
            />
          </div>
          
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
              placeholder="Mínimo 8 caracteres"
              required
              className={styles.input}
            />
            <p className={styles.passwordHint}>
              Debe incluir mayúsculas, minúsculas, números y símbolos
            </p>
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
            {authStatus === 'loading' ? 'Creando cuenta...' : 'Registrarse'}
          </button>
        </form>
        
        <p className={styles.footer}>
          ¿Ya tienes cuenta?{' '}
          <Link to="/login" className={styles.link}>
            Inicia sesión aquí
          </Link>
        </p>
      </div>
    </Layout>
  )
}
