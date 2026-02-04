import React from 'react'
import styles from './Footer.module.css'

export default function Footer() {
  return (
    <footer className={styles.footer}>
      <div className={styles.container}>
        <p className={styles.title}>
          Task Manager - Gestiona tus tareas de forma eficiente
        </p>
        <p className={styles.copyright}>
          © {new Date().getFullYear()} Task Manager. Todos los derechos reservados.
        </p>
      </div>
    </footer>
  )
}
