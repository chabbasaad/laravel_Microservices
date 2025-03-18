import { jsx as _jsx } from "react/jsx-runtime";
/**
 * TODO: Update this component to use your client-side framework's link
 * component. We've provided examples of how to do this for Next.js, Remix, and
 * Inertia.js in the Catalyst documentation:
 *
 * https://catalyst.tailwindui.com/docs#client-side-router-integration
 */
import * as Headless from '@headlessui/react';
import { forwardRef } from 'react';
export const Link = forwardRef(function Link(props, ref) {
    return (_jsx(Headless.DataInteractive, { children: _jsx("a", { ...props, ref: ref }) }));
});
