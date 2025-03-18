import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import useEventStore from "../../../service/store/event-store.tsx";
import OverviewEvent from "./overview-event.tsx";

export default function OverviewEventWrapper() {
    const { id } = useParams();
    const { events, fetchEvent } = useEventStore();
    const [event, setEvent] = useState(null);

    useEffect(() => {
        async function loadEvent() {
            let foundEvent = events.find(e => e.id === parseInt(id));
            if (!foundEvent) {
                foundEvent = await fetchEvent(id);
            }
            setEvent(foundEvent);
        }
        loadEvent();
    }, [id, events, fetchEvent]);

    if (!event) return <p className="text-center mt-10 text-lg text-gray-500">Chargement...</p>;

    return <OverviewEvent event={event} />;
}
