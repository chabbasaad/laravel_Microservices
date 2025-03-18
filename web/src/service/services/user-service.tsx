import axios, {AxiosError} from "axios";
import {toast} from "react-toastify";
import {LoginUserRequest, LoginUserResponse, RegisterUserRequest, RegisterUserResponse, User} from "../model/user.tsx";


const API_URL = 'http://localhost:8000/api/v1/';
console.log(API_URL)

const getAuthHeaders = () => ({
    Authorization: `Bearer ${localStorage.getItem("user_token")}`,
    "Content-Type": "application/json",
});

export const register = async (params: Omit<RegisterUserRequest, "id">): Promise<RegisterUserResponse> => {
    try {
        const response = await axios.post<RegisterUserResponse>(API_URL +'auth/register', params);
        toast.success(response.data.message);
        return response.data;
    }catch (error) {
        const errorData = error.response.data;
        if (errorData.errors) {
            const errorMessages = Object.values(errorData.errors).flat();
            errorMessages.forEach((msg) => toast.error(msg));
        } else {
            toast.error(errorData.error|| "Une erreur est survenue");
        }
        throw error;

    }
};

export const login = async (param: Omit<LoginUserRequest, "id">): Promise<LoginUserResponse> => {
    try {
        const response = await axios.post<LoginUserResponse>(API_URL + `auth/login`,param)
        return response.data;
    } catch (error) {
        const errorData = error.response.data;
        if (errorData.errors) {
            const errorMessages = Object.values(errorData.errors).flat();
            errorMessages.forEach((msg) => toast.error(msg));
        } else {
            toast.error(errorData.error || "Une erreur est survenue");
        }
        throw error;
    }
};

export const fetchUser = async (id: number): Promise<User> => {
    try {
        const response = await axios.get<User>(`${API_URL}users/${id}`, { headers: getAuthHeaders() });
        console.log(response)
        return response.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response) {
            const errorMessage = error.response.data?.message || "Une erreur est survenue";
            toast.error(errorMessage);
        } else {
            toast.error("Erreur inconnue");
        }
        throw error;
    }
};

export const fetchUsers = async (): Promise<UserFetchResponseData> => {
    try {
        const response = await axios.get<UserFetchResponseData>(`${API_URL}users`, { headers: getAuthHeaders() });
        return response.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response) {
            const errorMessage = error.response.data?.message || "Une erreur est survenue";
            toast.error(errorMessage);
        } else {
            toast.error("Erreur inconnue");
        }
        throw error;
    }
};

export const updateUser = async (id: number, params: UserUpdateRequestData): Promise<UserUpdateResponseData> => {
    try {

        const response = await axios.put<UserUpdateResponseData>(`${API_URL}users/${id}`, params, { headers: getAuthHeaders() });
        toast.success(response.data.message);
        return response.data;

    }catch (error) {
        if (error instanceof AxiosError && error.response) {
            const errorMessage = error.response.data?.message || "Une erreur est survenue";
            toast.error(errorMessage);
        } else {
            toast.error("Erreur inconnue");
        }
        throw error;

    }
};

export const deleteUser = async (id: number): Promise<void> => {
    try {
        const response = await axios.delete(`${API_URL}users/${id}`, { headers: getAuthHeaders() });
        toast.success(response.data.message);
    } catch (error) {
        if (error instanceof AxiosError && error.response) {
            const errorMessage = error.response.data?.message || "Une erreur est survenue";
            toast.error(errorMessage);
        } else {
            toast.error("Erreur inconnue");
        }
        throw error;
    }
};