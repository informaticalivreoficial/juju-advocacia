import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

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
                    50: '#f4f3fb',
                    100: '#e9e5f8',
                    200: '#d3cbf0',
                    300: '#b0a2e2',
                    400: '#8a74d0',
                    500: '#6d55ba',
                    600: '#5c43a3',
                    700: '#4d3685',
                    800: '#3d2b68',
                    900: '#322552',
                    950: '#1d1436',
                },
            },
        },
    },

    plugins: [forms],
};
