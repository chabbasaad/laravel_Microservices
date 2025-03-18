import { Disclosure, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import { Bars3Icon, XMarkIcon } from '@heroicons/react/24/outline';
import { Link } from "react-router-dom";
import Login from "../../connexion/login/login";
import Register from "../../connexion/register/register";
import { Dialog } from '../../components/kit-ui/dialog';
import { useTranslation } from "react-i18next";
import Languages from "../languages/languages.tsx";
import { useState} from "react";

type NavigationItem = {
  name: string;
  href: string;
  current: boolean;
};

export default function Header() {
  const { t } = useTranslation();
  const userData = localStorage.getItem("user_data");
  const user = userData ? JSON.parse(userData) as { role?: string } : null;
  const userRole = user?.role || "";

  const [isOpenLogin, setIsOpenLogin] = useState(false);
  const [isOpenRegister, setIsOpenRegister] = useState(false);

  const navigation: NavigationItem[] = [
    { name: t('nav_home'), href: '/', current: false },
  ];

  const navigationAdmin: NavigationItem[] = [
    { name: t('nav_home'), href: '/', current: false },
    { name: t('nav_users'), href: 'admin/gestion-users', current: false },
    { name: t('nav_events'), href: 'admin/gestion-event', current: false },
    { name: t('nav_tickets'), href: 'admin/gestion-tickets', current: false },
  ];

  const navigationCreator: NavigationItem[] = [
    { name: t('nav_home'), href: '/', current: false },
    { name: t('nav_events'), href: '/admin/gestion-event', current: false },
  ];

  const navigationLinks = userRole === "admin" ? navigationAdmin : userRole === "event_creator" ? navigationCreator : navigation;

  const handleLogout = () => {
    localStorage.removeItem("user_token");
    window.location.reload();
  };

  return (
      <Disclosure>
        <nav className="fixed w-full top-0 z-50 bg-white">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div className="relative flex h-16 items-center justify-between">
              {/* Mobile menu button */}
              <div className="absolute inset-y-0 left-0 flex items-center sm:hidden">
                <Disclosure.Button
                    className="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-700 hover:text-white">
                  {({open}) => (
                      <>
                        <span className="sr-only">{t("open_menu")}</span>
                        {open ? (
                            <XMarkIcon className="block h-6 w-6" aria-hidden="true"/>
                        ) : (
                            <Bars3Icon className="block h-6 w-6" aria-hidden="true"/>
                        )}
                      </>
                  )}
                </Disclosure.Button>
              </div>

              {/* Desktop navigation */}
              <img src="/logo/logo.png" width="100" />
              <div className="flex-1 flex items-center justify-center sm:items-stretch sm:justify-start">

                <div className="hidden sm:ml-6 sm:block">

                  <div className="flex space-x-4">
                    {navigationLinks.map((item) => (
                        <Link key={item.name} to={item.href}
                              className="text-gray-900 hover:text-gray-700 px-3 py-2 text-sm font-medium">
                          {item.name}
                        </Link>
                    ))}
                  </div>
                </div>
              </div>

              {/* User menu / Auth buttons */}
              <div
                  className="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                {user ? (
                    <Menu as="div" className="relative ml-3">
                      <div>
                        <MenuButton
                            className="relative flex rounded-full bg-gray-800 text-sm focus:ring-offset-2 focus:ring-offset-gray-800">
                          <span className="absolute -inset-1.5"/>
                          <img alt="Profile" src="/image/user1.png" className="h-8 w-8 rounded-full"/>
                        </MenuButton>
                      </div>
                      <MenuItems
                          className="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                        <MenuItem>
                          {({active}) => (
                              <Link to="/profile" className={`block px-4 py-2 text-sm ${active ? 'bg-gray-100' : ''}`}>
                                {t("profile.title")}
                              </Link>
                          )}
                        </MenuItem>
                        <MenuItem>
                          {({active}) => (
                              <button onClick={handleLogout}
                                      className={`block w-full text-left px-4 py-2 text-sm ${active ? 'bg-gray-100' : ''}`}>
                                {t("logout")}
                              </button>
                          )}
                        </MenuItem>
                      </MenuItems>
                    </Menu>
                ) : (
                    <div className="hidden lg:flex lg:flex-1 lg:justify-end">
                      <button className="text-sm font-semibold text-gray-900" onClick={() => setIsOpenRegister(true)}>
                        {t("register")}
                      </button>
                      <button className="ml-5 text-sm font-semibold text-gray-900" onClick={() => setIsOpenLogin(true)}>
                        {t("login")} <span aria-hidden="true">&rarr;</span>
                      </button>
                    </div>
                )}
                <Languages/>
              </div>
            </div>
          </div>
        </nav>


        <Dialog style={{zIndex: 11, position: "fixed", top: 0, left: 0, right: 0, bottom: 0, display: "flex", justifyContent: "center", alignItems: "center"}} open={isOpenLogin} onClose={() => setIsOpenLogin(false)}>
          <Login closeModal={() => setIsOpenLogin(false)} />
        </Dialog>
        <Dialog  style={{zIndex: 11, position: "fixed", top: 0, left: 0, right: 0, bottom: 0, display: "flex", justifyContent: "center", alignItems: "center"}} open={isOpenRegister} onClose={() => setIsOpenRegister(false)}>
          <Register closeModal={() => setIsOpenRegister(false)} />
        </Dialog>
      </Disclosure>
  );
}
