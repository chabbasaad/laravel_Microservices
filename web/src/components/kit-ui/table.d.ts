import type React from 'react';
export declare function Table({ bleed, dense, grid, striped, className, children, ...props }: {
    bleed?: boolean;
    dense?: boolean;
    grid?: boolean;
    striped?: boolean;
} & React.ComponentPropsWithoutRef<'div'>): import("react/jsx-runtime").JSX.Element;
export declare function TableHead({ className, ...props }: React.ComponentPropsWithoutRef<'thead'>): import("react/jsx-runtime").JSX.Element;
export declare function TableBody(props: React.ComponentPropsWithoutRef<'tbody'>): import("react/jsx-runtime").JSX.Element;
export declare function TableRow({ href, target, title, className, ...props }: {
    href?: string;
    target?: string;
    title?: string;
} & React.ComponentPropsWithoutRef<'tr'>): import("react/jsx-runtime").JSX.Element;
export declare function TableHeader({ className, ...props }: React.ComponentPropsWithoutRef<'th'>): import("react/jsx-runtime").JSX.Element;
export declare function TableCell({ className, children, ...props }: React.ComponentPropsWithoutRef<'td'>): import("react/jsx-runtime").JSX.Element;
