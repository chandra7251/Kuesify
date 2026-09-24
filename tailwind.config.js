import forms from '@tailwindcss/forms';
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    primary: '#3154D5',
                    secondary: '#90CB31',
                    lime: '#A8D83D',
                    accent: '#E6F1F5',
                    dark: '#233EA8',
                    hover: '#2645B8',
                },
                support: {
                    1: '#D7A928',
                    2: '#7C869C',
                    3: '#4B392E',
                },
                status: {
                    success: '#90CB31',
                    warning: '#D7A928',
                    danger: '#D9656D',
                },
            },
            boxShadow: {
                figma: '0 4px 16px rgba(0, 0, 0, 0.08)',
                'figma-card': '0 4px 14px rgba(0, 0, 0, 0.07)',
                'figma-sm': '0 2px 8px rgba(0, 0, 0, 0.06)',
                'figma-hover': '0 6px 20px rgba(0, 0, 0, 0.12)',
            },
        },
    },

    plugins: [forms],
};
