import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import * as Headless from '@headlessui/react';
import clsx from 'clsx';
import { forwardRef } from 'react';
import { TouchTarget } from './button';
import { Link } from './link';
export function Avatar({ src = null, square = false, initials, alt = '', className, ...props }) {
    return (_jsxs("span", { "data-slot": "avatar", ...props, className: clsx(className, 
        // Basic layout
        'inline-grid shrink-0 align-middle [--avatar-radius:20%] [--ring-opacity:20%] *:col-start-1 *:row-start-1', 'outline -outline-offset-1 outline-black/(--ring-opacity) dark:outline-white/(--ring-opacity)', 
        // Add the correct border radius
        square ? 'rounded-(--avatar-radius) *:rounded-(--avatar-radius)' : 'rounded-full *:rounded-full'), children: [initials && (_jsxs("svg", { className: "size-full fill-current p-[5%] text-[48px] font-medium uppercase select-none", viewBox: "0 0 100 100", "aria-hidden": alt ? undefined : 'true', children: [alt && _jsx("title", { children: alt }), _jsx("text", { x: "50%", y: "50%", alignmentBaseline: "middle", dominantBaseline: "middle", textAnchor: "middle", dy: ".125em", children: initials })] })), src && _jsx("img", { className: "size-full", src: src, alt: alt })] }));
}
export const AvatarButton = forwardRef(function AvatarButton({ src, square = false, initials, alt, className, ...props }, ref) {
    let classes = clsx(className, square ? 'rounded-[20%]' : 'rounded-full', 'relative inline-grid focus:outline-hidden data-focus:outline-2 data-focus:outline-offset-2 data-focus:outline-blue-500');
    return 'href' in props ? (_jsx(Link, { ...props, className: classes, ref: ref, children: _jsx(TouchTarget, { children: _jsx(Avatar, { src: src, square: square, initials: initials, alt: alt }) }) })) : (_jsx(Headless.Button, { ...props, className: classes, ref: ref, children: _jsx(TouchTarget, { children: _jsx(Avatar, { src: src, square: square, initials: initials, alt: alt }) }) }));
});
