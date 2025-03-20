<template>
  <B24DropdownMenu
    :items="userMenuItems"
  >
    <B24Button
      color="link"
      depth="light"
      class="gap-2"
      use-dropdown
    >
      <B24Avatar
        size="xs"
        src="/avatar/assistant.png"
      />
      <span class="hidden md:inline">John Doe</span>
    </B24Button>
  </B24DropdownMenu>
</template>

<script setup lang="ts">
const userMenuItems = [
  [{
    label: 'Profile',
    click: () => console.log('Profile clicked')
  }]
]
</script>
