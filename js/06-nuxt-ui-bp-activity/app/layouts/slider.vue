<script setup lang="ts">
useHead({
	bodyAttrs: {
		class: 'bg-tertiary font-b24-system text-base-900 antialiased',
	}
})

const route = useRoute()

</script>

<template>
	<div class="">
		<slot></slot>
	</div>
</template>