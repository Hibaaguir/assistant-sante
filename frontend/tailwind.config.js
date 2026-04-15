export default {
    content: ["./index.html", "./src/**/*.vue", "./src/**/*.js"],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Montserrat", "system-ui", "sans-serif"],
            },
            letterSpacing: {
                tight: "-0.01em",
            },
            colors: {
                primary: {
                    DEFAULT: "#149bd7",
                },
                blue: {
                    50: "#eef9fd",
                    100: "#d7eff9",
                    200: "#afdff3",
                    300: "#82cdec",
                    400: "#4cb8e2",
                    500: "#149bd7",
                    600: "#128dc4",
                    700: "#0f74a1",
                    800: "#0d5c7f",
                    900: "#0a4660",
                },
                brand: {
                    50: "#fff7f7",
                    100: "#ffe9ed",
                    200: "#ffd9e0",
                    300: "#ffc5d4",
                    400: "#ffacc2",
                    500: "#ff8aad",
                    600: "#ff6895",
                    700: "#ff4670",
                    800: "#ff1f57",
                    900: "#c2185b",
                },
            },
        },
    },
    plugins: [],
};
