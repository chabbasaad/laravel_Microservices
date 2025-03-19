import { useEffect, useState } from "react";
import useEventStore from "../../../service/store/event-store.tsx";
import Spinner from "../../../components/sniper/sniper.tsx";

export default function UpdateEventAdmin({ id, setIsOpenUpdate }: { id: number, setIsOpenUpdate: (open: boolean) => void }) {
    const { updateEvent, events } = useEventStore();
    const [eventData, setEventData] = useState({
        title: "",
        description: "",
        status: "",
        date: "2025-06-15",
        location: "Convention Center",
        max_tickets: 500,
        price: 99.99,
        speakers: "test",
        sponsors: "test",
        image: "test"
    });

    const [isLoading, setIsLoading] = useState(false); // État pour le chargement

    useEffect(() => {
        const selectedEvent = events.find(event => event.id === id);
        if (selectedEvent) {
            setEventData({
                title: selectedEvent.title || "",
                description: selectedEvent.description || "",
                date: selectedEvent.date ? new Date(selectedEvent.date).toISOString().split("T")[0] : "",
                status: selectedEvent.status || "",
                location: selectedEvent.location || "",
                max_tickets: selectedEvent.max_tickets || 500,
                price: selectedEvent.price || 99.99,
                speakers: selectedEvent.speakers || "",
                sponsors: selectedEvent.sponsors || "",
                image: selectedEvent.image || ""
            });
        }
    }, [id, events]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        setEventData({
            ...eventData,
            [e.target.name]: e.target.value,
        });
    };

    const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setIsLoading(true); // Activer le chargement
        try {
            await updateEvent(id, eventData); // Mettre à jour l'événement
            setIsOpenUpdate(false); // Fermer le modal après succès
        } catch (error) {
            console.error("Erreur lors de la mise à jour :", error);
        } finally {
            setIsLoading(false); // Désactiver le chargement
        }
    };

    return (
        <div className="max-w-2xl mx-auto bg-white rounded-lg p-5">
            <h2 className="text-2xl font-semibold text-gray-900 text-center">
                Modifier un Événement
            </h2>
            <form onSubmit={handleSubmit} className="space-y-5">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label className="block text-gray-700 font-medium">Nom de l'Événement</label>
                        <input
                            type="text"
                            name="title"
                            value={eventData.title}
                            onChange={handleChange}
                            required
                            className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 focus:outline-indigo-600"
                        />
                    </div>

                    <div>
                        <label className="block text-gray-700 font-medium">Date</label>
                        <input
                            type="date"
                            name="date"
                            value={eventData.date}
                            onChange={handleChange}
                            required
                            className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 focus:outline-indigo-600"
                        />
                    </div>

                    <div className="col-span-2">
                        <label className="block text-gray-700 font-medium">Description</label>
                        <textarea
                            name="description"
                            value={eventData.description}
                            onChange={handleChange}
                            rows={4}
                            required
                            className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 focus:outline-indigo-600"
                        />
                    </div>

                    <div>
                        <label className="block text-gray-700 font-medium">Statut</label>
                        <input
                            type="text"
                            name="status"
                            value={eventData.status}
                            onChange={handleChange}
                            required
                            className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 focus:outline-indigo-600"
                        />
                    </div>

                    <div>
                        <label className="block text-gray-700 font-medium">Localisation</label>
                        <input
                            type="text"
                            name="location"
                            value={eventData.location}
                            onChange={handleChange}
                            required
                            className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 focus:outline-indigo-600"
                        />
                    </div>

                    <div>
                        <label className="block text-gray-700 font-medium">Nombre de billets</label>
                        <input
                            type="number"
                            name="max_tickets"
                            value={eventData.max_tickets}
                            onChange={handleChange}
                            required
                            className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 focus:outline-indigo-600"
                        />
                    </div>

                    <div>
                        <label className="block text-gray-700 font-medium">Prix (€)</label>
                        <input
                            type="number"
                            name="price"
                            value={eventData.price}
                            onChange={handleChange}
                            required
                            className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 focus:outline-indigo-600"
                        />
                    </div>

                    <div>
                        <label className="block text-gray-700 font-medium">Intervenants</label>
                        <input
                            type="text"
                            name="speakers"
                            value={eventData.speakers}
                            onChange={handleChange}
                            required
                            className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 focus:outline-indigo-600"
                        />
                    </div>

                    <div>
                        <label className="block text-gray-700 font-medium">Sponsors</label>
                        <input
                            type="text"
                            name="sponsors"
                            value={eventData.sponsors}
                            onChange={handleChange}
                            required
                            className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 focus:outline-indigo-600"
                        />
                    </div>

                    <div>
                        <label className="block text-gray-700 font-medium">Image URL</label>
                        <input
                            type="text"
                            name="image"
                            value={eventData.image}
                            onChange={handleChange}
                            required
                            className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 focus:outline-indigo-600"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    disabled={isLoading}
                    className={`w-full py-2 rounded-lg font-semibold transition duration-200 ${
                        isLoading
                            ? "bg-gray-400 cursor-not-allowed"
                            : "bg-gray-950 text-white hover:bg-gray-800"
                    }`}
                >
                    {isLoading ? <Spinner /> : "Mettre à jour l'événement"}
                </button>
            </form>
        </div>
    );
}
