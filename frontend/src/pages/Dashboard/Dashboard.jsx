import React, { useEffect, useMemo } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import Layout from '../../components/Layout/Layout'
import { fetchTasks } from '../../features/tasks/tasksSlice'
import styles from './Dashboard.module.css'

export default function Dashboard() {
  const dispatch = useDispatch()
  const tasks = useSelector(state => state.tasks.list)
  const status = useSelector(state => state.tasks.status)

  useEffect(() => {
    if (status === 'idle' || tasks.length === 0) {
      dispatch(fetchTasks())
    }
  }, [dispatch, status, tasks.length])

  const stats = useMemo(() => {
    const today = new Date().toDateString()
    const isToday = (dateValue) => {
      if (!dateValue) return false
      const d = new Date(dateValue)
      if (Number.isNaN(d.getTime())) return false
      return d.toDateString() === today
    }

    const total = tasks.length
    const pending = tasks.filter(t => t.status === 'pending').length
    const inProgress = tasks.filter(t => t.status === 'in_progress').length
    const done = tasks.filter(t => t.status === 'done').length
    const createdToday = tasks.filter(t => isToday(t.created_at)).length
    const completedToday = tasks.filter(t => t.status === 'done' && isToday(t.updated_at || t.created_at)).length
    const updatedToday = tasks.filter(t => isToday(t.updated_at)).length

    const completionRate = total ? Math.round((done / total) * 100) : 0
    const inProgressRate = total ? Math.round((inProgress / total) * 100) : 0
    const pendingRate = total ? Math.round((pending / total) * 100) : 0

    return {
      total,
      pending,
      inProgress,
      done,
      createdToday,
      completedToday,
      updatedToday,
      completionRate,
      inProgressRate,
      pendingRate
    }
  }, [tasks])

  return (
    <Layout>
      <div className={styles.container}>
        <div className={styles.header}>
          <div>
            <h2 className={styles.title}>Dashboard</h2>
            <p className={styles.subtitle}>Resumen gráfico de tus tareas</p>
          </div>
        </div>

        <div className={styles.kpiGrid}>
          <div className={styles.kpiCard}>
            <p className={styles.kpiLabel}>Total de tareas</p>
            <p className={styles.kpiValue}>{stats.total}</p>
          </div>
          <div className={`${styles.kpiCard} ${styles.kpiAccent}`}>
            <p className={styles.kpiLabel}>Creadas hoy</p>
            <p className={styles.kpiValue}>{stats.createdToday}</p>
          </div>
          <div className={`${styles.kpiCard} ${styles.kpiAccent}`}>
            <p className={styles.kpiLabel}>Completadas hoy</p>
            <p className={styles.kpiValue}>{stats.completedToday}</p>
          </div>
          <div className={styles.kpiCard}>
            <p className={styles.kpiLabel}>Actualizadas hoy</p>
            <p className={styles.kpiValue}>{stats.updatedToday}</p>
          </div>
        </div>

        <div className={styles.statusGrid}>
          <div className={`${styles.statusCard} ${styles.statusPending}`}>
            <p className={styles.statusLabel}>Pendientes</p>
            <p className={styles.statusValue}>{stats.pending}</p>
            <div className={styles.statusBar}>
              <div className={styles.pendingBar} style={{ width: `${stats.pendingRate}%` }} />
            </div>
          </div>
          <div className={`${styles.statusCard} ${styles.statusProgress}`}>
            <p className={styles.statusLabel}>En progreso</p>
            <p className={styles.statusValue}>{stats.inProgress}</p>
            <div className={styles.statusBar}>
              <div className={styles.progressBar} style={{ width: `${stats.inProgressRate}%` }} />
            </div>
          </div>
          <div className={`${styles.statusCard} ${styles.statusDone}`}>
            <p className={styles.statusLabel}>Completadas</p>
            <p className={styles.statusValue}>{stats.done}</p>
            <div className={styles.statusBar}>
              <div className={styles.doneBar} style={{ width: `${stats.completionRate}%` }} />
            </div>
          </div>
        </div>

        <div className={styles.rateGrid}>
          <div className={styles.rateCard}>
            <div
              className={styles.rateChart}
              style={{
                background: `conic-gradient(#10b981 ${stats.completionRate}%, rgba(15, 40, 48, 0.1) 0)`
              }}
            >
              <div className={styles.rateInner}>
                <span className={styles.rateValue}>{stats.completionRate}%</span>
              </div>
            </div>
            <p className={styles.rateLabel}>Tasa de completado</p>
          </div>
          <div className={styles.rateCard}>
            <div
              className={styles.rateChart}
              style={{
                background: `conic-gradient(#3b82f6 ${stats.inProgressRate}%, rgba(15, 40, 48, 0.1) 0)`
              }}
            >
              <div className={styles.rateInner}>
                <span className={styles.rateValue}>{stats.inProgressRate}%</span>
              </div>
            </div>
            <p className={styles.rateLabel}>Tasa en progreso</p>
          </div>
          <div className={styles.rateCard}>
            <div
              className={styles.rateChart}
              style={{
                background: `conic-gradient(#f59e0b ${stats.pendingRate}%, rgba(15, 40, 48, 0.1) 0)`
              }}
            >
              <div className={styles.rateInner}>
                <span className={styles.rateValue}>{stats.pendingRate}%</span>
              </div>
            </div>
            <p className={styles.rateLabel}>Tasa pendientes</p>
          </div>
        </div>
      </div>
    </Layout>
  )
}
