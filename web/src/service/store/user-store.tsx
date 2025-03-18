import { create } from "zustand";
import {User} from "../model/user.tsx";
import {fetchUser, fetchUsers} from "../services/user-service.tsx";

interface UserState {
    users: User[];
    userUp: User | null;
    loading: boolean;
    fetchUsers: () => Promise<void>;
    fetchUser: (params : UserLogin) => Promise<UserLoginResponseData | null>;
    createUser : (params: UserCreateRequestData) => Promise<void>;
    updateUser : (id: number, params : UserUpdateRequestData)=> Promise<UserUpdateResponseData | null>;
    deleteUser: (id: number) => Promise<void>;

}

const useUserStore = create<UserState>((set) => ({
    users: [],
    userUp: null ,
    loading : false,

    fetchUsers: async () => {
        try {
            const response = await fetchUsers();
            const users = response.data
            set({ users });
        } catch (error) {
            console.error(error);
        }
    },

    fetchUser: async (params): Promise<UserLoginResponseData | null> => {
        try {
            const response = await fetchUser(params);
            const userUp = response.user
            set({ userUp });
            return response;
        } catch (error) {
            console.error(error);
            return null;
        }
    },

    createUser: async (params) => {
        set({ loading: true });
        try {
            const response  = await createUser(params);
            const newUser = response.data;
            set((state) => ({ users: [...state.users, newUser] }));
        } catch (error) {
            console.error(error);
        } finally {
            set({ loading: false });
        }
    },

    updateUser: async (id, params) : Promise<UserUpdateResponseData | null> => {
        set({ loading: true });
        try {
            const response  = await updateUser(id, params);
            const updatedUser = response.data;
            set((state ) => ({
                users: state.users.map((h) => (h.id === id ? updatedUser : h)),
                userUp: updatedUser,
            }));
            return response;
        } catch (error) {
            console.error(error);
            return null;
        } finally {
            set({ loading: false });
        }
    },

    deleteUser: async (id) => {
        set({ loading: true });
        try {
            await deleteUser(id);
            set((state) => ({
                users: state.users.filter((h) => h.id !== id),
                user: state.userUp?.id === id ? null : state.userUp,
            }));
        } catch (error) {
            console.error("Erreur lors de la suppression de l'hôtel:", error);
        } finally {
            set({ loading: false });
        }
    },

}));

export default useUserStore;
