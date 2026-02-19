

import React, { useEffect, useState } from 'react'
import api from '../../services/api';
import Main from '../Layouts/Main';

export default function Games() {
    const [page, setPage] = useState(0);
    const [size, setSize] = useState(10);
    const [sortBy, setSortBy] = useState("title");
    const [sortDir, setSortDir] = useState("title");
    const [games, setGames] = useState([]);
    const [loading, setloading] = useState();
    const [totalElement, setTotalElement] = useState(0);

    const fetchData = async() => {
        setloading(true);

        try {
            const res = await api.get("/games", {
                params: {page, size, sortBy, sortDir}
            });

            setGames(res.data.contents);
            setTotalElement(res.data.totalElement);
        } catch (err) {
            console.log(err);
        } finally {
            setloading(false);
        }
    }

    useEffect(() => {
        fetchData();
    }, [page, sortBy, sortDir]);

    const pageCount = Math.ceil(totalElement / size);
    const isLastPage = (page + 1) * size >= totalElement;

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
            <div className="container">
                <div className="d-flex">
                    <div className="me-3">
                        <label className="form-label me-3">Title</label>
                        <select
                            className="form-select" 
                            value={sortBy} 
                            onChange={(e) => setSortBy(e.target.value)}
                        >
                            <option value="title">title</option>
                            <option value="popular">popular</option>
                            <option value="uploaddate">Upload Date</option>
                        </select>
                    </div>
                    <div className="me-3">
                        <label className="form-label me-3">Title</label>
                        <select
                            className="form-select" 
                            value={sortDir} 
                            onChange={(e) => setSortDir(e.target.value)}
                        >
                            <option value="asc">asc</option>
                            <option value="desc">desc</option>
                        </select>
                    </div>
                </div>

                {games.map((game, idx) => (
                    <div className="crad" key={idx}>
                        <div className="row g-0"></div>
                    </div>
                ))}

                <div className="d-flex justify-content-between">
                    <button className="btn btn-primary" disabled={page === 0} onClick={() => setPage(page - 1)}>Prev</button>
                    <span>this is page {page} of {pageCount}</span>
                    <button className="btn btn-primary" disabled={isLastPage} onClick={() => setPage(page + 1)}>Next</button>
                </div>
            </div>
        </>
    )
}
