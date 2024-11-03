<script setup lang="ts">
useHead({
	bodyAttrs: {
		class: 'bg-tertiary font-b24-system text-base-900 antialiased px-lg'
	}
})
</script>

<template>
	<div class="min-h-screen overflow-hidden flex flex-col flex-1 text-ellipsis max-w-fill">
		<slot></slot>
	</div>
</template>