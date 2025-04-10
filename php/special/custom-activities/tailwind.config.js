/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
      "./public/*.{js,php,html}",
      "./layouts/*.{js,php,html}",
    ],
	theme: {
		extend: {},
	},
	plugins: [
		require('@bitrix24/b24style')({
			logs: false,
			useLocalFonts: false,
		}),
	],
}