<script setup lang="ts">
import type { IActivity } from '~/types'

defineProps<{
  activity: IActivity
}>()

const emit = defineEmits<{ close: [boolean] }>()
</script>

<template>
  <B24Modal
    :title="`Confirm uninstallation ${activity.title}?`"
    description="The activity will become unavailable in all business processes."
    :close="{ onClick: () => emit('close', false) }"
  >
    <template #footer>
      <div class="flex gap-2">
        <B24Button
          rounded
          label="I confirm"
          color="danger"
          size="sm"
          @click="emit('close', true)"
        />
        <B24Button
          rounded
          label="Cancel"
          color="link"
          depth="light"
          size="sm"
          @click="emit('close', false)"
        />
      </div>
    </template>
  </B24Modal>
</template>
