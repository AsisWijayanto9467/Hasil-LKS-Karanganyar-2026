

import React from 'react'
import api from '../../services/api';
import { useNavigate } from 'react-router-dom';

export default function Main() {
    const navigate = useNavigate();

    const hanldeLogout = async() => {
        try {
            await api.post("/auth/signout");

            // localStoarge.removeItem("token");
            localStorage.clear();
            navigate("/");
        } catch (err) {
            console.log(err);
        }
    }

    return (
        <>
            <nav className="navbar navbar-expand-lg nvabra-dark bg-primary">
                <div className="container-fluid">
                    <span className="navbar-brand text-white">LKS Games</span>
                    <div className="d-flex ms-auto align-items-center text-white">
                        <span className="me-3">My Web</span>
                        <button className="btn btn-outline-light btn-sm" onClick={hanldeLogout}>Logout</button>
                    </div>
                </div>
            </nav>


            <div className="mt-4 container d-flex">
                <button className="btn-success btn me-3" onClick={() => navigate("/users")}>Users</button>
                <button className="btn-success btn" onClick={() => navigate("/admins")}>Admins</button>
            </div>
        </>
    )
}
