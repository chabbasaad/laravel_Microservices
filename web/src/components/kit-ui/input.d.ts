import * as Headless from '@headlessui/react';
import React from 'react';
export declare function InputGroup({ children }: React.ComponentPropsWithoutRef<'span'>): import("react/jsx-runtime").JSX.Element;
declare const dateTypes: string[];
type DateType = (typeof dateTypes)[number];
export declare const Input: React.ForwardRefExoticComponent<{
    className?: string;
    type?: "email" | "number" | "password" | "search" | "tel" | "text" | "url" | DateType;
} & Omit<Headless.InputProps<"input">, "className" | "as"> & React.RefAttributes<HTMLInputElement>>;
export {};
