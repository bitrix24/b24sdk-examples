/** @type {import('tailwindcss').Config} */
import b24style from '@bitrix24/b24style'

export default {
	content: [
		"./components/**/*.{js,vue,ts}",
		"./layouts/**/*.vue",
		"./pages/**/*.vue",
		"./plugins/**/*.{js,ts}",
		"./app.vue",
		"./error.vue",
	],
	theme: {
		extend: {},
	},
	plugins: [
		b24style({
			logs: false,
			useLocalFonts: false,
		})
	]
}
