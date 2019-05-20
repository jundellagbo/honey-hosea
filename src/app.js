import axios from "axios";
const session = localStorage.getItem("userToken");
export const user = session ? JSON.parse(session) : null;
export const postHeader = {
    headers: {
        "Content-Type": "application/x-www-form-urlencoded"
    }
};
export const api = axios.create({
    baseURL: "http://localhost/app/api",
    timeout: 5000,
});