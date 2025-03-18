import * as Headless from '@headlessui/react';
import { Fragment } from 'react';
export declare function Listbox<T>({ className, placeholder, autoFocus, 'aria-label': ariaLabel, children: options, ...props }: {
    className?: string;
    placeholder?: React.ReactNode;
    autoFocus?: boolean;
    'aria-label'?: string;
    children?: React.ReactNode;
} & Omit<Headless.ListboxProps<typeof Fragment, T>, 'as' | 'multiple'>): import("react/jsx-runtime").JSX.Element;
export declare function ListboxOption<T>({ children, className, ...props }: {
    className?: string;
    children?: React.ReactNode;
} & Omit<Headless.ListboxOptionProps<'div', T>, 'as' | 'className'>): import("react/jsx-runtime").JSX.Element;
export declare function ListboxLabel({ className, ...props }: React.ComponentPropsWithoutRef<'span'>): import("react/jsx-runtime").JSX.Element;
export declare function ListboxDescription({ className, children, ...props }: React.ComponentPropsWithoutRef<'span'>): import("react/jsx-runtime").JSX.Element;
