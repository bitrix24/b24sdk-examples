<script setup lang="ts">
import SettingsIcon from '@bitrix24/b24icons-vue/common-service/SettingsIcon'
import UserCompanyIcon from '@bitrix24/b24icons-vue/common-b24/UserCompanyIcon'
import GraphsDiagramIcon from '@bitrix24/b24icons-vue/main/GraphsDiagramIcon'
import HomeIcon from '@bitrix24/b24icons-vue/main/HomeIcon'

definePageMeta({
  layout: false
})

const navItems = [
  { title: 'Home', path: '/', icon: HomeIcon },
  { title: 'Analytics', path: '/', icon: GraphsDiagramIcon },
  { title: 'Users', path: '/', icon: UserCompanyIcon },
  { title: 'Settings', path: '/', icon: SettingsIcon }
]
</script>

<template>
  <NuxtLayout
    name="dashboard"
    title="Analytics Dashboard"
    :nav-items="navItems"
  >
    <!-- Page Content -->
    <ProseH1>dashboard content</ProseH1>
  </NuxtLayout>
</template>
