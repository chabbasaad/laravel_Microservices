import * as Headless from '@headlessui/react';
import type React from 'react';
export declare function SwitchGroup({ className, ...props }: React.ComponentPropsWithoutRef<'div'>): import("react/jsx-runtime").JSX.Element;
export declare function SwitchField({ className, ...props }: {
    className?: string;
} & Omit<Headless.FieldProps, 'as' | 'className'>): import("react/jsx-runtime").JSX.Element;
declare const colors: {
    'dark/zinc': string[];
    'dark/white': string[];
    dark: string[];
    zinc: string[];
    white: string[];
    red: string[];
    orange: string[];
    amber: string[];
    yellow: string[];
    lime: string[];
    green: string[];
    emerald: string[];
    teal: string[];
    cyan: string[];
    sky: string[];
    blue: string[];
    indigo: string[];
    violet: string[];
    purple: string[];
    fuchsia: string[];
    pink: string[];
    rose: string[];
};
type Color = keyof typeof colors;
export declare function Switch({ color, className, ...props }: {
    color?: Color;
    className?: string;
} & Omit<Headless.SwitchProps, 'as' | 'className' | 'children'>): import("react/jsx-runtime").JSX.Element;
export {};
