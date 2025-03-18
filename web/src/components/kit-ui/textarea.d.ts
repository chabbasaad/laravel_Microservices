import * as Headless from '@headlessui/react';
import React from 'react';
export declare const Textarea: React.ForwardRefExoticComponent<{
    className?: string;
    resizable?: boolean;
} & Omit<Headless.TextareaProps<"textarea">, "className" | "as"> & React.RefAttributes<HTMLTextAreaElement>>;
