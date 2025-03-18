import * as Headless from '@headlessui/react';
import React from 'react';
export declare function Sidebar({ className, ...props }: React.ComponentPropsWithoutRef<'nav'>): import("react/jsx-runtime").JSX.Element;
export declare function SidebarHeader({ className, ...props }: React.ComponentPropsWithoutRef<'div'>): import("react/jsx-runtime").JSX.Element;
export declare function SidebarBody({ className, ...props }: React.ComponentPropsWithoutRef<'div'>): import("react/jsx-runtime").JSX.Element;
export declare function SidebarFooter({ className, ...props }: React.ComponentPropsWithoutRef<'div'>): import("react/jsx-runtime").JSX.Element;
export declare function SidebarSection({ className, ...props }: React.ComponentPropsWithoutRef<'div'>): import("react/jsx-runtime").JSX.Element;
export declare function SidebarDivider({ className, ...props }: React.ComponentPropsWithoutRef<'hr'>): import("react/jsx-runtime").JSX.Element;
export declare function SidebarSpacer({ className, ...props }: React.ComponentPropsWithoutRef<'div'>): import("react/jsx-runtime").JSX.Element;
export declare function SidebarHeading({ className, ...props }: React.ComponentPropsWithoutRef<'h3'>): import("react/jsx-runtime").JSX.Element;
export declare const SidebarItem: React.ForwardRefExoticComponent<({
    current?: boolean;
    className?: string;
    children: React.ReactNode;
} & (Omit<Headless.ButtonProps<"button">, "className" | "as"> | Omit<Headless.ButtonProps<React.ForwardRefExoticComponent<{
    href: string;
} & Omit<React.DetailedHTMLProps<React.AnchorHTMLAttributes<HTMLAnchorElement>, HTMLAnchorElement>, "ref"> & React.RefAttributes<HTMLAnchorElement>>>, "className" | "as">)) & React.RefAttributes<HTMLAnchorElement | HTMLButtonElement>>;
export declare function SidebarLabel({ className, ...props }: React.ComponentPropsWithoutRef<'span'>): import("react/jsx-runtime").JSX.Element;
