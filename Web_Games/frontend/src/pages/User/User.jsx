import React, { useEffect, useState } from 'react'
import Main from '../Layouts/Main'
import api from '../../services/api';
import { useNavigate } from 'react-router-dom';

export default function User() {
    const [loading, setLoading] = useState(true);
    const [users, setUsers] = useState([]);
    const [error, setError] = useState("");
    const navigate = useNavigate();

    const role = localStorage.getItem("role");
    

    const fetchData = async() => {
        try {
            const res = await api.get("/users");

            setUsers(res.data.content);
        } catch (error) {
            console.log(error)
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        fetchData();
    }, [])


    const handleDelete = async(id) => {
        if(!window.confirm("Yakin menghapus ini?"))return;
        try {
            await api.delete(`/users/${id}`)

            fetchData();
        } catch (error) {
            console.log(error)
        }
    }

    if(loading) {
        return (
            <>
                <Main />

                <h4>Loading....</h4>
            </>
        )
    }

    return (
        <>
            <Main />

            <div className="container mt-3">
                <h1>Users List</h1>

                {role === "admin" && (
                    <button className="btn btn-primary" onClick={() => navigate("/users/create")}>
                        Tambah User
                    </button>
                )}

                <button className="btn btn-primary" onClick={() => navigate("/users/create")}>
                    Tambah User
                </button>

                <table className="table">
                    <thead>
                        <tr>
                            <th>Id.User</th>
                            <th>username</th>
                            <th>login_last_at</th>
                            <th>created at</th>
                            <th>updated at</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {users.map((user, idx) => (
                            <tr key={idx}>
                                <td>{user.id}</td>
                                <td>{user.username}</td>
                                <td>{user.last_login_at ?? "-"}</td>
                                <td>{user.created_at}</td>
                                <td>{user.updated_at}</td>
                                <td>
                                    <div className="d-flex">
                                        <button className="btn btn-danger btn-sm me-3" onClick={() => handleDelete(user.id)}>Delete</button>
                                        <button className="btn btn-primary btn-sm" onClick={() => navigate(`/users/edit/${user.id}/${user.username}`)}>Update</button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    )
}
