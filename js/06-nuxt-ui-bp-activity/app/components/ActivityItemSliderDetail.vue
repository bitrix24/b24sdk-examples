<script setup lang="ts">
import type { IActivity } from '~/types'

const props = defineProps<{
  activity: IActivity
}>()

const emit = defineEmits<{ close: [boolean] }>()

// region Locale ////
const { locale } = useI18n()
const contentCollection = computed(() => `contentActivities_${locale.value}`)
// endregion ////

const { data: content } = await useAsyncData(props.activity.path, () => {
  return queryCollection(contentCollection.value).path(props.activity.path).first()
})
</script>

<template>
  <B24Slideover
    :title="activity.title"
    :description="activity.description"
    :close="{ onClick: () => emit('close', false) }"
    :b24ui="{
      content: 'sm:max-w-2/4 md:max-w-2/3 lg:max-w-1/3',
      body: 'm-5 p-5 bg-white dark:bg-white/10 rounded'
    }"
  >
    <template #body>
      <B24Advice
        v-if="!content"
        class="min-w-full"
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        <p>An error has been identified in generating activity data.</p>
        <p>Please contact the app’s technical support team — our specialists will resolve the issue promptly.</p>
        <div class="mt-2 flex flex-row flex-wrap items-start justify-between gap-2">
          <B24Button size="xs" color="primary" label="@todo" />
        </div>
      </B24Advice>
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
        v-if="activity.isInstall"
        class="absolute right-5"
        size="lg"
        color="collab"
        depth="light"
        use-fill
        label="Installed"
      />
    </template>
  </B24Slideover>
</template>
