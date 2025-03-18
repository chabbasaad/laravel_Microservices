import ListEvents from "../events/list-events.tsx";
import Footer from "../../../components/footer/footer.tsx";
import {useTranslation} from "react-i18next";

export default function Home() {
    const { t } = useTranslation();

    return (
        <div className="mt-17 w-full">
        <div className="bg-white ">
            <div className="relative">
                <div className="mx-auto max-w-7xl">
                    <div className="relative z-5 lg:w-full lg:max-w-2xl">
                        <svg
                            viewBox="0 0 105 100"
                            preserveAspectRatio="none"
                            aria-hidden="true"
                            className="absolute inset-y-0 right-8 hidden h-full w-80 translate-x-1/2 transform fill-white lg:block"
                        >
                            <polygon points="0,0 80,0 50,100 0,100" />
                        </svg>
                        <div className="relative px-6 py-32 sm:py-40 lg:px-8 lg:py-56 lg:pr-0">
                            <div className="mx-auto max-w-2xl lg:mx-0 lg:max-w-xl">
                                <h1 className="text-2xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-7xl">
                                    {t('title1')}
                                </h1>
                                <p className="mt-8 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8">
                                    {t('title2')}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="bg-gray-50 h-200 lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
                    <img
                        alt="Événement en direct"
                        src="/image/image1.jpg"
                        className="aspect-3/2 object-cover lg:aspect-auto lg:size-full"
                    />
                </div>
            </div>
        </div>
            <ListEvents/>
            <Footer/>
        </div>
    )
}
