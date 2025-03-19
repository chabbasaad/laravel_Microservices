import { useEffect, useState } from "react";
import useUserStore from "../../../service/store/user-store";
import Spinner from "../../../components/sniper/sniper.tsx";

export default function UpdateUserAdmin({
                                            id,
                                            setIsOpenUpdate,
                                        }: {
    id: number;
    setIsOpenUpdate: (open: boolean) => void;
}) {
    const { updateUser, users } = useUserStore();
    const [userData, setUserData] = useState({
        name: "",
        email: "",
    });
    const [isLoading, setIsLoading] = useState(false); // Ajout du state pour le chargement

    useEffect(() => {
        const selectedUser = users.find((user) => user.id === id);
        if (selectedUser) {
            setUserData({
                name: selectedUser.name || "",
                email: selectedUser.email || "",
            });
        }
    }, [id, users]);

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
    ) => {
        setUserData({
            ...userData,
            [e.target.name]: e.target.value,
        });
    };

    const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setIsLoading(true); // Activation du chargement
        try {
            await updateUser(id, userData); // Appel de la mise à jour
            setIsOpenUpdate(false); // Fermer le modal après succès
        } catch (error) {
            console.error("Erreur lors de la mise à jour :", error);
        } finally {
            setIsLoading(false); // Désactivation du chargement
        }
    };

    return (
        <div className="max-w-2xl mx-auto bg-white rounded-lg p-5">
            <h2 className="text-2xl font-semibold text-gray-900 text-center">
                Modifier un Utilisateur
            </h2>
            <form onSubmit={handleSubmit} className="space-y-5">
                <div>
                    <label className="block text-gray-700 font-medium">
                        Nom de l'utilisateur
                    </label>
                    <input
                        type="text"
                        name="name"
                        value={userData.name}
                        onChange={handleChange}
                        required
                        className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 placeholder-gray-400 focus:outline-indigo-600"
                    />
                </div>

                <div>
                    <label className="block text-gray-700 font-medium">
                        Email
                    </label>
                    <input
                        type="email"
                        name="email"
                        value={userData.email}
                        onChange={handleChange}
                        required
                        className="m-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-gray-300 placeholder-gray-400 focus:outline-indigo-600"
                    />
                </div>

                <button
                    type="submit"
                    disabled={isLoading} // Désactivation du bouton pendant le chargement
                    className={`w-full py-2 rounded-lg font-semibold transition duration-200 ${
                        isLoading
                            ? "bg-gray-400 cursor-not-allowed"
                            : "bg-gray-950 text-white hover:bg-gray-800"
                    }`}
                >
                    {isLoading ? <Spinner /> : "Mettre à jour l'utilisateur"}
                </button>
            </form>
        </div>
    );
}
