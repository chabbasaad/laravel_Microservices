import { useEffect, useState } from "react";
import useUserStore from "../../../service/store/user-store";

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

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        updateUser(id, userData);
        setIsOpenUpdate(false);
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
                    className="w-full bg-gray-950 text-white py-2 rounded-lg font-semibold transition duration-200 hover:bg-gray-800"
                >
                    Mettre à jour l'utilisateur
                </button>
            </form>
        </div>
    );
}
