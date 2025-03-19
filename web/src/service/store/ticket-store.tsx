import { create } from "zustand";
import {createTicket, deleteTicket, fetchTickets} from "../services/ticket-service.tsx";
import {CreateTicketRequest, Ticket} from "../model/ticket.tsx";

interface TicketState {
    tickets: Ticket[];
    ticket : Ticket | null;
    ticketUser : Ticket[];
    loading: boolean;
    isLoading : boolean,
    fetchTickets: (id : number) => Promise<void>;
    createTicket: (id : number,params : CreateTicketRequest) => Promise<void>;
    deleteTicket: (id : number,params : any) => Promise<void>;
}

const useTicketStore = create<TicketState>((set) => ({
    tickets: [],
    ticket: null ,
    ticketUser : [],
    loading : false,
    isLoading : false,

    fetchTickets: async (id) => {
         set({ isLoading: true });
        try {
            const response = await fetchTickets(id);
            set({ tickets: response, isLoading: false });
        } catch (error) {
            console.error(error);
            set({ isLoading: false });
        }
    },

    createTicket: async (id,params) : Promise<void>  => {
        try {
             await createTicket(params);
            const tickets = await fetchTickets(id);
            set({ tickets });
        } catch (error) {
            console.error(error);
        }
    },

    deleteTicket: async (id,params) => {
        try {
            await deleteTicket(id,params);
            set((state) => ({
                ticketUser: state.ticketUser.filter((h) => h.id !== id),
                ticket: state.ticket?.id === id ? null : state.ticket,
            }));
        } catch (error) {
            console.error(error);
        }
    },

}));

export default useTicketStore;
