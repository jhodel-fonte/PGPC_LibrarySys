import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './vendor/tallstackui/tallstackui/src/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                navy: {
                    primary: '#102B70',
                    hover: '#0B225E',
                    overlay: '#071943',
                },
                gold: {
                    accent: '#FCC719',
                },
                blue: {
                    progress: '#3B82F6',
                    focus: '#EFF6FF',
                    decorative: '#DBEAFE',
                },
                base: {
                    text: '#0F172A',
                    label: '#334155',
                    secondary: '#64748B',
                    muted: '#94A3B8',
                },
                border: {
                    DEFAULT: '#E2E8F0',
                    hover: '#CBD5E1',
                },
                soft: '#F8FAFC',
                error: {
                    bg: '#FEF2F2',
                    border: '#FECACA',
                    text: '#B91C1C',
                    red: '#EF4444',
                }
            },
            fontFamily: {
                sans: ['Open Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
        animation: {
            'pgpc-pulse': 'pgpc-pulse 1.8s cubic-bezier(.4, 0, .6, 1) infinite',
            'pgpc-spin': 'pgpc-spin 1.2s linear infinite',
        },
        keyframes: {
            'pgpc-pulse': {
                '0%, 100%': { transform: 'scale(1)' },
                '50%': { transform: 'scale(1.035)' },
            },
            'pgpc-spin': {
                'to': { transform: 'rotate(360deg)' },
            }
        },
    },

    plugins: [forms],
};
