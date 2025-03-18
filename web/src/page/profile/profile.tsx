import { useState, useEffect } from "react";
import { UserCircleIcon } from "@heroicons/react/24/outline";
import { TicketIcon } from "@heroicons/react/24/outline";
import useUserStore from "../../service/store/user-store.tsx";
import useTicketStore from "../../service/store/ticket-store.tsx";
import { Ticket } from "../../service/model/ticket.tsx";

const secondaryNavigation = [
    { name: "General", href: "#", icon: UserCircleIcon },
    { name: "Mes Tickets", href: "#", icon: TicketIcon },
];

export default function Profile() {
    const { updateUser } = useUserStore();
    const { fetchTickets, ticketUser, deleteTicket, loadingTickets } = useTicketStore(); // Assuming `loadingTickets` is the loading state in your store
    const [user, setUser] = useState<{ id: number; name: string; pseudo: string; email: string } | null>(null);
    const [editMode, setEditMode] = useState(false);
    const [currentNavigation, setCurrentNavigation] = useState("General");

    useEffect(() => {
        const userData = localStorage.getItem("user_data");
        if (userData) {
            const parsedUser = JSON.parse(userData);
            setUser(parsedUser);
            fetchTickets(parsedUser.id); // Fetch tickets when user is loaded
        }
    }, []);

    const handleNavigationClick = (name: string) => {
        setCurrentNavigation(name);
    };

    const handleDeleteTicket = (ticketId: number) => {
        deleteTicket(ticketId);
    };

    return (
        <>
            <div className="mx-auto max-w-7xl pt-16 lg:flex lg:gap-x-16 lg:px-8">
                <aside className="flex overflow-x-auto border-b border-gray-900/5 py-4 lg:block lg:w-64 lg:flex-none lg:border-0 lg:py-20">
                    <nav className="flex-none px-4 sm:px-6 lg:px-0">
                        <ul role="list" className="flex gap-x-3 gap-y-1 whitespace-nowrap lg:flex-col">
                            {secondaryNavigation.map((item) => (
                                <li key={item.name}>
                                    <a
                                        href={item.href}
                                        onClick={(e) => {
                                            e.preventDefault();
                                            handleNavigationClick(item.name);
                                        }}
                                        className={`group flex gap-x-3 rounded-md py-2 pr-3 pl-2 text-sm/6 font-semibold ${
                                            currentNavigation === item.name
                                                ? "bg-gray-200 text-gray-950"
                                                : "text-gray-800 hover:bg-gray-950 hover:text-white"
                                        }`}
                                    >
                                        <item.icon
                                            className={`size-6 shrink-0 ${
                                                currentNavigation === item.name
                                                    ? "text-gray-950"
                                                    : "text-gray-400 group-hover:text-white"
                                            }`}
                                        />
                                        {item.name}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </nav>
                </aside>

                <main className="px-4 py-16 sm:px-6 lg:flex-auto lg:px-0 lg:py-20">
                    <div className="mx-auto max-w-2xl space-y-16 sm:space-y-20 lg:mx-0 lg:max-w-none">
                        {currentNavigation === "General" && (
                            <div>
                                <h2 className="text-base/7 font-semibold text-gray-900">Profile</h2>
                                <p className="mt-1 text-sm/6 text-gray-500">Modifiez vos informations personnelles.</p>
                                {user ? (
                                    <dl className="mt-6 divide-y divide-gray-100 border-t border-gray-200 text-sm/6">
                                        {/* Affichage des informations utilisateur */}
                                    </dl>
                                ) : (
                                    <p className="text-sm/6 text-gray-500">Aucune information utilisateur trouvée.</p>
                                )}
                                <div className="mt-6">
                                    {editMode ? (
                                        <div className="flex gap-x-4">
                                            <button
                                                onClick={() => setEditMode(false)}
                                                className="px-4 py-2 text-white bg-gray-950 rounded-md hover:bg-gray-800"
                                            >
                                                Sauvegarder
                                            </button>
                                            <button
                                                onClick={() => setEditMode(false)}
                                                className="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                                            >
                                                Annuler
                                            </button>
                                        </div>
                                    ) : (
                                        <button
                                            onClick={() => setEditMode(true)}
                                            className="px-4 py-2 text-white bg-gray-950 rounded-md hover:bg-white hover:text-gray-950"
                                        >
                                            Modifier
                                        </button>
                                    )}
                                </div>
                            </div>
                        )}

                        {currentNavigation === "Mes Tickets" && (
                            <div>
                                <h2 className="text-base/7 font-semibold text-gray-900">Mes Tickets</h2>
                                <p className="mt-1 text-sm/6 text-gray-500">
                                    Retrouvez ici tous vos tickets pour vos événements.
                                </p>

                                {/* Show loading indicator if tickets are being fetched */}
                                {loadingTickets ? (
                                    <div className="mt-4 text-center">
                                        <p className="text-lg text-gray-500">Chargement des tickets...</p>
                                    </div>
                                ) : ticketUser.length === 0 ? (
                                    <p className="mt-4 text-gray-900">Aucun ticket trouvé.</p>
                                ) : (
                                    <div className="mt-4 space-y-4">
                                        {ticketUser.map((ticket: Ticket) => (
                                            <div key={ticket.id} className="p-4 border rounded-lg bg-gray-100">
                                                <p className="font-semibold text-gray-900">
                                                    Ticket #{ticket.ticket_number}
                                                </p>
                                                <p className="text-gray-700">
                                                    Prix : {ticket.price}€ | Statut : {ticket.status}
                                                </p>
                                                <p className="text-gray-600">
                                                    Événement :{" "}
                                                    {ticket.event.error
                                                        ? "Détails non disponibles"
                                                        : `${ticket.event.name} - ${ticket.event.date}`}
                                                </p>
                                                <p className="text-gray-500 text-sm">
                                                    Achat le : {new Date(ticket.purchase_date).toLocaleDateString()}
                                                </p>
                                                {/* Delete button */}
                                                <button
                                                    onClick={() => handleDeleteTicket(ticket.id)}
                                                    className="mt-4 px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-800"
                                                >
                                                    Supprimer
                                                </button>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </main>
            </div>
        </>
    );
}
