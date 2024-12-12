import defaultTheme from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./vendor/savannabits/filament-flatpickr/**/*.blade.php",
        "./vendor/guava/calendar/resources/**/*.blade.php",
        "./vendor/awcodes/filament-quick-create/resources/**/*.blade.php",
        "\vendor/awcodes/overlook/resources/**/*.blade.php",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            spacing: {
                "input-padding": "4px", // Example custom spacing
            },
            fontSize: {
                "input-lg": "14px", // Custom font size for inputs
            },
        },
    },
    plugins: [],
};
