export default defineAppConfig({
	// https://bitrix24.github.io/b24ui/guide/getting-started.html
	toaster: {
		position: 'top-right' as const,
		expand: true,
		duration: 8000
	},
	b24ui: {
		colorMode: false
	}
})
