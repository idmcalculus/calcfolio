/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./components/**/*.{js,vue,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./plugins/**/*.{js,ts}",
    "./nuxt.config.{js,ts}",
    "./app.vue"
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#fef2f2',
          100: '#fee2e2',
          200: '#fecaca',
          300: '#fca5a5',
          400: '#f87171',
          500: '#F54747',
          600: '#dc2626',
          700: '#b91c1c',
          800: '#991b1b',
          900: '#7f1d1d',
          950: '#450a0a',
          DEFAULT: '#F54747'
        },
        'accent': '#6c63ff',
        'darkBg': '#0d0d0d',
        'lightBg': '#ffffff'
      },
      fontFamily: {
        sans: ['Manrope', 'sans-serif'],
      },
      animation: {
        'shine': 'shine 3s linear infinite',
        'blink': 'blink 0.7s steps(1) infinite'
      },
      keyframes: {
        shine: {
          'to': { 'background-position': '200% center' }
        },
        blink: {
          '50%': { 'border-color': 'transparent' }
        }
      }
    }
  },
  plugins: [],
}
