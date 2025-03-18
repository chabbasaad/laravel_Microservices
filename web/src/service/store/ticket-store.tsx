import { create } from "zustand";
import {createTicket, deleteTicket, fetchTickets} from "../services/ticket-service.tsx";
import {CreateTicketRequest, Ticket} from "../model/ticket.tsx";

interface TicketState {
    tickets: Ticket[];
    ticket : Ticket | null;
    ticketUser : Ticket[];
    loading: boolean;
    fetchTickets: (id : number) => Promise<void>;
    createTicket: (params : CreateTicketRequest) => Promise<void>;
    deleteTicket: (id : number) => Promise<void>;
}

const useTicketStore = create<TicketState>((set) => ({
    tickets: [],
    ticket: null ,
    ticketUser : [],
    loading : false,

    fetchTickets: async (id) => {
        try {
            const response = await fetchTickets(id);
            const newTickets = Array.isArray(response) ? response : [response];

            set((state) => {
                const existingTicketIds = state.ticketUser.map((ticket) => ticket.id);
                const uniqueNewTickets = newTickets.filter((ticket) => !existingTicketIds.includes(ticket.id));

                return { ticketUser: [...state.ticketUser, ...uniqueNewTickets] };
            });
        } catch (error) {
            console.error(error);
        }
    },

    createTicket: async (params) : Promise<void>  => {
        try {
            const response = await createTicket(params);
            const tickets = response.tickets
            set({ tickets });
        } catch (error) {
            console.error(error);
        }
    },

    deleteTicket: async (id) => {
        try {
            await deleteTicket(id);
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
