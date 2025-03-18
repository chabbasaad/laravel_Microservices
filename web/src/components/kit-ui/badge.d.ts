import * as Headless from '@headlessui/react';
import React from 'react';
declare const colors: {
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
    zinc: string;
};
type BadgeProps = {
    color?: keyof typeof colors;
};
export declare function Badge({ color, className, ...props }: BadgeProps & React.ComponentPropsWithoutRef<'span'>): import("react/jsx-runtime").JSX.Element;
export declare const BadgeButton: React.ForwardRefExoticComponent<(BadgeProps & ({
    className?: string;
    children: React.ReactNode;
} & (Omit<Headless.ButtonProps<"button">, "className" | "as"> | Omit<Omit<{
    href: string;
} & Omit<React.DetailedHTMLProps<React.AnchorHTMLAttributes<HTMLAnchorElement>, HTMLAnchorElement>, "ref"> & React.RefAttributes<HTMLAnchorElement>, "ref">, "className">))) & React.RefAttributes<HTMLElement>>;
export {};
