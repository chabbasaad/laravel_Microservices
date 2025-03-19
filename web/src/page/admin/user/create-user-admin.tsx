import React, { useState } from "react";
import useUserStore from "../../../service/store/user-store.tsx";
import { RegisterUserRequest } from "../../../service/model/user.tsx";

export default function CreateUserAdmin({ setIsOpenCreate }: { setIsOpenCreate: (open: boolean) => void }) {
    const { createUser } = useUserStore();

    const defaultUserData: RegisterUserRequest = {
        name: "John Doe",
        email: "john.doe@example.com",
        password: "password123",
        password_confirmation: "password123",
    };

    const [userData, setUserData] = useState<any>(defaultUserData);
    const [isLoading, setIsLoading] = useState<boolean>(false); // État pour gérer le chargement

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setUserData((prev: any) => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async () => {
        console.log("User data:", userData);

        setIsLoading(true); // Démarrer le chargement
        try {
            await createUser(userData); // Attendre la création de l'utilisateur
            setIsOpenCreate(false); // Fermer le modal une fois l'utilisateur créé
        } catch (error) {
            console.error("Erreur lors de la création de l'utilisateur", error);
        } finally {
            setIsLoading(false); // Terminer le chargement
        }
    };

    // Validation pour s'assurer que tous les champs sont remplis et que les mots de passe correspondent
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
                    disabled={!isFormValid || isLoading}
                    className={`w-full p-2 rounded ${isFormValid && !isLoading ? "bg-gray-950 text-white hover:bg-gray-800" : "bg-gray-400 text-gray-700 cursor-not-allowed"}`}
                >
                    {isLoading ? (
                        <div className="flex justify-center items-center">
                            <span className="ml-2">Chargement...</span>
                        </div>
                    ) : (
                        "Ajouter l'utilisateur"
                    )}
                </button>
            </div>
        </div>
    );
}
