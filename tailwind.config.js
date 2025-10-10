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
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Merriweather', 'Georgia', 'serif'],
            },
            colors: {
                barber: {
                    50: '#fdf7f1',
                    100: '#f8ebd9',
                    200: '#efd3ab',
                    300: '#e6b97f',
                    400: '#db934c',
                    500: '#c96f1f', // dourado/laranja
                    600: '#a45317',
                    700: '#7b3b0f',
                    800: '#4f2a0a',
                    900: '#2b1704', // escuro
                    red: '#6b0f0f',
                    black: '#0b0b0b',
                }
            }
        },
    },

    plugins: [forms],
};
