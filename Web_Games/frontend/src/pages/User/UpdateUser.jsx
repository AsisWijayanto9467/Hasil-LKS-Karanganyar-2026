import React, { useEffect, useState } from "react";
import Main from "../Layouts/Main";
import { useNavigate, useParams } from "react-router-dom";
import api from "../../services/api";

export default function UpdateUser() {
    const navigate = useNavigate();
    const {id, usernameD} = useParams();

    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");

    const [loading, setLoading] = useState(false);
    const [loadingData, setLoadingData] = useState(true);
    const [error, setError] = useState("");

    const fetchData = async() => {
        try {
            const resData = await api.get(`/users/${usernameD}`);
            console.log(`/users/${usernameD}`);

            setUsername(resData.data.username);
        } catch (err) {
            console.log(err);
        } finally {
            setLoadingData(false)
        }
    }

    useEffect(() => {
        fetchData();
    }, [])


    const handleSubmit = async(e) => {
        e.preventDefault();
        setLoading(true);
        setError("");


        try {
            await api.post(`/users/${id}`, {
                username,
                password
            });

            navigate("/users");
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
        <Main />

        <div className="container mt-4">
            <h4 className="text-center">Update Users</h4>

            <form onSubmit={handleSubmit}>
            <div className="mb-3">
                <label className="form-label">Username</label>
                <input
                className="form-control"
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                required
                />
            </div>
            <div className="mb-3">
                <label className="form-label">Password</label>
                <input
                className="form-control"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                />
            </div>

            <div className="d-flex justify-content-between">
                <button
                className="btn btn-primary mt-3"
                type="submit"
                disabled={loading}
                >
                {loading ? "Submitting..." : "Submit"}
                </button>
                <button
                onClick={() => navigate("/users")}
                className="btn btn-secondary"
                >
                Kembali
                </button>
            </div>
            </form>
        </div>
        </>
    );
}
