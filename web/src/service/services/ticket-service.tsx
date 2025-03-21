import axios, {AxiosError} from "axios";
import {toast} from "react-toastify";
import {TicketResponse} from "../model/event.tsx";
import {Event} from "../model/event.tsx";
import {CreateTicketRequest, CreateTicketResponse} from "../model/ticket.tsx";


const API_URL = import.meta.env.VITE_API_URL;
const getAuthHeaders = () => ({
    Authorization: `Bearer ${localStorage.getItem("user_token")}`,
    "Content-Type": "application/json",
});



export const fetchTickets = async (id : number): Promise<TicketResponse[]> => {
    try {
        const response = await axios.get<TicketResponse[]>(`${API_URL}tickets/user/${id}`, { headers: getAuthHeaders() });
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


export const createTicket = async (params: CreateTicketRequest): Promise<CreateTicketResponse> => {
    try {
        const response = await axios.post<CreateTicketResponse>(`${API_URL}tickets/purchase`,params, { headers: getAuthHeaders() });
        toast.success("Success");
        return response.data;
    } catch (error) {
        console.log(error)
        if (error instanceof AxiosError && error.response) {
            const errorData = error.response.data;
            if (errorData.errors) {
                const errorMessages = Object.values(errorData.errors).flat();
                errorMessages.forEach((msg : any) => toast.error(msg));
            } else {
                toast.error(errorData.message || error.response.data.error);
            }
        } else {
            toast.error("Erreur inconnue");
        }
        throw error;
    }
};


export const validateTicket = async (id: number): Promise<any> => {
    try {
        const response = await axios.post<CreateTicketResponse>(`${API_URL}tickets/${id}/validate`, { headers: getAuthHeaders() });
        toast.success("Success");
        return response.data;
    } catch (error) {
        console.log(error)
        if (error instanceof AxiosError && error.response) {
            const errorData = error.response.data;
            if (errorData.errors) {
                const errorMessages = Object.values(errorData.errors).flat();
                errorMessages.forEach((msg : any) => toast.error(msg));
            } else {
                toast.error(errorData.message || error.response.data.error);
            }
        } else {
            toast.error("Erreur inconnue");
        }
        throw error;
    }
};

export const deleteTicket = async (id: number,params : any): Promise<Event> => {
    try {
        const response = await axios.post<Event>(`${API_URL}tickets/${id}/cancel`, params,{ headers: getAuthHeaders() });
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

