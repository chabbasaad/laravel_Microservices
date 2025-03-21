import { useEffect, useState } from "react";
import { UserCircleIcon, TicketIcon } from "@heroicons/react/24/outline";
import useUserStore from "../../service/store/user-store.tsx";
import useTicketStore from "../../service/store/ticket-store.tsx";
import { useTranslation } from "react-i18next";
import Spinner from "../../components/sniper/sniper.tsx";

const secondaryNavigation = [
    { name: "General", href: "#", icon: UserCircleIcon },
    { name: "Mes Tickets", href: "#", icon: TicketIcon },
];

export default function Profile() {
    const { t } = useTranslation();

    const { user, updateUser, fetchUser } = useUserStore();
    const { tickets, fetchTickets, deleteTicket, loading: ticketLoading } = useTicketStore();
    const userData = localStorage.getItem("user_data");
    const userLocal = userData ? JSON.parse(userData) : "";

    const [editMode, setEditMode] = useState(false);
    const [currentNavigation, setCurrentNavigation] = useState("General");
    const [formData, setFormData] = useState({
        name: user?.name || "",
        email: user?.email || "",
        password: "",
    });
    const [isDeleting, setIsDeleting] = useState(false);
    const [isLoading, setIsLoading] = useState(true);  // Pour les tickets et les données générales
    const [isUserLoading, setIsUserLoading] = useState(true);  // Ajouter un état de chargement pour l'utilisateur

    const role = typeof userLocal === "object" && userLocal !== null ? userLocal?.role : undefined;
    const id = typeof userLocal === "object" && userLocal !== null ? userLocal?.id : undefined;

    useEffect(() => {
        const fetchData = async () => {
            setIsLoading(true);  // Début du chargement des tickets
            setIsUserLoading(true);  // Début du chargement des données utilisateur
            try {
                await fetchTickets(id);  // Récupérer les tickets
                await fetchUser(id, role);  // Récupérer les données de l'utilisateur
            } catch (error) {
                console.error("Erreur lors du chargement des données:", error);
            } finally {
                setIsLoading(false);  // Fin du chargement des tickets
                setIsUserLoading(false);  // Fin du chargement des données utilisateur
            }
        };

        fetchData();
    }, [id, role]);

    const handleNavigationClick = (name: string) => {
        setCurrentNavigation(name);
    };

    const handleDeleteTicket = async (ticketId: number, eventId: number, quantity: number) => {
        setIsDeleting(true);
        const params = {
            event_id: eventId,
            quantity: quantity,
            payment: {
                card_number: "4111111111111111",
                expiry: "12/25",
                cvv: "123"
            }
        };

        try {
            await deleteTicket(ticketId, params); // Suppression du ticket
        } catch (error) {
            console.error("Erreur lors de la suppression du ticket:", error);
        } finally {
            setIsDeleting(false); // Fin de la suppression
        }
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async () => {
        try {
            await updateUser(id | 1, formData);
            setEditMode(false);
        } catch (error) {
            console.error("Erreur lors de la mise à jour de l'utilisateur:", error);
        }
    };

    return (
        <div className="mx-auto max-w-7xl pt-16 lg:flex lg:gap-x-16 lg:px-8">
            {/* Navigation */}
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
                                    className={`group flex gap-x-3 rounded-md py-2 pr-3 pl-2 text-sm font-semibold ${currentNavigation === item.name ? "bg-gray-200 text-gray-950" : "text-gray-800 hover:bg-gray-950 hover:text-white"}`}
                                >
                                    <item.icon
                                        className={`size-6 shrink-0 ${currentNavigation === item.name ? "text-gray-950" : "text-gray-400 group-hover:text-white"}`}
                                    />
                                    {item.name}
                                </a>
                            </li>
                        ))}
                    </ul>
                </nav>
            </aside>

            {/* Main Content */}
            <main className="px-4 py-16 sm:px-6 lg:flex-auto lg:px-0 lg:py-20">
                <div className="mx-auto max-w-2xl space-y-16 sm:space-y-20 lg:mx-0 lg:max-w-none">
                    {currentNavigation === "General" && (
                        <div>
                            <h2 className="text-base font-semibold text-gray-900">Profile</h2>
                            <p className="mt-1 text-sm text-gray-500">{t("title-profile")}</p>

                            {isUserLoading ? (  // Afficher le spinner si les données utilisateur sont en cours de chargement
                                <div className="mt-4 text-center">
                                    <Spinner />
                                </div>
                            ) : user ? (
                                <div className="mt-6 space-y-4">
                                    {!editMode ? (
                                        <dl className="border-t border-gray-200 divide-y divide-gray-100 text-sm">
                                            <div className="flex justify-between py-3">
                                                <dt className="text-gray-500">{t("profile.name")}</dt>
                                                <dd className="text-gray-900">{user.name}</dd>
                                            </div>
                                            <div className="flex justify-between py-3">
                                                <dt className="text-gray-500">{t("profile.email")}</dt>
                                                <dd className="text-gray-900">{user.email}</dd>
                                            </div>
                                            <div className="flex justify-between py-3">
                                                <dt className="text-gray-500">{t("profile.date-create-profile")}</dt>
                                                <dd className="text-gray-900">{new Date(user.created_at).toLocaleDateString()}</dd>
                                            </div>
                                        </dl>
                                    ) : (
                                        <div className="space-y-4">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">{t("profile.name")}</label>
                                                <input
                                                    type="text"
                                                    name="name"
                                                    value={formData.name}
                                                    onChange={handleChange}
                                                    className="w-full p-2 mt-1 border rounded-md"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">{t("profile.email")}</label>
                                                <input
                                                    type="email"
                                                    name="email"
                                                    value={formData.email}
                                                    onChange={handleChange}
                                                    className="w-full p-2 mt-1 border rounded-md"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">{t("profile.password")}</label>
                                                <input
                                                    type="password"
                                                    name="password"
                                                    value={formData.password}
                                                    onChange={handleChange}
                                                    className="w-full p-2 mt-1 border rounded-md"
                                                />
                                            </div>
                                        </div>
                                    )}

                                    <div className="mt-6 flex gap-x-4">
                                        {editMode ? (
                                            <>
                                                <button
                                                    onClick={handleSubmit}
                                                    className="px-4 py-2 text-white bg-gray-950 rounded-md hover:bg-gray-800"
                                                >
                                                    {t("profile.save")}
                                                </button>
                                                <button
                                                    onClick={() => setEditMode(false)}
                                                    className="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                                                >
                                                    {t("profile.cancel")}
                                                </button>
                                            </>
                                        ) : (
                                            <button
                                                onClick={() => setEditMode(true)}
                                                className="px-4 py-2 text-white bg-gray-950 rounded-md hover:bg-white hover:text-gray-950"
                                            >
                                                {t("profile.edit")}
                                            </button>
                                        )}
                                    </div>
                                </div>
                            ) : (
                                <p className="text-sm text-gray-500"> {t("profile.no_user_info")}</p>
                            )}
                        </div>
                    )}

                    {currentNavigation === "Mes Tickets" && (
                        <div>
                            <h2 className="text-base font-semibold text-gray-900">{t("tickets.title")}</h2>
                            <p className="mt-1 text-sm text-gray-500">{t("tickets.description")}</p>

                            {isLoading ? (
                                <div className="mt-4 text-center">
                                    <Spinner />
                                </div>
                            ) : tickets.length === 0 ? (
                                <p className="mt-4 text-gray-900">{t("tickets.no_tickets")}</p>
                            ) : (
                                <div className="mt-4 space-y-4">
                                    {tickets.map((ticket: any) => (
                                        <div key={ticket.id} className="p-4 border rounded-lg bg-gray-100">
                                            <p className="font-semibold text-gray-900">{t("tickets.ticket_number")} #{ticket.ticket_number}</p>
                                            <p className=" text-gray-900">{t("tickets.status")}: {ticket.status}</p>
                                            <p className=" text-gray-900">{t("tickets.event_name")}: {ticket.event.title}</p>
                                            <p className=" text-gray-900">{t("tickets.price")}: {ticket.price}</p>
                                            <p className=" text-gray-900">{t("tickets.created_at")}: {ticket.created_at}</p>
                                            {ticket.status !== "cancelled" || ticket.status === "confirmed" && (
                                                <button
                                                    onClick={() => handleDeleteTicket(ticket.id, ticket.event.id, ticket.quantity)}
                                                    disabled={isDeleting}
                                                    className="mt-4 px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-800"
                                                >
                                                    {isDeleting ? <Spinner /> : t("tickets.delete")}
                                                </button>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </main>
        </div>
    );
}
