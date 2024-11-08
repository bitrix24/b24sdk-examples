<script setup lang="ts">
import { type Ref, ref, type ComputedRef, computed } from 'vue'
import WheelIcon from '@bitrix24/b24icons-vue/common-service/WheelIcon'
import BtnClockIcon from '@bitrix24/b24icons-vue/button-specialized/BtnClockIcon'
import CheckIcon from '@bitrix24/b24icons-vue/main/CheckIcon'

definePageMeta({
	layout: "slider",
})

// region Action ////
const processStatus: Ref<{ save: boolean, apply: boolean }> = ref({
	save: false,
	apply: false,
})

const isProcess: ComputedRef<boolean> = computed((): boolean =>
{
	return processStatus.value.save || processStatus.value.apply
})

const makeSave = async (): Promise<void> =>
{
	processStatus.value.save = true
	
	setTimeout(() =>
		{
			processStatus.value.save = false
		},
		2_000
	)
}

const makeApply = async (): Promise<void> =>
{
	processStatus.value.apply = true
	
	setTimeout(() =>
		{
			processStatus.value.apply = false
		},
		2_000
	)
}

const makeClosePage = async (): Promise<void> =>
{
	alert('[Cancel].click')
}
// endregion ////

interface Item {
	code: string,
	icon: string,
	title: string,
	description: string,
	isActive: boolean,
}

interface Group {
	code: string,
	title: string,
	items: Item[]
}

function getElements(
	groupIndex: number,
	array: Item[],
	count: number
): Item[]
{
	return JSON.parse(JSON.stringify(array)).slice(0, count).map(item => {
		item.code = `group-${groupIndex}-${item.code}`
		return item
	})
}

const listAllItems: Item[] = [
	{
		code: 'item-1',
		icon: 'contacts',
		title: 'Irure duis magna in sunt',
		description: 'Nisi nostrud excepteur exercitation irure dolore enim reprehenderit commodo',
		isActive: false,
	},
	{
		code: 'item-2',
		icon: 'contacts',
		title: 'Cupidatat ut sit ea eu',
		description: 'Lorem ipsum dolor sit amet sed labore fugiat ut excepteur amet incididunt',
		isActive: false,
	},
	{
		code: 'item-3',
		icon: 'contacts',
		title: 'Consequat aliquip velit anim non',
		description: 'Reprehenderit fugiat ut laboris consequat mollit. Est aute dolore aliqua ut lorem eu aliqua',
		isActive: true,
	},
	{
		code: 'item-4',
		icon: 'contacts',
		title: 'Ut ut veniam id dolor',
		description: 'Nostrud deserunt sed sed sed. Aliqua mollit laboris anim laborum mollit',
		isActive: false,
	},
	{
		code: 'item-5',
		icon: 'contacts',
		title: 'Tempor veniam elit nostrud voluptate',
		description: 'Do culpa lorem consequat ullamco. Duis sunt ut proident eiusmod incididunt tempor enim id officia voluptate ea',
		isActive: true,
	},
]

let groupIndex = 0;
const groups: Ref<Group[]> = ref([
	{
		code: `group-${++groupIndex}`,
		title: 'Lorem ipsum dolor sit amet in anim dolor nulla excepteur elit eu.',
		items: getElements(groupIndex, listAllItems, Math.min(4, listAllItems.length))
	} as Group,
	{
		code: `group-${++groupIndex}`,
		title: 'Lorem ipsum dolor sit amet labore',
		items: getElements(groupIndex, listAllItems, Math.min(5, listAllItems.length))
	} as Group,
	{
		code: `group-${++groupIndex}`,
		title: 'Commodo aliquip reprehenderit adipiscing',
		items: getElements(groupIndex, listAllItems, Math.min(2, listAllItems.length))
	} as Group,
	{
		code: `group-${++groupIndex}`,
		title: 'Occaecat laboris laborum ut',
		items: getElements(groupIndex, listAllItems, Math.min(4, listAllItems.length))
	} as Group,
	{
		code: `group-${++groupIndex}`,
		title: 'Commodo aliquip laboris laborum ut',
		items: getElements(groupIndex, listAllItems, Math.min(3, listAllItems.length))
	} as Group,
	{
		code: `group-${++groupIndex}`,
		title: 'Ipsum dolor',
		items: getElements(groupIndex, listAllItems, Math.min(1, listAllItems.length))
	} as Group
])

</script>

