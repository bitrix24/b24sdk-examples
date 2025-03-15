<template>
  <B24DropdownMenu
    :items="userMenuItems"
  >
    <B24Button
      color="success"
      depth="dark"
      class="gap-2"
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
    // icon: 'i-heroicons-user-circle',
    click: () => console.log('Profile clicked')
  }],
  [{
    label: 'Sign Out',
    // icon: 'i-heroicons-arrow-left-on-rectangle',
    click: () => console.log('Sign out clicked')
  }]
]
</script>
