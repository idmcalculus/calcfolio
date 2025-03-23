import type { Config } from 'tailwindcss'

export default <Partial<Config> & { darkMode: 'class' }> {
	darkMode: 'class',
	content: [
	  './app.vue',
	  './components/**/*.{vue,js,ts}',
	  './pages/**/*.{vue,js,ts}',
	  './composables/**/*.{js,ts}'
	],
	theme: {
	  extend: {
		colors: {
		  primary: '#F54747',
		  accent: '#6c63ff',
		  darkBg: '#1a1a1a',
		  lightBg: '#ffffff'
		}
	  }
	},
	plugins: []
}