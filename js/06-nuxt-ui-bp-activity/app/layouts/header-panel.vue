<script setup lang="ts">
import Market1Icon from '@bitrix24/b24icons-vue/main/Market1Icon'
import Settings1Icon from '@bitrix24/b24icons-vue/main/SettingsIcon'
import SearchIcon from '@bitrix24/b24icons-vue/button/SearchIcon'

useHead({
	bodyAttrs: {
		class: 'text-base-master dark:text-base-150 bg-base-50 dark:bg-base-dark font-b24-system antialiased',
	}
})

const route = useRoute()

</script>

<template>
	<div class="relative flex flex-col" >
		
		<div class="fixed top-0 z-1 w-full bg-white shadow-bottom-md p-2 flex items-center justify-between gap-3">
			<B24Link raw to="/" class="flex items-center justify-between gap-0.5">
				<Market1Icon class="size-8 sm:size-16 text-primary" />

				<div class="hidden sm:block text-primary ">
					{{ route.meta.title }}
				</div>
			</B24Link>
			<div class="grow font-b24-primary" v-if="route.path !== '/'">
				<div class="flex flex-row gap-2xs items-end">
					<div class="flex flex-col">
						<div class="text-3xs leading-tight opacity-70 text-base-500 items-center">cooperation</div>
						<B24Link to="/" is-action>For integrators</B24Link>
					</div>
				</div>
			</div>
			
			<div class="flex items-center justify-end gap-4">
				<B24Input
					:trailing-icon="SearchIcon"
					placeholder="Search..."
					class="w-full max-w-[400px]"
					rounded
				/>
				
				<B24Button class="self-center" :icon="Settings1Icon" color="link" to="/" />
			</div>
		</div>
		
		<div class="mt-24">
			<slot></slot>
		</div>
	</div>
</template>