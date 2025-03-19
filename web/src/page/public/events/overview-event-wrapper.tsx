import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import useEventStore from "../../../service/store/event-store";
import OverviewEvent from "./overview-event";

// Define the structure of the event object with required properties
interface Event {
    id: number;
    title: string;
    description: string;
    date: string;
    location: string;
    available_tickets: number;
    speakers: string;
    sponsors: string;
    [key: string]: any;  // Allow additional properties if needed
}

export default function OverviewEventWrapper() {
    const { id } = useParams<{ id: string }>();  // id will be of type string
    const { events, fetchEvent } = useEventStore();
    const [event, setEvent] = useState<Event | undefined>(undefined);

    useEffect(() => {
        async function loadEvent() {
            const eventId = id ? parseInt(id, 10) : 0;  // Ensure the id is parsed to a number
            let foundEvent = events.find((e: Event) => e.id === eventId);

            if (!foundEvent) {
                const fetchedEvent = await fetchEvent(eventId);
                foundEvent = fetchedEvent ?? undefined;
            }

            setEvent(foundEvent);
        }

        loadEvent();
    }, [id, events, fetchEvent]);

    if (!event) return <p className="text-center mt-10 text-lg text-gray-500">Chargement...</p>;

    return <OverviewEvent event={event} />;
}
