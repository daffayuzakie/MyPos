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
                brand: {
                    sidebar: '#d6ece8', // Light mint soft for sidebar
                    'sidebar-hover': '#c7e4df',
                    primary: '#447d91', // Blue-teal button/active pill
                    'primary-dark': '#386a7c',
                    accent: '#26424e', // Deep dark blue-slate
                    bg: '#eef5f8', // Very light soft ice-blue main background
                    card: '#f0f7fa',
                    border: '#dbe7ec',
                    muted: '#65828e',
                }
            }
        },
    },

    plugins: [forms],
};
