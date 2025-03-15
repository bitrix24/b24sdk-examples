<script setup lang="ts">
import { watch, onMounted } from 'vue'
import { UserNav } from '#components'
import MenuIcon from '@bitrix24/b24icons-vue/main/MenuIcon'

defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  },
  navItems: {
    type: Array as PropType<Array<{
      title: string
      path: string
      icon: any
    }>>,
    default: () => []
  }
})

const isMobileSidebarOpen = ref(false)
const sidebarRef = ref<HTMLElement>()
const bodyClassList = ref(new Set([
  'text-base-master', 'dark:text-base-150', 'bg-base-50', 'dark:bg-base-dark', 'font-b24-system', 'antialiased'
]))
const htmlClassList = ref(new Set())

useHead({
  htmlAttrs: {
    class: computed(() => Array.from(htmlClassList.value).join(' '))
  },
  bodyAttrs: {
    class: computed(() => Array.from(bodyClassList.value).join(' '))
  }
})
// Close sidebar if resize
onMounted(() => {
  const handleResize = () => {
    if (window.innerWidth >= 1024) {
      isMobileSidebarOpen.value = false
    }
  }

  window.addEventListener('resize', handleResize)
  return () => window.removeEventListener('resize', handleResize)
})

watch(isMobileSidebarOpen, (newSlidebarState) => {
  if (newSlidebarState) {
    htmlClassList.value.add('overflow-hidden')
    htmlClassList.value.add('pe-0')
  } else {
    htmlClassList.value.delete('overflow-hidden')
    htmlClassList.value.delete('pe-0')
  }
})
</script>

