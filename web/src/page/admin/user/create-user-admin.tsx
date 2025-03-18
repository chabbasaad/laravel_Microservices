import { useState } from "react";
import useUserStore from "../../../service/store/user-store.tsx";
import {RegisterUserRequest} from "../../../service/model/user.tsx";



export default function CreateUserAdmin({ setIsOpenCreate }: { setIsOpenCreate: (open: boolean) => void }) {
    const { createUser } = useUserStore();

    const defaultUserData: RegisterUserRequest = {
        name: "John Doe",
        email: "john.doe@example.com",
        password: "password123",
        password_confirmation: "password123",
    };

    const [userData, setUserData] = useState<any>(defaultUserData);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setUserData((prev  : any) => ({ ...prev, [name]: value }));
    };

    const handleSubmit = () => {
        console.log("User data:", userData);
        createUser(userData);
        setIsOpenCreate(false);
    };

    // Validation function to check if all fields are filled and passwords match
    const isFormValid = Object.values(userData).every((value) => value !== "") && userData.password === userData.password_confirmation;

    return (
        <div className="max-w-2xl mx-auto bg-white rounded-lg p-5">
            <h2 className="text-2xl font-bold text-gray-900">Ajouter un utilisateur</h2>
            <div className="space-y-4">
                <div>
                    <label className="block font-medium text-gray-700">Nom</label>
                    <input
                        type="text"
                        name="name"
                        value={userData.name}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <div>
                    <label className="block font-medium text-gray-700 mt-2">Email</label>
                    <input
                        type="email"
                        name="email"
                        value={userData.email}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <div>
                    <label className="block font-medium text-gray-700 mt-2">Mot de passe</label>
                    <input
                        type="password"
                        name="password"
                        value={userData.password}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <div>
                    <label className="block font-medium text-gray-700 mt-2">Confirmation du mot de passe</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        value={userData.password_confirmation}
                        onChange={handleChange}
                        className="w-full p-2 border rounded"
                    />
                </div>

                <button
                    onClick={handleSubmit}
                    disabled={!isFormValid}  // Disable button if the form is not valid
                    className={`w-full p-2 rounded ${isFormValid ? "bg-gray-950 text-white hover:bg-gray-800" : "bg-gray-400 text-gray-700 cursor-not-allowed"}`}
                >
                    Ajouter l'utilisateur
                </button>
            </div>
        </div>
    );
}
