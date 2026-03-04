import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                primary: '#00539f',
                'primary-hover': '#003f79',
                secondary: '#0f172a',
                accent: '#f58220',
                background: '#f4f7fb',
                surface: '#ffffff',
                border: '#d6dee8',
                text: '#1f2937',
                'text-muted': '#64748b',
                success: '#0f766e',
                danger: '#b42318',
                'danger-hover': '#8f1c14',
            },
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                md: '0.5rem',
                lg: '0.75rem',
                xl: '0.95rem',
            },
            boxShadow: {
                soft: '0 1px 2px rgba(15, 23, 42, 0.06), 0 8px 24px rgba(15, 23, 42, 0.06)',
                card: '0 1px 2px rgba(15, 23, 42, 0.07), 0 12px 32px rgba(15, 23, 42, 0.08)',
            },
        },
    },

    plugins: [forms],
};
