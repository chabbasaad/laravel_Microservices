import type React from 'react';
export declare function Pagination({ 'aria-label': ariaLabel, className, ...props }: React.ComponentPropsWithoutRef<'nav'>): import("react/jsx-runtime").JSX.Element;
export declare function PaginationPrevious({ href, className, children, }: React.PropsWithChildren<{
    href?: string | null;
    className?: string;
}>): import("react/jsx-runtime").JSX.Element;
export declare function PaginationNext({ href, className, children, }: React.PropsWithChildren<{
    href?: string | null;
    className?: string;
}>): import("react/jsx-runtime").JSX.Element;
export declare function PaginationList({ className, ...props }: React.ComponentPropsWithoutRef<'span'>): import("react/jsx-runtime").JSX.Element;
export declare function PaginationPage({ href, className, current, children, }: React.PropsWithChildren<{
    href: string;
    className?: string;
    current?: boolean;
}>): import("react/jsx-runtime").JSX.Element;
export declare function PaginationGap({ className, children, ...props }: React.ComponentPropsWithoutRef<'span'>): import("react/jsx-runtime").JSX.Element;
