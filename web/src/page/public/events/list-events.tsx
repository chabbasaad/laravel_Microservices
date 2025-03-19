import { useEffect } from "react";
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom";
import useEventStore from "../../../service/store/event-store.tsx";

export default function ListEvents() {
    const { t } = useTranslation();
    const { events, fetchEvents, loading } = useEventStore();
    const navigate = useNavigate();
    const userData = localStorage.getItem("user_data");

    const user = userData ? JSON.parse(userData) as { role?: string } : null;
    const userRole = user?.role || "";

    useEffect(() => {
        fetchEvents();
    }, []);

    const isUserLoggedIn = userRole !== "";

    return (
        <div className="bg-white py-24 sm:py-10">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <h2 className="text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">
                        {t("event")}
                    </h2>
                    <p className="mt-2 text-lg text-gray-600">{t("event-title")}</p>
                </div>

                {!userData ? (
                    <p className="text-center text-red-800 mt-10 text-lg text-gray-500">
                        {t("please_log_in_to_view")}
                    </p>
                ) : loading ? (
                    <p className="text-center mt-10 text-lg text-gray-500">{t("loading")}</p>
                ) : events.length === 0 ? (
                    <p className="text-center mt-10 text-lg text-gray-500">{t("no_events")}</p>
                ) : (
                    <div className="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                        {events.map((event) => (
                            <div
                                key={event.id}
                                className="relative isolate flex flex-col justify-end overflow-hidden rounded-2xl cursor-pointer"
                                onClick={() => navigate(`/event/${event.id}`)}
                            >
                                <img
                                    src={event.image || "default-image.jpg"} // Replace with a fallback image if no image is provided
                                    alt={event.title}
                                    className="object-cover w-full h-60 sm:h-80 lg:h-96 rounded-2xl"
                                />

                                <div className="absolute bottom-4 left-4 text-white bg-black bg-opacity-50 p-4 rounded-md">
                                    <div className="flex flex-wrap items-center gap-y-1 text-sm text-gray-300">
                                        <time dateTime={event.date} className="mr-8">
                                            {new Date(event.date).toLocaleDateString()}
                                        </time>
                                        <div className="-ml-4 flex items-center gap-x-4">
                                            <svg viewBox="0 0 2 2" className="-ml-0.5 size-0.5 flex-none fill-white/50">
                                                <circle r={1} cx={1} cy={1} />
                                            </svg>
                                            <div className="flex gap-x-2.5">
                                                {event.speakers || t("unknown_speaker")}
                                            </div>
                                        </div>
                                    </div>
                                    <h3 className="mt-3 text-lg font-semibold">{event.title}</h3>
                                    <p className="mt-1 text-sm">{event.description}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
