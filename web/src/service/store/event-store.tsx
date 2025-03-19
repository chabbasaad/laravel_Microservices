import { create } from "zustand";
import {createEvent, deleteEvent, fetchEvent, fetchEvents, updateEvent} from "../services/event-service.tsx";
import {Event, EventCreateRequest} from "../model/event.tsx";

interface EventState {
    events: Event[];
    event: Event | null;
    loading: boolean;
    isLoading : boolean,
    fetchEvents: () => Promise<void>;
    fetchEvent: (id : number) => Promise<Event | null>;
    createEvent: (params : EventCreateRequest) => Promise<Event | null>;
    deleteEvent: (id : number) => Promise<void>;
    updateEvent: (id : number, params : Event) => Promise<void>;


}

const useEventStore = create<EventState>((set) => ({
    events: [],
    event: null ,
    loading : false,
    isLoading : false,

    fetchEvents: async () => {
        set({ isLoading: true });
        try {
            const response = await fetchEvents();
            set({ events: response.data, isLoading: false });
        } catch (error) {
            console.error(error);
            set({ isLoading: false });
        }
    },

    fetchEvent: async (id): Promise<Event | null> => {
        try {
            const response = await fetchEvent(id);
            const event = response
            set((state) => ({ events: [...state.events, event] }));
            return event;
        } catch (error) {
            console.error(error);
            return null;
        }
    },

    createEvent: async (params) : Promise<Event | null>  => {
        try {
            const response = await createEvent(params);
            const event = response
            set((state) => ({ events: [...state.events, event] }));
            return event;

        } catch (error) {
            console.error(error);
            return null;
        }
    },

    deleteEvent: async (id) => {
        try {
            await deleteEvent(id);
            set((state) => ({
                events: state.events.filter((h) => h.id !== id),
                event: state.event?.id === id ? null : state.event,
            }));
        } catch (error) {
            console.error(error);
        }
    },

    updateEvent: async (id, params) => {
        try {
            const event =  await updateEvent(id, params);
            console.log(event)
            set((state ) => ({
                events: state.events.map((h) => (h.id === id ? event : h)),
                event: event,
            }));
        } catch (error) {
            console.error(error);
        }
    },

}));

export default useEventStore;
