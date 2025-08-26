<script setup lang="ts">
useHead({
	bodyAttrs: {
		class: 'bg-tertiary font-b24-system text-base-900 antialiased w-full',
	}
})

const route = useRoute()

</script>

<template>
	<div class="flex flex-col" >
		<div class="px-lg mb-xs2">
			<div class="min-h-7xl h-7xl w-full flex flex-row items-center justify-normal gap-lg2 border-b border-b-base-900/0.1">
				<div class="pl-xs2 text-4xl font-light font-b24-secondary text-base-master">{{ route.meta.title }}</div>
				<div class="pr-xs2 grow flex gap-4 flex-row items-end justify-end text-lg" v-if="route.path === '/'">
					<div class="text-md font-light text-base-900">
						<a
							class="hover:text-primary-link hover:underline underline-offset-2 transition-opacity hover:opacity-80"
							href="https://bitrix24.github.io/b24style/reference/colors.html"
							target="_blank"
						>@bitrix24/b24style</a>
					</div>
					<div class="text-md font-light text-base-900">
						<a
							class="hover:text-primary-link hover:underline underline-offset-2 transition-opacity hover:opacity-80"
							href="https://bitrix24.github.io/b24icons/guide/icons.html"
							target="_blank"
						>@bitrix24/b24icons</a>
					</div>
					<div class="text-md font-light text-base-900">
						<a
							class="hover:text-primary-link hover:underline underline-offset-2 transition-opacity hover:opacity-80"
							href="https://bitrix24.github.io/b24jssdk/"
							target="_blank"
						>@bitrix24/b24jssdk</a>
					</div>
				</div>
			</div>
		</div>
		<slot></slot>
	</div>
</template>