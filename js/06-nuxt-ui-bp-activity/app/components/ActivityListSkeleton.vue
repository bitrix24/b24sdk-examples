<script setup lang="ts">
import ActivityItemSkeleton from './ActivityItemSkeleton.vue'

withDefaults(defineProps<{
  itemsCount?: number
}>(), {
  itemsCount: 36
})
</script>

<template>
  <div class="my-3 flex flex-row items-start justify-start gap-2">
    <B24Skeleton class="h-4 w-[150px]" />
    <B24Skeleton class="h-4 w-[150px]" />
    <B24Skeleton class="h-4 w-[150px]" />
  </div>
  <B24Separator />
  <div class="mt-4  grid grid-cols-[repeat(auto-fill,minmax(266px,1fr))] gap-y-sm gap-x-sm">
    <ActivityItemSkeleton
      v-for="i in itemsCount"
      :key="i"
    />
  </div>
</template>
