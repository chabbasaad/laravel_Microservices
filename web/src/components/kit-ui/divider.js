import { jsx as _jsx } from "react/jsx-runtime";
import clsx from 'clsx';
export function Divider({ soft = false, className, ...props }) {
    return (_jsx("hr", { role: "presentation", ...props, className: clsx(className, 'w-full border-t', soft && 'border-zinc-950/5 dark:border-white/5', !soft && 'border-zinc-950/10 dark:border-white/10') }));
}
