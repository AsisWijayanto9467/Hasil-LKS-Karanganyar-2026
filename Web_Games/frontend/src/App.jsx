import React from 'react'
import {BrowserRouter as Router, Routes, Route} from "react-router-dom"
import Login from './pages/Auth/Login'
import ProtectedRoute from './services/ProtectedRoute'
import Dashboard from './pages/Home/Dashboard'
import Register from './pages/Auth/Register'
import User from './pages/User/User'
import CreateUser from './pages/User/CreateUser'
import Admin from './pages/Admin/Admin'
import UpdateUser from './pages/User/UpdateUser'

export default function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/register" element={<Register />} />

        <Route element={<ProtectedRoute allowedRole={["admin"]} />}>
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/admins" element={<Admin /> } />
          <Route path="/users" element={<User /> } />
          <Route path="/users/create" element={<CreateUser /> } />
          <Route path="/users/edit/:id/:usernameD"element={<UpdateUser /> } />
        </Route>
      </Routes>
    </Router>
  )
}
