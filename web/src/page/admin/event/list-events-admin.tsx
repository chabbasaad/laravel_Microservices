import { useTranslation } from "react-i18next";
import useEventStore from "../../../service/store/event-store.tsx";
import { useEffect, useState } from "react";
import {Dialog} from "../../../components/kit-ui/dialog";
import CreateEventsAdmin from "./create-events-admin.tsx";
import UpdateEventAdmin from "./update-event-admin.tsx";


export default function ListEventsAdmin() {
    const { t } = useTranslation();
    const { events, fetchEvents, deleteEvent } = useEventStore();
    const [isOpenCreate, setIsOpenCreate] = useState(false);
    const [isOpenUpdate, setIsOpenUpdate] = useState(false);
    const [selectedEventId, setSelectedEventId] = useState<number | null>(null);

    const handleUpdateClick = (id: number) => {
        setSelectedEventId(id);
        setIsOpenUpdate(true);
    }

    const [isDeleting, setIsDeleting] = useState(false);

    useEffect(() => {
        fetchEvents();
    }, [fetchEvents]);

    const handleDelete = (eventId: number) => {
        if (window.confirm(t("confirm_delete"))) {
            setIsDeleting(true);
            deleteEvent(eventId);
            setIsDeleting(false);
        }
    };
    return (
        <div className="px-4 sm:px-6 lg:px-8 mt-20">
            <div className="sm:flex sm:items-center">
                <div className="sm:flex-auto">
                    <h1 className="text-base font-semibold text-gray-900">{t("event")}</h1>
                    <p className="mt-2 text-sm text-gray-700">
                        {t("event-title")}
                    </p>
                </div>
                <div className="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                    <button
                        type="button"
                        onClick={() => setIsOpenCreate(true)}
                        className="block rounded-md bg-gray-950 px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-gray-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    >
                        {t("add-event")}
                    </button>
                </div>
            </div>
            <div className="mt-8 flow-root">
                <div className="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div className="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table className="min-w-full divide-y divide-gray-300">
                            <thead>
                            <tr>
                                <th scope="col" className="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                    {t("name")}
                                </th>
                                <th scope="col" className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {t("title")}
                                </th>
                                <th scope="col" className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {t("email")}
                                </th>
                                <th scope="col" className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {t("role")}
                                </th>
                                <th scope="col" className="relative py-3.5 pr-4 pl-3 sm:pr-3">
                                    <span className="sr-only">{t("edit")}</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody className="bg-white">
                            {events.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="py-4 text-center text-sm text-gray-500">{t("no_events")}</td>
                                </tr>
                            ) : (
                                events.map((event) => (
                                    <tr key={event.id} className="even:bg-gray-50">
                                        <td className="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                            {event.title}
                                        </td>
                                        <td className="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{event.description}</td>
                                        <td className="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{new Date(event.date).toLocaleDateString()}</td>
                                        <td className="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{event.status}</td>
                                        <td className="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                            <button
                                                onClick={() => handleDelete(event.id)}
                                                className="text-red-600 hover:text-red-900"
                                                disabled={isDeleting}
                                            >
                                                {isDeleting ? t("loading") : t("delete")}
                                            </button>
                                            <button
                                                onClick={() => handleUpdateClick(event.id)}
                                                className="text-indigo-800 ml-5 hover:text-indigo-400"
                                                disabled={isDeleting}
                                            >
                                                {isDeleting ? t("loading") : t("edit")}
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <Dialog className="mt-20"  open={isOpenCreate} onClose={() => setIsOpenCreate(false)}>
                <CreateEventsAdmin setIsOpenCreate={setIsOpenCreate}  />
            </Dialog>
            <Dialog  open={isOpenUpdate} onClose={() => setIsOpenUpdate(false)}>
                {selectedEventId && <UpdateEventAdmin id={selectedEventId} setIsOpenUpdate={setIsOpenUpdate}  />}
            </Dialog>
        </div>
    );
}
