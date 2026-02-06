# Task Manager - Frontend

Frontend en React + Vite + Redux Toolkit.

## 📦 Estructura del Proyecto

```
task-manager/
│
├──  RAÍZ
│   ├── .env.example
│   ├── .gitignore
│   ├── .git/
│   ├── .github/
│   ├── docker-compose.yml
│   ├── README.md
│   ├── QUICKSTART_PROYECT.md
│
└── 💻 FRONTEND/
    ├── src/
    │   ├── components/
    │   │   ├── Footer/
    │   │   ├── Layout/
    │   │   ├── Navbar/
    │   │   ├── ProtectedRoute/
    │   │   ├── TaskForm/
    │   │   ├── TaskItem/
    │   │   ├── Toast/
    │   │   ├── ToastContainer/
    │   │   └── WebSocketIndicator/
    │   ├── contexts/
    │   ├── features/
    │   │   └── auth/
    │   │   └── tasks/
    │   │   └── user/
    │   ├── hooks/
    │   │   └── useWebSocket.js
    │   ├── pages/
    │   ├── services/
    │   ├── store/
    │   ├── assets/
    │   ├── App.jsx
    │   ├── main.jsx
    │   └── styles.css
    ├── .env.example
    ├── package.json
    ├── package-lock.json
    ├── Dockerfile
    ├── vite.config.js
    ├── index.html
    └── node_modules/
```