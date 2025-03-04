<script setup lang="ts">
import type { IActivity } from '~/types'

const props = defineProps<{
  activity: IActivity
}>()

const emit = defineEmits<{ close: [boolean] }>()

const { data: content } = await useAsyncData(props.activity.path, () => {
  return queryCollection('contentActivities').path(props.activity.path).first()
})
</script>

<template>
  <B24Slideover
    :title="activity.title"
    :description="activity.description"
    :close="{ onClick: () => emit('close', false) }"
    :b24ui="{
      overlay: 'backdrop-blur-xs',
      content: 'sm:max-w-1/3',
      body: 'm-5 p-5 bg-white dark:bg-white/10 rounded'
    }"
  >
    <template #body>
      <template v-if="!content">
        {{ '@todo make this' }}
      </template>
      <ContentRenderer v-else :value="content" />
    </template>

    <template #footer>
      <div class="flex gap-2">
        <B24Button
          rounded
          label="Close"
          color="link"
          depth="dark"
          @click="emit('close', false)"
        />
      </div>
      <B24Badge
        class="absolute left-5"
        v-if="activity.isInstall"
        size="lg"
        color="collab"
        use-fill
        label="Installed"
      />
    </template>
  </B24Slideover>
</template>