<template>
  <div
    data-tt="@todo"
    class="relative min-h-screen bg-background"
    data-new-class="relative isolate flex min-h-svh w-full bg-white max-lg:flex-col lg:bg-base-100 dark:bg-base-900 dark:lg:bg-base-950"
  >
    <!-- Mobile Sidebar Overlay -->
    <div
      v-if="isMobileSidebarOpen"
      class="fixed inset-0 z-4 bg-black/50 lg:hidden"
      @click="isMobileSidebarOpen = false"
    />

    <!-- Sidebar -->
    <aside
      ref="sidebarRef"
      data-clas1="@todo max-lg:hidden"
      data-clas2="left-0 top-0      h-screen border-r shadow-xl transform transition-transform lg:translate-x-0"
      data-clas3="     p-2                                                            data-closed:-translate-x-full"
      class="fixed z-5 inset-y-0 max-w-64 w-full transition duration-300 ease-in-out  lg:translate-x-0"
      :class="{
        '-translate-x-full w-64 max-lg:hidden': !isMobileSidebarOpen,
        'translate-x-0 p-2 ': isMobileSidebarOpen
      }"
    >
      <div
        class="flex h-full flex-col min-h-0"
        :class="{
          '': !isMobileSidebarOpen,
          'rounded-lg bg-white ring-1 shadow-xs ring-base-950/5 dark:bg-base-900 dark:ring-white/10': isMobileSidebarOpen
        }"
      >
        <div
          v-if="isMobileSidebarOpen"
          class="-mb-3 px-4 pt-3 lg:hidden"
        >
          <B24Button
            label="close"
            color="link"
            @click.stop="isMobileSidebarOpen = false"
          />
        </div>
        <div class="flex h-full min-h-0 flex-col">
          <div class="flex flex-col border-b border-red-950/5 p-4 dark:border-white/5 [&>[data-slot=section]+[data-slot=section]]:mt-2.5">
            <h2 class="text-xl font-semibold">
              Dashboard
            </h2>
          </div>
          <div class="flex flex-1 flex-col overflow-y-auto p-4 [&>[data-slot=section]+[data-slot=section]]:mt-8">
            <nav class="flex flex-col gap-0.5">
              <slot name="sidebar-content">
                <!-- Default sidebar content -->
                <ul class="space-y-2">
                  <li v-for="item in navItems" :key="item.path">
                    <B24Link
                      :to="item.path"
                      class="flex items-center rounded-lg px-4 py-2.5 text-sm hover:bg-base-200 hover:text-white"
                    >
                      {{ item.title }}
                    </B24Link>
                  </li>
                </ul>
              </slot>
            </nav>
            <div aria-hidden="true" class="mt-8 flex-1" />
            <div data-slot="section" class="flex flex-col gap-0.5">
              <span class="relative"><a class="flex w-full items-center gap-3 rounded-lg px-2 py-2.5 text-left text-base/6 font-medium text-base-950 sm:py-2 sm:text-sm/5 *:data-[slot=icon]:size-6 *:data-[slot=icon]:shrink-0 *:data-[slot=icon]:fill-base-500 sm:*:data-[slot=icon]:size-5 *:last:data-[slot=icon]:ml-auto *:last:data-[slot=icon]:size-5 sm:*:last:data-[slot=icon]:size-4 *:data-[slot=avatar]:-m-0.5 *:data-[slot=avatar]:size-7 *:data-[slot=avatar]:[--ring-opacity:10%] sm:*:data-[slot=avatar]:size-6 hover:bg-base-950/5 hover:*:data-[slot=icon]:fill-base-950 data-active:bg-base-950/5 data-active:*:data-[slot=icon]:fill-base-950 data-current:*:data-[slot=icon]:fill-base-950 dark:text-white dark:*:data-[slot=icon]:fill-base-400 dark:hover:bg-white/5 dark:hover:*:data-[slot=icon]:fill-white dark:data-active:bg-white/5 dark:data-active:*:data-[slot=icon]:fill-white dark:data-current:*:data-[slot=icon]:fill-white" type="button" data-headlessui-state="" href="#"><span class="absolute top-1/2 left-1/2 size-[max(100%,2.75rem)] -translate-x-1/2 -translate-y-1/2 [@media(pointer:fine)]:hidden" aria-hidden="true" /><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0ZM8.94 6.94a.75.75 0 1 1-1.061-1.061 3 3 0 1 1 2.871 5.026v.345a.75.75 0 0 1-1.5 0v-.5c0-.72.57-1.172 1.081-1.287A1.5 1.5 0 1 0 8.94 6.94ZM10 15a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg><span class="truncate">Support</span></a></span><span class="relative"><a class="flex w-full items-center gap-3 rounded-lg px-2 py-2.5 text-left text-base/6 font-medium text-base-950 sm:py-2 sm:text-sm/5 *:data-[slot=icon]:size-6 *:data-[slot=icon]:shrink-0 *:data-[slot=icon]:fill-base-500 sm:*:data-[slot=icon]:size-5 *:last:data-[slot=icon]:ml-auto *:last:data-[slot=icon]:size-5 sm:*:last:data-[slot=icon]:size-4 *:data-[slot=avatar]:-m-0.5 *:data-[slot=avatar]:size-7 *:data-[slot=avatar]:[--ring-opacity:10%] sm:*:data-[slot=avatar]:size-6 hover:bg-base-950/5 hover:*:data-[slot=icon]:fill-base-950 data-active:bg-base-950/5 data-active:*:data-[slot=icon]:fill-base-950 data-current:*:data-[slot=icon]:fill-base-950 dark:text-white dark:*:data-[slot=icon]:fill-base-400 dark:hover:bg-white/5 dark:hover:*:data-[slot=icon]:fill-white dark:data-active:bg-white/5 dark:data-active:*:data-[slot=icon]:fill-white dark:data-current:*:data-[slot=icon]:fill-white" type="button" data-headlessui-state="" href="#"><span class="absolute top-1/2 left-1/2 size-[max(100%,2.75rem)] -translate-x-1/2 -translate-y-1/2 [@media(pointer:fine)]:hidden" aria-hidden="true" /><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon"><path d="M15.98 1.804a1 1 0 0 0-1.96 0l-.24 1.192a1 1 0 0 1-.784.785l-1.192.238a1 1 0 0 0 0 1.962l1.192.238a1 1 0 0 1 .785.785l.238 1.192a1 1 0 0 0 1.962 0l.238-1.192a1 1 0 0 1 .785-.785l1.192-.238a1 1 0 0 0 0-1.962l-1.192-.238a1 1 0 0 1-.785-.785l-.238-1.192ZM6.949 5.684a1 1 0 0 0-1.898 0l-.683 2.051a1 1 0 0 1-.633.633l-2.051.683a1 1 0 0 0 0 1.898l2.051.684a1 1 0 0 1 .633.632l.683 2.051a1 1 0 0 0 1.898 0l.683-2.051a1 1 0 0 1 .633-.633l2.051-.683a1 1 0 0 0 0-1.898l-2.051-.683a1 1 0 0 1-.633-.633L6.95 5.684ZM13.949 13.684a1 1 0 0 0-1.898 0l-.184.551a1 1 0 0 1-.632.633l-.551.183a1 1 0 0 0 0 1.898l.551.183a1 1 0 0 1 .633.633l.183.551a1 1 0 0 0 1.898 0l.184-.551a1 1 0 0 1 .632-.633l.551-.183a1 1 0 0 0 0-1.898l-.551-.184a1 1 0 0 1-.633-.632l-.183-.551Z" /></svg><span class="truncate">Changelog</span></a></span>
            </div>
          </div>
          <div class="max-lg:hidden flex flex-col border-t border-base-950/5 p-4 dark:border-white/5 [&>[data-slot=section]+[data-slot=section]]:mt-2.5">
            <UserNav />
          </div>
        </div>
      </div>
    </aside>
    <!-- Main Content -->
    <div class="lg:pl-64">
      <!-- Header -->
      <header class="sticky top-0 z-2 border-b border-red-950/5 p-4 dark:border-white/5 bg-white/80 backdrop-blur">
        <div class="flex items-center justify-between px-6">
          <div class="flex items-center gap-4">
            <button
              class="lg:hidden"
              @click="isMobileSidebarOpen = !isMobileSidebarOpen"
            >
              <MenuIcon class="h-6 w-6" />
            </button>
            <slot name="header-left">
              <h1 class="text-xl font-semibold">
                {{ title }}
              </h1>
            </slot>
          </div>

          <div class="flex items-center gap-4">
            <slot name="header-right">
              <div class="lg:hidden">
                <UserNav />
              </div>
            </slot>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="min-h-[calc(100vh-4rem)] p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
