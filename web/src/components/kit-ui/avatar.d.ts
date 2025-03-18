import * as Headless from '@headlessui/react';
import React from 'react';
type AvatarProps = {
    src?: string | null;
    square?: boolean;
    initials?: string;
    alt?: string;
    className?: string;
};
export declare function Avatar({ src, square, initials, alt, className, ...props }: AvatarProps & React.ComponentPropsWithoutRef<'span'>): import("react/jsx-runtime").JSX.Element;
export declare const AvatarButton: React.ForwardRefExoticComponent<(AvatarProps & (Omit<Headless.ButtonProps<"button">, "className" | "as"> | Omit<Omit<{
    href: string;
} & Omit<React.DetailedHTMLProps<React.AnchorHTMLAttributes<HTMLAnchorElement>, HTMLAnchorElement>, "ref"> & React.RefAttributes<HTMLAnchorElement>, "ref">, "className">)) & React.RefAttributes<HTMLElement>>;
export {};
