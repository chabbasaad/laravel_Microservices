
export interface Event {
    image: string;
    id: number;
    title: string;
    description: string;
    date: string;
    location: string;
    max_tickets: number;
    available_tickets: number;
    price: string;
    creator_id: number;
    status: string;
    speakers: string;
    sponsors: string;
    created_at: string;
    updated_at: string;
}

export interface Speaker {
    id: number;
    bio: string;
    name: string;
    topic: string;
    company: string;
    position: string;
    photo_url: string;
    speaking_time: string;
}

export interface Sponsor {
    id: number;
    name: string;
    tier: string;
    type: string;
    logo_url: string;
    website_url: string;
}


export interface EventCreateRequest{
    "title": string,
    "description":string,
    "date": string,
    "location": string,
    "max_tickets": number,
    "price": number,
    "status": string,
    "speakers": string,
    "sponsors": string;
    image : string
}


export interface EventUpdateRequest{
    id: number;
    title: string;
    description: string;
    date: string;
    location: string;
    max_tickets: number;
    available_tickets: number;
    price: string;
    creator_id: number;
    status: string;
    speakers: Speaker[];
    sponsors: Sponsor[];
    created_at: string;
    updated_at: string;
}

export interface EventUpdateResponse{

}

export interface FetchEventsResponse {
    current_page: number;
    data: Event[];
    total: number;
}

export interface FetchEventsResponse {
    current_page: number;
    data: Event[];
    total: number;
}


export interface TicketResponse {
    id: number;
    ticket_number: string;
    event_id: number;
    user_id: number;
    price: string;
    status: string;
    purchase_date: string;
    used_at: string | null;
    cancelled_at: string | null;
    created_at: string;
    updated_at: string;
    payment: {
        id: number;
        ticket_id: number;
        transaction_id: string;
        amount: string;
        status: string;
        payment_method: string;
        error_message: string | null;
        paid_at: string;
        refunded_at: string | null;
        created_at: string;
        updated_at: string;
    }
}

export type TicketResponseArray = TicketResponse[];


