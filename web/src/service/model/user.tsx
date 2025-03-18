export interface User {
    id: number;
    name : string;
    email :string;
    created_at: string;
    updated_at :string;
    phone_number : string;
    address : string;
    city : string;
    country: string;
    role : string;
}

export interface RegisterUserRequest {
    "name": string,
    "email": string,
    "password": string,
    "password_confirmation": string
}
export interface RegisterUserResponse  {
    status: string
    message : string;
    data :  {
        user : User
        token : string
        token_type : string
    }
}


export interface LoginUserRequest {
    email : string;
    password: string;
}


export interface LoginUserResponse {
    message: string;
    user :{
        id: number
        role :string
    }
    token: string;
    token_type : string
}

export interface UpdateUserRequest {

}

export interface UpdateUserResponse {

}

export interface FetchUsersResponse {

}

