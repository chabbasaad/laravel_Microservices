import { Navigate } from "react-router-dom";
import {JSX} from "react";
const isAuthenticated = () => {
    const userData = localStorage.getItem("user_data");

    if (!userData) {
        return false;
    }

    try {
        const user = JSON.parse(userData);
        return user?.role === "admin" || user?.role === "event_creator";
    } catch (error) {
        console.error("Erreur de parsing des données utilisateur :", error);
        return false;
    }
};
const AuthGuard = ({ children }: { children: JSX.Element }) => {
    return isAuthenticated() ? children : <Navigate to="/" />;
};


export default AuthGuard;
