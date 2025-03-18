import { useState } from "react";
import useEventStore from "../../../service/store/event-store.tsx";
import { EventCreateRequest } from "../../../service/model/event.tsx";

export default function CreateEventsAdmin({ setIsOpenCreate }: { setIsOpenCreate: (open: boolean) => void }) {
    const { createEvent } = useEventStore();

    const defaultEventData: EventCreateRequest = {
        title: "Tech Conference 2025",
        description: "Annual technology conference",
        date: "2025-06-15T09:00:00",
        location: "Convention Center",
        max_tickets: 500,
        price: 99.99,
        status: "draft",
        speakers: "test",
        sponsors: "test",
        image: "test"
    };

    const [eventData, setEventData] = useState<EventCreateRequest>(defaultEventData);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setEventData((prev) => ({ ...prev, [name]: value }));
    };

    const handleSubmit = () => {
        console.log("Event data:", eventData);
        createEvent(eventData);
        setIsOpenCreate(false);
    };

    // Validation function to check if all fields are filled
    const isFormValid = Object.values(eventData).every((value) => value !== "" && value !== null);

    return (
        <div className="max-w-2xl mx-auto bg-white rounded-lg ">
            <h2 className="text-2xl font-bold  text-gray-900">Ajouter un événement</h2>
            <div className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block font-medium text-gray-700">Titre</label>
                        <input
                            type="text"
                            name="title"
                            value={eventData.title}
                            onChange={handleChange}
                            className="w-full p-2 border rounded"
                        />

                        <label className="block font-medium text-gray-700 mt-2">Description</label>
                        <textarea
                            name="description"
                            value={eventData.description}
                            onChange={handleChange}
                            className="w-full p-2 border rounded"
                        />

                        <label className="block font-medium text-gray-700 mt-2">Date</label>
                        <input
                            type="datetime-local"
                            name="date"
                            value={eventData.date}
                            onChange={handleChange}
                            className="w-full p-2 border rounded"
                        />

                        <label className="block font-medium text-gray-700 mt-2">Localisation</label>
                        <input
                            type="text"
                            name="location"
                            value={eventData.location}
                            onChange={handleChange}
                            className="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <label className="block font-medium text-gray-700">Nombre de billets</label>
                        <input
                            type="number"
                            name="max_tickets"
                            value={eventData.max_tickets}
                            onChange={handleChange}
                            className="w-full p-2 border rounded"
                        />

                        <label className="block font-medium text-gray-700 mt-2">Prix (€)</label>
                        <input
                            type="number"
                            name="price"
                            value={eventData.price}
                            onChange={handleChange}
                            className="w-full p-2 border rounded"
                        />
                    </div>
                </div>

                <div>
                    <label className="block font-medium text-gray-700 mt-2">Intervenants</label>
                    <input
                        type="text"
                        name="speakers"
                        value={eventData.speakers}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <div>
                    <label className="block font-medium text-gray-700 mt-2">Sponsors</label>
                    <input
                        type="text"
                        name="sponsors"
                        value={eventData.sponsors}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <div>
                    <label className="block font-medium text-gray-700 mt-2">Image</label>
                    <input
                        type="text"
                        name="image"
                        value={eventData.image}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <button
                    onClick={handleSubmit}
                    disabled={!isFormValid}  // Disable button if the form is not valid
                    className={`w-full p-2 rounded ${isFormValid ? "bg-gray-950 text-white hover:bg-gray-800" : "bg-gray-400 text-gray-700 cursor-not-allowed"}`}
                >
                    Ajouter l'événement
                </button>
            </div>
        </div>
    );
}
