import { useState } from "react";
import useTicketStore from "../../../service/store/ticket-store.tsx";
import { CreateTicketRequest } from "../../../service/model/ticket.tsx";
import Spinner from "../../../components/sniper/sniper.tsx";

export default function CreateTicketAdmin({ setIsOpenCreate }: { setIsOpenCreate: (open: boolean) => void }) {
    const { createTicket } = useTicketStore();
    const userData = localStorage.getItem("user_data");
    const user = userData ? JSON.parse(userData) as { role?: string } : null;
    const [loading, setLoading] = useState(false); // Ajout du spinner d'état

    const defaultTicketData: CreateTicketRequest = {
        event_id: 1,
        quantity: 1,
        payment: {
            card_number: "4111111111111111",
            expiry: "12/25",
            cvv: "123"
        }
    };

    const [ticketData, setTicketData] = useState<CreateTicketRequest>(defaultTicketData);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setTicketData((prev) => ({
            ...prev,
            [name]: name.includes("payment.")
                ? { ...prev.payment, [name.split(".")[1]]: value }
                : value
        }));
    };

    const handleSubmit = async () => {
        setLoading(true); // Active le spinner
        console.log("Ticket data:", ticketData);
        if (user?.id) {
            await createTicket(user.id, ticketData);
            setIsOpenCreate(false);
        } else {
            console.error("Utilisateur non trouvé !");
        }
        setLoading(false); // Désactive le spinner après la requête
    };

    // Validation function to check if all fields are filled
    const isFormValid = ticketData.event_id > 0 && ticketData.quantity > 0
        && Object.values(ticketData.payment).every(value => value !== "");

    return (
        <div className="max-w-2xl mx-auto bg-white rounded-lg p-5">
            <h2 className="text-2xl font-bold text-gray-900">Créer un ticket</h2>
            <div className="space-y-4">
                <div>
                    <label className="block font-medium text-gray-700">ID de l'événement</label>
                    <input
                        type="number"
                        name="event_id"
                        value={ticketData.event_id}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <div>
                    <label className="block font-medium text-gray-700 mt-2">Quantité</label>
                    <input
                        type="number"
                        name="quantity"
                        value={ticketData.quantity}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <h3 className="text-lg font-semibold text-gray-900 mt-4">Informations de paiement</h3>

                <div>
                    <label className="block font-medium text-gray-700 mt-2">Numéro de carte</label>
                    <input
                        type="text"
                        name="payment.card_number"
                        value={ticketData.payment.card_number}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <div>
                    <label className="block font-medium text-gray-700 mt-2">Date d'expiration</label>
                    <input
                        type="text"
                        name="payment.expiry"
                        value={ticketData.payment.expiry}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <div>
                    <label className="block font-medium text-gray-700 mt-2">CVV</label>
                    <input
                        type="text"
                        name="payment.cvv"
                        value={ticketData.payment.cvv}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <button
                    onClick={handleSubmit}
                    disabled={!isFormValid || loading}
                    className={`w-full p-2 rounded font-semibold transition duration-200 ${
                        isFormValid && !loading ? "bg-gray-950 text-white hover:bg-gray-800" : "bg-gray-400 text-gray-700 cursor-not-allowed"
                    }`}
                >
                    {loading ? (
                        <div className="flex items-center justify-center">
                            Chargement...
                        </div>
                    ) : "Acheter le ticket"}
                </button>
            </div>
        </div>
    );
}
