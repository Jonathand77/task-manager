import React, { useState } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { useDispatch, useSelector } from 'react-redux'
import { registerUser } from '../../features/auth/authSlice'
import Layout from '../../components/Layout/Layout'
import { useToast } from '../../contexts/ToastContext'
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
  const { showSuccess, showError, showWarning } = useToast()

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(null)

    // Validación de campos
    if (!name.trim()) {
      showWarning('El nombre es obligatorio')
      return
    }
    if (!email.trim()) {
      showWarning('El email es obligatorio')
      return
    }
    if (!password.trim()) {
      showWarning('La contraseña es obligatoria')
      return
    }
    if (password.length < 8) {
      showWarning('La contraseña debe tener al menos 8 caracteres')
      return
    }

    try {
      const resultAction = await dispatch(registerUser({ email, password, name }))
      if (registerUser.fulfilled.match(resultAction)) {
        showSuccess('¡Cuenta creada exitosamente! Bienvenido')
        setTimeout(() => navigate('/dashboard'), 500)
      } else {
        const errorMsg = resultAction.payload?.error || 'Error al crear la cuenta'
        setError(errorMsg)
        showError(errorMsg)
      }
    } catch (err) {
      const errorMsg = err.response?.data?.message || 'Error al crear la cuenta'
      setError(errorMsg)
      showError(errorMsg)
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
