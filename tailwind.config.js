import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                background: '#070A12',
                surface: '#0E1422',
                'surface-2': '#151C2E',
                brand: {
                    DEFAULT: '#F5B301',
                    soft: '#FFE08A',
                },
                success: '#16C784',
                danger: '#EA3943',
                info: '#3861FB',
                purple: '#7C3AED',
                'text-main': '#F8FAFC',
                'text-muted': '#94A3B8',
                border: '#263044',
            },
            boxShadow: {
                glass: '0 8px 32px 0 rgba(0, 0, 0, 0.37)',
            },
            backgroundImage: {
                'brand-gradient': 'linear-gradient(135deg, #F5B301 0%, #FFE08A 100%)',
            },
        },
    },

    plugins: [forms],
};
