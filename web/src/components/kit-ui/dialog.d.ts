import * as Headless from '@headlessui/react';
import type React from 'react';
import { Text } from './text';
declare const sizes: {
    xs: string;
    sm: string;
    md: string;
    lg: string;
    xl: string;
    '2xl': string;
    '3xl': string;
    '4xl': string;
    '5xl': string;
};
export declare function Dialog({ size, className, children, ...props }: {
    size?: keyof typeof sizes;
    className?: string;
    children: React.ReactNode;
} & Omit<Headless.DialogProps, 'as' | 'className'>): import("react/jsx-runtime").JSX.Element;
export declare function DialogTitle({ className, ...props }: {
    className?: string;
} & Omit<Headless.DialogTitleProps, 'as' | 'className'>): import("react/jsx-runtime").JSX.Element;
export declare function DialogDescription({ className, ...props }: {
    className?: string;
} & Omit<Headless.DescriptionProps<typeof Text>, 'as' | 'className'>): import("react/jsx-runtime").JSX.Element;
export declare function DialogBody({ className, ...props }: React.ComponentPropsWithoutRef<'div'>): import("react/jsx-runtime").JSX.Element;
export declare function DialogActions({ className, ...props }: React.ComponentPropsWithoutRef<'div'>): import("react/jsx-runtime").JSX.Element;
export {};
