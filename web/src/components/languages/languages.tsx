import { Menu, Transition } from "@headlessui/react";
import { Fragment } from "react";
import { useTranslation } from "react-i18next";
import { ChevronDownIcon } from "@heroicons/react/24/solid";

export default function Languages() {
    const {  i18n } = useTranslation();

    const changeLanguage = (lang: string) => {
        i18n.changeLanguage(lang);
        localStorage.setItem("language", lang);
    };

    return (
        <div className="relative inline-block text-left ml-2">
            <Menu as="div" className="relative">
                <div>
                    <Menu.Button className="inline-flex  justify-center rounded-md    px-4 py-2 text-sm font-medium text-gray-70">
                        🌍 {i18n.language.toUpperCase()}
                        <ChevronDownIcon className="ml-2 h-5 w-5 text-gray-500" />
                    </Menu.Button>
                </div>

                <Transition
                    as={Fragment}
                    enter="transition ease-out duration-100"
                    enterFrom="transform opacity-0 scale-95"
                    enterTo="transform opacity-100 scale-100"
                    leave="transition ease-in duration-75"
                    leaveFrom="transform opacity-100 scale-100"
                    leaveTo="transform opacity-0 scale-95"
                >
                    <Menu.Items className="absolute right-0 mt-2 w-36 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                        <div className="py-1">
                            <Menu.Item>
                                {({ active }) => (
                                    <button
                                        onClick={() => changeLanguage("fr")}
                                        className={`${
                                            active ? "bg-gray-100" : ""
                                        } flex w-full items-center px-4 py-2 text-sm text-gray-700`}
                                    >
                                        🇫🇷 Français
                                    </button>
                                )}
                            </Menu.Item>
                            <Menu.Item>
                                {({ active }) => (
                                    <button
                                        onClick={() => changeLanguage("en")}
                                        className={`${
                                            active ? "bg-gray-100" : ""
                                        } flex w-full items-center px-4 py-2 text-sm text-gray-700`}
                                    >
                                        🇬🇧 English
                                    </button>
                                )}
                            </Menu.Item>
                        </div>
                    </Menu.Items>
                </Transition>
            </Menu>
        </div>
    );
}
