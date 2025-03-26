<script setup lang="ts">
import { UserNav, Logo, NavSettings } from '#components'

const route = useRoute()

useHead({
  bodyAttrs: {
    class: 'text-base-master dark:text-base-150 bg-base-50 dark:bg-base-dark font-b24-system antialiased'
  }
})

const props = defineProps({
  navItems: {
    type: Array as PropType<Array<{
      label: string
      value: string
      active?: boolean
      onSelect?(e: Event): void
    }>>,
    default: () => []
  }
})

const navList = computed(() => (handleClick: () => void) => {
  return props.navItems.map((item) => {
    return {
      ...item,
      onSelect(e: Event) {
        e.preventDefault()
        handleClick()
        if (typeof item?.onSelect === 'function') {
          item.onSelect(e)
        }
      }
    }
  })
})
</script>

<template>
  <B24SidebarLayout
    :use-light-content="route.path !== '/activity-list'"
  >
    <template #sidebar="{ handleClick }">
      <B24SidebarHeader>
        <B24SidebarSection class="ps-[18px] flex-row  items-center justify-start gap-0.5 text-primary">
          <Logo class="size-8" />
          <ProseH4 class="mb-0 leading-none">
            {{ $t('app.title') }}
          </ProseH4>
        </B24SidebarSection>
      </B24SidebarHeader>
      <B24SidebarBody>
        <B24NavigationMenu
          :items="navList(handleClick)"
          variant="pill"
          orientation="vertical"
        />
        <B24SidebarSpacer />
        <NavSettings />
      </B24SidebarBody>
      <B24SidebarFooter>
        <UserNav />
      </B24SidebarFooter>
    </template>

    <!-- Header -->
    <div>
      <slot name="header-title">
        <!-- / header-title / -->
      </slot>
    </div>

    <!-- Content -->
    <slot />
  </B24SidebarLayout>
</template>
