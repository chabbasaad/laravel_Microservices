import axios, {AxiosError} from "axios";
import {toast} from "react-toastify";
import {EventCreateRequest, FetchEventsResponse} from "../model/event.tsx";
import {Event} from "../model/event.tsx";


const API_URL = import.meta.env.VITE_API_URL;
const getAuthHeaders = () => ({
    Authorization: `Bearer ${localStorage.getItem("user_token")}`,
    "Content-Type": "application/json",
});



export const fetchEvents = async (): Promise<FetchEventsResponse> => {
    try {
        const response = await axios.get<FetchEventsResponse>(`${API_URL}events`, { headers: getAuthHeaders() });
        return response.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response) {
            const errorMessage = error.response.data?.message || "Une erreur est survenue";
            console.log(errorMessage)
        } else {
            toast.error("Erreur inconnue");
        }
        throw error;
    }
};


export const fetchEvent = async (id: number): Promise<Event> => {
    try {
        const response = await axios.get<Event>(`${API_URL}events/${id}`, { headers: getAuthHeaders() });
        return response.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response) {
            const errorMessage = error.response.data?.message || "Une erreur est survenue";
            console.log(errorMessage)
        } else {
            toast.error("Erreur inconnue");
        }
        throw error;
    }
};


export const createEvent = async (params: EventCreateRequest): Promise<Event> => {
    try {
        const response = await axios.post<Event>(`${API_URL}events`,params, { headers: getAuthHeaders() });
        toast.success("Success");
        return response.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response) {
            const errorData = error.response.data;
            if (errorData.errors) {
                const errorMessages = Object.values(errorData.errors).flat();
                errorMessages.forEach((msg : any) => toast.error(msg));
            } else {
                toast.error(errorData.message || "Une erreur est survenue");
            }
        } else {
            toast.error("Erreur inconnue");
        }
        throw error;
    }
};


export const deleteEvent = async (id: number): Promise<Event> => {
    try {
        const response = await axios.delete<Event>(`${API_URL}events/${id}`, { headers: getAuthHeaders() });
        toast.success("Success");
        return response.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response) {
            const errorMessage = error.response.data?.message || "Une erreur est survenue";
            toast.error(errorMessage)
        } else {
            toast.error("Erreur inconnue");
        }
        throw error;
    }
};

export const updateEvent = async (id: number, params: any): Promise<Event> => {
    try {
        const response = await axios.put<Event>(`${API_URL}events/${id}`, params, { headers: getAuthHeaders() });
        toast.success("Success");
        return response.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response) {
            const errorMessage = error.response.data?.message || "Une erreur est survenue";
            toast.error(errorMessage)
        } else {
            toast.error("Erreur inconnue");
        }
        throw error;
    }
};

