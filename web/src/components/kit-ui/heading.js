import { jsx as _jsx } from "react/jsx-runtime";
import clsx from 'clsx';
export function Heading({ className, level = 1, ...props }) {
    let Element = `h${level}`;
    return (_jsx(Element, { ...props, className: clsx(className, 'text-2xl/8 font-semibold text-zinc-950 sm:text-xl/8 dark:text-white') }));
}
export function Subheading({ className, level = 2, ...props }) {
    let Element = `h${level}`;
    return (_jsx(Element, { ...props, className: clsx(className, 'text-base/7 font-semibold text-zinc-950 sm:text-sm/6 dark:text-white') }));
}
