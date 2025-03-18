import { Routes, Route } from "react-router-dom";
import Home from "../page/public/home/home.tsx";
import AuthGuard from "./auth-guard.tsx";
import Profile from "../page/profile/profile.tsx";
import OverviewEventWrapper from "../page/public/events/overview-event-wrapper.tsx";
import AuthGuardAdmin from "./auth-guard-admin.tsx";
import ListEventsAdmin from "../page/admin/event/list-events-admin.tsx";


const AppRoutes = () => {
    return (
        <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/event/:id" element={<OverviewEventWrapper />} />


            <Route
                path="/profile"
                element={
                    <AuthGuard>
                        <Profile />
                    </AuthGuard>
                }
            />

            <Route
                path="/admin/gestion-event"
                element={
                    <AuthGuardAdmin>
                        <ListEventsAdmin />
                    </AuthGuardAdmin>
                }
            />
    {/*
            <Route
                path="/admin/gestion-users"
                element={
                    <AuthGuardAdmin>
                        <UsersList />
                    </AuthGuardAdmin>
                }
            />
            <Route
                path="/admin/gestion-booking"
                element={
                    <AuthGuardAdmin>
                        <BookingList />
                    </AuthGuardAdmin>
                }
            />*/}
        </Routes>
    );
};

export default AppRoutes;
