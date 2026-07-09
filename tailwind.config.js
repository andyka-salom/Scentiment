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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                serif: ['Playfair Display', ...defaultTheme.fontFamily.serif],
                outfit: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                charcoal: '#2B2B2B',
                gold: '#C6A961',
                ivory: '#FAF7F0',
            }
        },
    },

    plugins: [forms],
};
