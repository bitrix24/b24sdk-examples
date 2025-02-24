<script setup lang="ts">
useHead({
	bodyAttrs: {
		class: 'bg-tertiary font-b24-system text-base-900 antialiased',
	}
})
useHead({
	bodyAttrs: {
		class: 'text-base-master dark:text-base-150 bg-base-50 dark:bg-base-dark font-b24-system antialiased',
	}
})
</script>

<template>
	<div class="relative flex flex-col" >
		<slot></slot>
	</div>
</template>