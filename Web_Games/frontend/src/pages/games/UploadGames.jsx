import React, { useState } from 'react'
import { uploadGames } from '../../services/gameService';

export default function UploadGames({slug}) {
    const [file, setFile] = useState(null);
    const [loading, setLoading] = useState(false);
    const [message, setmessage] = useState("");

    const handleSubmit = async(e) => {
        e.preventDefault();

        if(!file) {
            setmessage("Masukan data zip terlebih dahulu");
            return;
        }

        setLoading(true);
        setmessage("");

        try {
            const response = await uploadGames(slug, file);

            setmessage("Upload success");
            console.log(response.data);
        } catch (err) {
            const data = err.response.data;

            if(data?.errors) {
                const message = Object.values(data.errors).flat().join(" | ");
                setmessage(message);
            } else if(data?.message) {
                setmessage(data.message);
            } else {
                setmessage("terjadi kesalahan");
            }
        } finally {
            setLoading(false)
        }
    }
    
    return (
        <>
            <form onSubmit={handleSubmit} className="mt-2">
                <input 
                    type="file" 
                    accept='.zip'
                    onChange={(e) => setFile(e.target.files[0])}
                />

                <button className="btn btn-primary" type="submit">Submit</button>

                {message && (
                    <div>{message}</div>
                )}
            </form>
        </>
    )
}
