import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                background: '#070A12',
                surface: '#0E1422',
                'surface-2': '#151C2E',
                brand: {
                    DEFAULT: '#F5B301',
                    soft: '#FFE08A',
                    dark: '#C49000',
                },
                success: '#16C784',
                danger: '#EA3943',
                info: '#3861FB',
                purple: '#7C3AED',
                muted: '#94A3B8',
                border: '#263044',
            },
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
                mono: ['IBM Plex Mono', ...defaultTheme.fontFamily.mono],
                display: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                glass: '0 8px 32px rgba(0, 0, 0, 0.35)',
                glow: '0 0 40px rgba(245, 179, 1, 0.15)',
            },
            backgroundImage: {
                'hero-grid':
                    'radial-gradient(ellipse 80% 50% at 50% -20%, rgba(245, 179, 1, 0.18), transparent), radial-gradient(ellipse 60% 40% at 90% 20%, rgba(56, 97, 251, 0.12), transparent)',
                'surface-glow':
                    'linear-gradient(135deg, rgba(245, 179, 1, 0.08), transparent 40%), linear-gradient(225deg, rgba(124, 58, 237, 0.06), transparent 40%)',
            },
            animation: {
                'ticker': 'ticker 40s linear infinite',
                'fade-up': 'fadeUp 0.6s ease-out both',
                'pulse-soft': 'pulseSoft 3s ease-in-out infinite',
            },
            keyframes: {
                ticker: {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
                fadeUp: {
                    '0%': { opacity: '0', transform: 'translateY(16px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.7' },
                },
            },
        },
    },

    plugins: [forms],
};
