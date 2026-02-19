import api from "./api";


export const uploadGames = (slug, file) => {
    const token = localStorage.getItem("token");

    const formData = new FormData;

    formData.append("zipfile", file);
    formData.append("token", token);

    return api.post(`/games/${slug}/upload`, formData);
}


// export const uploadGames = (slug, file) => {
//     const token = localStorage.getItem("token");

//     const formData = new FormData;
//     formData.append("zipfile", file);
//     formData.append("token", token);

//     return api.post(`/games/${slug}/upload`, formData);
// }