<template>
	<div class="px-lg mb-xs2">
		<div
			class="min-h-7xl h-7xl w-full flex flex-row items-center justify-normal gap-lg2 border-b border-b-base-900/0.1"
		>
			<div class="pl-xs2 text-4xl font-light font-b24-secondary text-base-master">Page title1</div>
			<div class="grow flex flex-row items-end justify-end text-lg">
				<div class="grow font-b24-primary">
					<div class="flex flex-row gap-2xs items-end">
						<WheelIcon class="size-xl2 text-info-text"/>
						<div class="flex flex-col">
							<div class="text-3xs leading-tight opacity-70 text-base-500 items-center">use case</div>
							<div
								class="text-sm text-primary-link cursor-pointer transition-opacity hover:opacity-80 border-b border-dashed border-b-primary-link"
								onclick="alert('[Some action].click')"
							>Some action</div>
						</div>
					</div>
				</div>
				<div class="text-md font-light text-base-900">
					<a
						class="hover:text-primary-link hover:underline underline-offset-2 transition-opacity hover:opacity-80"
						href="https://bitrix24.github.io/b24style/reference/colors.html"
						target="_blank"
					>@bitrix24/b24style</a>
				</div>
			</div>
		</div>
	</div>
	<div class="px-lg my-sm overflow-auto flex flex-col h-full-and-footer">
		<div
			v-for="(group) in groups"
			:key="group.code"
			class="mb-md"
		>
			<div class="mb-sm font-b24-secondary text-h4 font-light leading-8 text-base-900">{{ group.title }}</div>
			<div class="grid grid-cols-[repeat(auto-fill,minmax(266px,1fr))] gap-y-sm gap-x-xs">
				<div
					v-for="(item) in group.items"
					:key="item.code"
					class="bg-white py-sm2 px-xs2 cursor-pointer rounded-md flex flex-row gap-sm border-2 transition-shadow shadow hover:shadow-lg relative"
					:class="item.isActive ? 'border-success-text' : 'border-white hover:border-primary'"
				>
					<div
						v-if="item.isActive"
						class="absolute -top-2 -right-2 rounded-full bg-success-text size-5 text-success-on flex items-center justify-center"
					>
						<CheckIcon class="size-md" />
					</div>
					<div class="rounded-full bg-blue-200 size-14 min-w-14 min-h-14 flex items-center justify-center">
						<component
							:is="WheelIcon"
							class="size-12 text-info-text"
						></component>
					</div>
					<div class="max-w-11/12">
						<div
							class="font-b24-secondary text-black text-h6 leading-4 mb-xs font-semibold line-clamp-2"
							:title="item.title"
						>{{ item.title }}</div>
						<div
							class="font-b24-primary text-sm text-base-500 line-clamp-2"
							:title="item.description"
						>
							<div>{{ item.description }}</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="w-full h-7xl shadow-top-sm fixed insert-x-0 bottom-0 p-3 bg-white">
		<div class="flex flex-row flex-nowrap items-center justify-between gap-1">
			<div class="w-1/4 flex flex-row justify-start gap-4">&nbsp;</div>
			<div class="w-2/4 flex flex-row justify-center gap-4">
				<button
					@click="makeSave"
					:disabled="isProcess"
					class="min-w-[90px] flex flex-row items-center justify-center text-xs text-center font-semibold text-base-900 uppercase bg-green px-6 rounded hover:bg-green-400 active:bg-green-600 disabled:cursor-not-allowed disabled:bg-green-600 disabled:opacity-50"
				>
					<BtnClockIcon class="w-lg h-lg" v-if="processStatus.save"/>
					<span v-else>Save</span>
				</button>
				
				<button
					@click="makeApply"
					:disabled="isProcess"
					class="min-w-[90px] flex flex-row items-center justify-center text-xs font-semibold text-white uppercase bg-blue px-6 rounded hover:bg-blue-400 active:bg-blue-600 disabled:cursor-not-allowed disabled:bg-blue-600 disabled:opacity-50"
				>
					<BtnClockIcon class="w-lg h-lg" v-if="processStatus.apply && !processStatus.save"/>
					<span v-else>Apply</span>
				</button>
				
				<button
					@click="makeClosePage"
					:disabled="isProcess"
					class="text-xs text-center font-semibold text-base-900 uppercase px-2 py-3 hover:text-base-800 active:text-base-master disabled:cursor-not-allowed disabled:text-base-master disabled:opacity-50"
				>Cancel
				</button>
			</div>
			<div class="w-1/4 flex flex-row justify-end gap-1">
				<div class="text-base-400 text-xs">Updated 8 minutes ago</div>
			</div>
		</div>
	</div>
</template>