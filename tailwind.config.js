import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import plugin from 'tailwindcss/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './public/**/*.html', // caso existam HTMLs do tema
        './assets/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                urbanist: ['Urbanist', 'sans-serif'],
                poppins: ['Poppins', 'sans-serif'],
            },

            width: {
                66: '66%',
                70: '70%',
                88: '88%',
            },

            fontSize: {
                xs: '12px',
                sm: '14px',
                base: '16px',
                lg: '18px',
                xl: '20px',
                '2xl': '24px',
                '3xl': '28px',
                '4xl': '32px',
                '5xl': '48px',
            },

            colors: {
                orange: {
                    50: '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#ff8800', // 🍊 padrão MM Criativos / Popfy
                    600: '#ea580c',
                    700: '#c2410c',
                    800: '#9a3412',
                    900: '#7c2d12',
                },
                dark: {
                    700: '#383838',   // adicionar
                    800: '#262626',
                    900: '#111827',
                },

                darkblack: {
                    300: '#747681',
                    400: '#2A313C',
                    500: '#23262B',
                    600: '#1D1E24',
                    700: '#151515',
                },
                success: {
                    50: '#D9FBE6',
                    100: '#B7FFD1',
                    200: '#4ADE80',
                    300: '#22C55E',
                    400: '#16A34A',
                },
                warning: {
                    100: '#FDE047',
                    200: '#FACC15',
                    300: '#EAB308',
                },
                error: {
                    50: '#FCDEDE',
                    100: '#FF7171',
                    200: '#FF4747',
                    300: '#DD3333',
                },
                bgray: {
                    50: '#FAFAFA',
                    100: '#F7FAFC',
                    200: '#EDF2F7',
                    300: '#E2E8F0',
                    400: '#CBD5E0',
                    500: '#A0AEC0',
                    600: '#718096',
                    700: '#4A5568',
                    800: '#262626',
                    900: '#1A202C',
                },
                purple: '#936DFF',
                basicInterface: '#04091E',
                lightGray: '#F3F7F8',
            },

            lineHeight: {
                'extra-loose': '44.8px',
                'big-loose': '140%',
                130: '130%',
                150: '150%',
                160: '160%',
                175: '175%',
                180: '180%',
                220: '220%',
            },

            letterSpacing: {
                tight: '-0.96px',
                40: '-0.4px',
            },

            borderRadius: {
                20: '20px',
            },

            backgroundImage: {
                'bgc-dark': "url('/assets/images/background/comming-soon-dark.svg')",
                'bgc-light': "url('/assets/images/background/coming-soon-bg.svg')",
                'notfound-dark': "url('/assets/images/background/404-dark.jpg')",
                'notfound-light': "url('/assets/images/background/404-bg.png')",
            },
        },
    },

    plugins: [
        forms,
        plugin(function ({ addVariant }) {
            addVariant('current', '&.active');
        }),
    ],
};
