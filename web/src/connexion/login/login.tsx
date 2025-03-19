import { useState } from "react";
import { login } from "../../service/services/user-service.tsx";

export default function Login({ closeModal }: { closeModal: () => void }) {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [isLoading, setIsLoading] = useState(false); // Ajouter un état pour le chargement

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        const user = {
            email,
            password
        };

        setIsLoading(true);
        try {
            const response = await login(user);
            if (response) {
                localStorage.setItem("user_token", response.token);
                localStorage.setItem("user_data", JSON.stringify(response.user));
                closeModal();
                window.location.reload();
            }
        } catch (error) {
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="flex min-h-full flex-1 flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div className="sm:mx-auto sm:w-full sm:max-w-md">
                <h2 className="mt-6 text-center text-2xl font-bold text-gray-900">
                    Sign in to your account
                </h2>
            </div>

            <div className="sm:mx-auto sm:w-full sm:max-w-md">
                <div className="bg-white px-6 py-12 sm:rounded-lg sm:px-12">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <label htmlFor="email" className="block text-sm font-medium text-gray-900">
                                Email address
                            </label>
                            <div className="mt-2">
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                    className="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                />
                            </div>
                        </div>

                        <div>
                            <label htmlFor="password" className="block text-sm font-medium text-gray-900">
                                Password
                            </label>
                            <div className="mt-2">
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                    className="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                />
                            </div>
                        </div>

                        <div>
                            <button
                                type="submit"
                                disabled={isLoading} // Désactiver le bouton pendant le chargement
                                className="w-full rounded-md bg-gray-950 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                {isLoading ? (
                                    <div className="flex justify-center items-center">
                                        <span className="ml-2">Chargement...</span>
                                    </div>
                                ) : (
                                    "Connexion"
                                )}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
