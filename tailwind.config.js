import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "primary": "#C59D5F",
                "primary-hover": "#B08D55",
                "background-light": "#f8f7f6",
                "background-dark": "#221c10",
                "stone-custom": "#897d61",
            },
            fontFamily: {
                "display": ["Work Sans", "sans-serif"],
                "sans": ["Work Sans", ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "2xl": "1rem",
                "full": "9999px"
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
