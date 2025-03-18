import { useState } from 'react';
import { CloudArrowUpIcon, LockClosedIcon, ServerIcon } from '@heroicons/react/20/solid';
import useTicketStore from "../../../service/store/ticket-store.tsx";
import {CreateTicketRequest} from "../../../service/model/ticket.tsx";

export default function OverviewEvent({ event }) {
    const { createTicket } = useTicketStore();
    const [quantity, setQuantity] = useState(1);  // Default ticket quantity
    const [cardNumber, setCardNumber] = useState('');
    const [expiry, setExpiry] = useState('');
    const [cvv, setCvv] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const userData = localStorage.getItem("user_data");
    const user = userData ? JSON.parse(userData) as { role?: string } : null;

    const handleQuantityChange = (e) => {
        setQuantity(e.target.value);
    };

    const handlePaymentSubmit = async (e) => {
        e.preventDefault();
        setIsSubmitting(true);

        const paymentData : CreateTicketRequest = {
            event_id: event.id,
            quantity,
            payment: {
                card_number: cardNumber,
                expiry,
                cvv
            }
        };

        createTicket(user?.id,paymentData)
        setIsSubmitting(false);
    };

    return (
        <div className="relative isolate overflow-hidden bg-white py-24 sm:py-32">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl lg:mx-0">
                    <p className="text-base font-semibold text-indigo-600">Upcoming Event</p>
                    <h1 className="mt-2 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        {event.title}
                    </h1>
                    <p className="mt-6 text-xl text-gray-700">{event.description}</p>
                    <p className="mt-4 text-lg text-gray-600">
                        📅 {new Date(event.date).toLocaleDateString()} - 📍 {event.location}
                    </p>
                    <p className="mt-2 text-lg text-gray-600">🎟 {event.available_tickets} tickets available</p>
                </div>

                {/* Speakers Section */}
                <div className="mt-16">
                    <h2 className="text-2xl font-bold text-gray-900">🎤 Speakers</h2>
                    <div className="mt-6 space-y-6">
                        <p className="text-lg font-semibold text-gray-900">{event.speakers}</p>
                    </div>
                </div>

                {/* Sponsors Section */}
                <div className="mt-16">
                    <h2 className="text-2xl font-bold text-gray-900">🤝 Sponsors</h2>
                    <div className="mt-6 flex flex-wrap gap-6">
                        <p className="mt-2 text-lg font-semibold text-gray-900">{event.sponsors}</p>
                    </div>
                </div>

                {/* Features Section */}
                <div className="mt-16">
                    <h2 className="text-2xl font-bold tracking-tight text-gray-900">Why Attend?</h2>
                    <ul className="mt-6 space-y-6 text-gray-700">
                        <li className="flex gap-3">
                            <CloudArrowUpIcon className="size-6 text-indigo-600" />
                            <span>Get exclusive insights from industry leaders.</span>
                        </li>
                        <li className="flex gap-3">
                            <LockClosedIcon className="size-6 text-indigo-600" />
                            <span>Network with professionals and experts.</span>
                        </li>
                        <li className="flex gap-3">
                            <ServerIcon className="size-6 text-indigo-600" />
                            <span>Access valuable resources and opportunities.</span>
                        </li>
                    </ul>
                </div>

                {/* Payment Section */}
                <div className="mt-16">
                    <h2 className="flex item-center text-2xl font-bold tracking-tight text-gray-900 w-500">🎟 Purchase Tickets</h2>
                    <form onSubmit={handlePaymentSubmit} className="mt-6 space-y-6 w-150">
                        <div className="flex gap-4">
                            <div className="">
                                <label htmlFor="quantity" className="block text-sm font-medium text-gray-700">Ticket Quantity</label>
                                <input
                                    type="number"
                                    id="quantity"
                                    value={quantity}
                                    onChange={handleQuantityChange}
                                    min="1"
                                    max={event.available_tickets}
                                    className="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                />
                            </div>
                        </div>

                        <div>
                            <label htmlFor="cardNumber" className="block text-sm font-medium text-gray-700">Card Number</label>
                            <input
                                type="text"
                                id="cardNumber"
                                value={cardNumber}
                                onChange={(e) => setCardNumber(e.target.value)}
                                required
                                className="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                            />
                        </div>

                        <div className="flex gap-4">
                            <div className="w-1/2">
                                <label htmlFor="expiry" className="block text-sm font-medium text-gray-700">Expiry Date</label>
                                <input
                                    type="text"
                                    id="expiry"
                                    value={expiry}
                                    onChange={(e) => setExpiry(e.target.value)}
                                    required
                                    className="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                    placeholder="MM/YY"
                                />
                            </div>
                            <div className="w-1/2">
                                <label htmlFor="cvv" className="block text-sm font-medium text-gray-700">CVV</label>
                                <input
                                    type="text"
                                    id="cvv"
                                    value={cvv}
                                    onChange={(e) => setCvv(e.target.value)}
                                    required
                                    className="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                    placeholder="CVV"
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            disabled={isSubmitting}
                            className="w-full mt-4 bg-gray-950 text-white py-2 px-4 rounded-md hover:bg-gray-800 disabled:bg-indigo-300"
                        >
                            {isSubmitting ? 'Processing...' : 'Purchase Tickets'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}
