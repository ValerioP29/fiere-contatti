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
                primary: '#4F46E5',
                'primary-hover': '#4338CA',
                secondary: '#1E293B',
                accent: '#F59E0B',
                background: '#EEF2FF',
                surface: '#F8FAFC',
                border: '#CBD5E1',
                text: '#0F172A',
                'text-muted': '#475569',
                success: '#0F766E',
                danger: '#BE123C',
                'danger-hover': '#9F1239',
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
                soft: '0 2px 4px rgba(15, 23, 42, 0.05), 0 12px 28px rgba(79, 70, 229, 0.08)',
                card: '0 4px 10px rgba(15, 23, 42, 0.08), 0 16px 36px rgba(30, 41, 59, 0.12)',
            },
        },
    },

    plugins: [forms],
};
