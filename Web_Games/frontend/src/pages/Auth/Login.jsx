import React, { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom';
import api from '../../services/api';

export default function Login() {
    const navigate = useNavigate();

    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");

    

    const handleSubmit = async(e) => {
        e.preventDefault();
        setLoading(true);
        setError("");

        console.log(username, password);

        try {
            console.log(username, password);
            const res =  await api.post("/auth/signin", {
                username,
                password
            });

            const isAdmin = username.toLocaleLowerCase.includes("admin");

            localStorage.setItem("role", isAdmin ? "admin" : "user");

            const token = res.data.token;
            localStorage.setItem("token", token);

            navigate("/dashboard");
        } catch (err) {
            if(err.response) {
                const data = err.response.data;

                if(data.errors) {
                    const message = Object.values(data.errors).flat().join(" | ");
                    setError(message);
                } else if(data.message) {
                    setError(data.message);
                } else {
                    setError("Terjadi kesalahan server")
                }
            } else if(err.request) {
                setError("Kesalahan server")
            } else {
                setError("Terjadi error");
            }
        } finally {
            setLoading(false)
        }

        
    }

    return (
        <>
            <div className="container d-flex justify-content-center align-items-center vh-100 bg-light">
                <div className="card shadow" style={{ minWidth: 300, maxWidth:400, width: "100%" }}>
                    <div className="p-4">
                        <h4 className="text-center mb-3">Login</h4>

                        

                        <form onSubmit={handleSubmit}>
                            <div className="mb-3">
                                <label className="form-label">Username</label>
                                <input className="form-control" type="text" value={username} onChange={(e) => setUsername(e.target.value)} required/>
                            </div>
                            <div className="mb-3">
                                <label className="form-label">Password</label>
                                <input className="form-control" type="password" value={password} onChange={(e) => setPassword(e.target.value)} required/>
                            </div>

                            <span className="mb-3">Sudah punya akun? <Link to="/register">Register</Link></span>

                            <button className="btn btn-primary w-100 mt-3" type="submit" disabled={loading}>{loading ? "Logging in..." : "Login"}</button>
                        </form>
                    </div>
                </div>
            </div>
        </>
    )
}
