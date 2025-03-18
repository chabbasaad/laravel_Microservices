import * as Headless from '@headlessui/react';
export declare function RadioGroup({ className, ...props }: {
    className?: string;
} & Omit<Headless.RadioGroupProps, 'as' | 'className'>): import("react/jsx-runtime").JSX.Element;
export declare function RadioField({ className, ...props }: {
    className?: string;
} & Omit<Headless.FieldProps, 'as' | 'className'>): import("react/jsx-runtime").JSX.Element;
declare const colors: {
    'dark/zinc': string[];
    'dark/white': string[];
    white: string;
    dark: string;
    zinc: string;
    red: string;
    orange: string;
    amber: string;
    yellow: string;
    lime: string;
    green: string;
    emerald: string;
    teal: string;
    cyan: string;
    sky: string;
    blue: string;
    indigo: string;
    violet: string;
    purple: string;
    fuchsia: string;
    pink: string;
    rose: string;
};
type Color = keyof typeof colors;
export declare function Radio({ color, className, ...props }: {
    color?: Color;
    className?: string;
} & Omit<Headless.RadioProps, 'as' | 'className' | 'children'>): import("react/jsx-runtime").JSX.Element;
export {};
