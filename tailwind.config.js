import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
        './app/View/Components/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Sidebar / brand
                sidebar: {
                    DEFAULT: '#0f172a',  // slate-900
                    hover:   '#1e293b',  // slate-800
                    active:  '#1d4ed8',  // blue-700
                    border:  '#1e293b',
                    text:    '#94a3b8',  // slate-400
                    'text-active': '#f8fafc',
                },
                // Status colors
                status: {
                    tba:        '#64748b', // slate
                    acquire:    '#0ea5e9', // sky
                    teardown:   '#f59e0b', // amber
                    estimate:   '#8b5cf6', // violet
                    approval:   '#ec4899', // pink
                    repair:     '#10b981', // emerald
                    reassemble: '#14b8a6', // teal
                    deliver:    '#22c55e', // green
                    hold:       '#f97316', // orange
                    kicked:     '#ef4444', // red
                },
                // Role badge colors
                role: {
                    owner:          '#1d4ed8',
                    pdr_tech:       '#0891b2',
                    sales_advisor:  '#7c3aed',
                    sales_manager:  '#c2410c',
                    ri_tech:        '#0d9488',
                    porter:         '#65a30d',
                    bookkeeper:     '#be185d',
                },
            },
        },
    },

    plugins: [forms],
};
