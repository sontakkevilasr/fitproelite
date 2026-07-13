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
            },
            colors: {
                primary: {
                    50: '#effcf6',
                    100: '#c9f7e4',
                    200: '#94eecb',
                    300: '#5fe0b1',
                    400: '#33cb99',
                    500: '#14b382',
                    600: '#0d9268',
                    700: '#0d7456',
                    800: '#0f5c46',
                    900: '#0e4b3a',
                    950: '#062b21',
                },
            },
            spacing: {
                sidebar: '17rem',
            },
        },
    },

    plugins: [forms],
};
