module.exports = {
  darkMode: 'class',
  content: [
    './components/**/*.{js,vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './plugins/**/*.{js,ts}',
    './app.vue',
    './error.vue',
    './nuxt.config.{js,ts}'
  ],
  theme: {
    extend: {
      colors: {
        'primary': '#F54747',
        'accent': '#6c63ff',
        'darkBg': '#0d0d0d',
        'lightBg': '#ffffff'
      },
      fontFamily: {
        sans: ['Manrope', 'sans-serif'],
      },
    }
  },
  plugins: []
}
