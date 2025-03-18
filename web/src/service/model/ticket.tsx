export interface Payment {
    transaction_id: string;
    status: string;
    payment_method: string;
    paid_at: string;
    refunded_at: string | null;
}

export interface Ticket {
    id: number;
    ticket_number: string;
    event_id: number;
    user_id: number;
    price: string;
    status: string;
    purchase_date: string;
    used_at: string | null;
    cancelled_at: string | null;
    payment: Payment;
}

export interface CreateTicketResponse {
    message: string;
    tickets: Ticket[];
}



export interface CreateTicketRequest {
    "event_id": number,
    "quantity": number,
    "payment": {
        "card_number": string,  // Valid test Visa card
        "expiry": string,
        "cvv": string
    }
}


