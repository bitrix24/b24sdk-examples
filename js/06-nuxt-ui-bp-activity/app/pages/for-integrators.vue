<script setup lang="ts">
import { UserNav } from '#components'
import SettingsIcon from '@bitrix24/b24icons-vue/common-service/SettingsIcon'
import UserCompanyIcon from '@bitrix24/b24icons-vue/common-b24/UserCompanyIcon'
import GraphsDiagramIcon from '@bitrix24/b24icons-vue/main/GraphsDiagramIcon'
import HomeIcon from '@bitrix24/b24icons-vue/main/HomeIcon'

definePageMeta({
  layout: false
})

const navItems = [
  { title: 'Home', path: '/', icon: HomeIcon },
  { title: 'Analytics', path: '/analytics', icon: GraphsDiagramIcon },
  { title: 'Users', path: '/users', icon: UserCompanyIcon },
  { title: 'Settings', path: '/settings', icon: SettingsIcon }
]
</script>

<template>
  <NuxtLayout
    name="dashboard"
    title="Analytics Dashboard"
    :nav-items="navItems"
  >
    <!-- Page Content -->
    <div class="grid gap-6">
      <!-- ... ваш dashboard content ... -->
    </div>
  </NuxtLayout>
</template>
