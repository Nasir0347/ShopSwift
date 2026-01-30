/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {
            colors: {
                // Shopify Polaris-inspired palette
                surface: {
                    DEFAULT: '#FFFFFF',
                    subdued: '#F7F8FA', // Page background
                    hover: '#F1F2F4',
                },
                content: {
                    DEFAULT: '#202223', // Text main
                    subdued: '#6D7175', // Text secondary
                },
                primary: {
                    DEFAULT: '#008060', // Shopify Green
                    hover: '#006E52',
                    text: '#FFFFFF',
                },
                critical: {
                    DEFAULT: '#D82C0D',
                    subdued: '#FFF4F4',
                },
                warning: {
                    DEFAULT: '#FFC453',
                    subdued: '#FFEA8A',
                },
                success: {
                    DEFAULT: '#008060',
                    subdued: '#E3F1DF',
                },
                border: {
                    DEFAULT: '#E1E3E5',
                    subdued: '#C9CCCF',
                }
            },
            fontFamily: {
                sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'San Francisco', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'sans-serif'],
            },
            boxShadow: {
                'card': '0px 0px 5px rgba(0, 0, 0, 0.05), 0px 1px 2px rgba(0, 0, 0, 0.1)',
                'popover': '0px 3px 6px -3px rgba(23, 24, 24, 0.08), 0px 8px 20px -4px rgba(23, 24, 24, 0.12)',
            }
        },
    },
    plugins: [],
}
