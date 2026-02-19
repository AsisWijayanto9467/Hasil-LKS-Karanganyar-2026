import React, { useEffect, useState } from 'react'
import Main from '../Layouts/Main'
import api from '../../services/api';
import { useNavigate } from 'react-router-dom';

export default function Admin() {
    const [loading, setLoading] = useState(true);
    const [adminss, setAdmins] = useState([]);
    const [error, setError] = useState("");
    const navigate = useNavigate();

    const fetchData = async() => {
        try {
            const res = await api.get("/admins");

            setAdmins(res.data.content);
        } catch (error) {
            console.log(error)
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        fetchData();
    }, [])

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
                <h1>adminss List</h1>

                <table className="table">
                    <thead>
                        <tr>
                        <th>adminsname</th>
                        <th>login_last_at</th>
                        <th>created at</th>
                        <th>updated at</th>
                        </tr>
                    </thead>
                    <tbody>
                        {adminss.map((admins, idx) => (
                        <tr key={idx}>
                            <td>{admins.username}</td>
                            <td>{admins.last_login_at ?? "-"}</td>
                            <td>{admins.created_at}</td>
                            <td>{admins.updated_at}</td>
                        </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}
