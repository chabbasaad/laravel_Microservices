import { useTranslation } from "react-i18next";
import { useEffect, useState } from "react";
import useTicketStore from "../../../service/store/ticket-store";
import { Dialog } from "../../../components/kit-ui/dialog.tsx";
import CreateTicketAdmin from "./create-ticket-admin.tsx";
import Spinner from "../../../components/sniper/sniper.tsx";

export default function ListTicketsAdmin() {
    const { t } = useTranslation();
    const { tickets, fetchTickets, deleteTicket, isLoading } = useTicketStore(); // Ajout de `isLoading`
    const [isOpenCreate, setIsOpenCreate] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);
    const [deletingTicketId, setDeletingTicketId] = useState<number | null>(null);

    const userData = localStorage.getItem("user_data");
    const user = userData ? JSON.parse(userData) as { id?: number, role?: string } : null;

    useEffect(() => {
        const loadTickets = async () => {
            try {
                await fetchTickets(user?.id);
            } catch (error) {
                console.error("Erreur lors de la récupération des tickets", error);
            }
        };

        if (user?.id) {
            loadTickets();
        }
    }, [user?.id]);

    const handleDeleteTicket = async (ticketId: number, eventId: number, quantity: number) => {
        if (!window.confirm(t("confirm_delete"))) return;

        setIsDeleting(true);
        setDeletingTicketId(ticketId);
        try {
            const params = {
                event_id: eventId,
                quantity: quantity,
                payment: {
                    card_number: "4111111111111111",
                    expiry: "12/25",
                    cvv: "123"
                }
            };

            await deleteTicket(ticketId, params);
        } catch (error) {
            console.error("Erreur lors de la suppression du ticket", error);
        } finally {
            setIsDeleting(false);
            setDeletingTicketId(null);
        }
    };

    return (
        <div className="px-4 sm:px-6 lg:px-8 mt-20">
            <div className="sm:flex sm:items-center">
                <div className="sm:flex-auto">
                    <h1 className="text-base font-semibold text-gray-900">{t("tickets.title")}</h1>
                </div>
                <button
                    type="button"
                    onClick={() => setIsOpenCreate(true)}
                    className="block rounded-md bg-gray-950 px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-gray-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    {t("tickets.add")}
                </button>
            </div>

            <div className="mt-8 flow-root">
                <div className="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div className="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table className="min-w-full divide-y divide-gray-300">
                            <thead>
                            <tr>
                                <th className="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                    {t("tickets.ticket_number")}
                                </th>
                                <th className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {t("tickets.event_name")}
                                </th>
                                <th className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {t("tickets.price")}
                                </th>
                                <th className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {t("tickets.status")}
                                </th>
                                <th className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {t("tickets.purchase_date")}
                                </th>
                                <th className="relative py-3.5 pr-4 pl-3 sm:pr-3">
                                    <span className="sr-only">{t("delete")}</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody className="bg-white">
                            {isLoading ? (
                                <tr>
                                    <td colSpan={6} className="py-4 text-center text-sm text-gray-500">
                                        <Spinner />
                                    </td>
                                </tr>
                            ) : tickets.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="py-4 text-center text-sm text-gray-500">
                                        {t("tickets.no_tickets")}
                                    </td>
                                </tr>
                            ) : (
                                tickets.map((ticket) => (
                                    <tr key={ticket.id} className="even:bg-gray-50">
                                        <td className="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                            {ticket.ticket_number}
                                        </td>
                                        <td className="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                            {ticket.event?.title || t("unknown_event")}
                                        </td>
                                        <td className="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                            ${ticket.price}
                                        </td>
                                        <td className="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                            {ticket.status}
                                        </td>
                                        <td className="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                            {new Date(ticket.purchase_date).toLocaleDateString()}
                                        </td>
                                        <td className="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                            {ticket.status !== "cancelled" && (
                                                <button
                                                    onClick={() => handleDeleteTicket(ticket.id, ticket.event.id, ticket.quantity)}
                                                    className="mt-4 px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-800"
                                                    disabled={isDeleting || deletingTicketId === ticket.id}
                                                >
                                                    {isDeleting && deletingTicketId === ticket.id ? (
                                                        <svg className="animate-spin h-5 w-5 mr-2 text-white" viewBox="0 0 24 24">
                                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3-3-3h4z"></path>
                                                        </svg>
                                                    ) : null}
                                                    {t("tickets.delete")}
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                ))
                            )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <Dialog className="mt-20" open={isOpenCreate} onClose={() => setIsOpenCreate(false)}>
                <CreateTicketAdmin setIsOpenCreate={setIsOpenCreate} />
            </Dialog>
        </div>
    );
}
