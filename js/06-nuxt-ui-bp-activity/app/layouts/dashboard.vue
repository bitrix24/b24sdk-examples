<script setup lang="ts">
import { watch, onMounted } from 'vue'
import { UserNav } from '#components'
import MenuIcon from '@bitrix24/b24icons-vue/main/MenuIcon'
import Cross50Icon from '@bitrix24/b24icons-vue/actions/Cross50Icon'

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
  <div class="relative isolate min-h-svh bg-white w-full flex max-lg:flex-col w-full dark:bg-base-900 lg:bg-base-50 dark:lg:bg-dark">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 max-lg:hidden">
      <nav class="flex h-full min-h-0 flex-col">
        <!-- Sidebar/Header -->
        <div class="p-4 flex flex-col border-b border-base-950/5 dark:border-white/5 [&>[data-slot=section]+[data-slot=section]]:mt-2.5">
          <h2 class="text-xl font-semibold">
            Dashboard
          </h2>
        </div>
        <!-- Sidebar/Content -->
        <div class="p-4 flex flex-1 flex-col overflow-y-auto [&>[data-slot=section]+[data-slot=section]]:mt-8">
          <!-- Sidebar/Nav Main -->
          <div data-slot="section" class="flex flex-col gap-0.5">
            <template v-for="item in navItems" :key="item.path">
              <B24Link
                :to="item.path"
                class="flex items-center rounded-lg px-4 py-2.5 text-sm hover:bg-base-200 hover:text-white"
              >
                {{ item.title }}
              </B24Link>
            </template>
          </div>

          <!-- Sidebar/Nav Second -->
          <!-- @todo max-lg:hidden -->
          <div data-slot="section" class="max-lg:hidden flex flex-col gap-0.5">
            <!-- @todo text/xs -->
            <ProseH6 class="mb-1 px-2 text-xs/6 font-medium text-base-500 dark:text-base-400">
              Upcoming Events
            </ProseH6>
            <template v-for="item in navItems" :key="item.path">
              <B24Link
                :to="item.path"
                class="flex items-center rounded-lg px-4 py-2.5 text-sm hover:bg-base-200 hover:text-white"
              >
                {{ item.title }}
              </B24Link>
            </template>
          </div>

          <!-- Sidebar/Nav Delim -->
          <div aria-hidden="true" class="mt-8 flex-1"></div>

          <!-- Sidebar/Nav Last -->
          <div data-slot="section" class="flex flex-col gap-0.5">
            <template v-for="item in navItems" :key="item.path">
              <B24Link
                :to="item.path"
                class="flex items-center rounded-lg px-4 py-2.5 text-sm hover:bg-base-200 hover:text-white"
              >
                {{ item.title }}
              </B24Link>
            </template>
          </div>
        </div>
        <!-- Sidebar/Footer -->
        <!-- @todo max-lg:hidden -->
        <div class="p-4 flex flex-col border-t border-base-950/5 dark:border-white/5 [&>[data-slot=section]+[data-slot=section]]:mt-2.5 max-lg:hidden">
          <UserNav />
        </div>
      </nav>
    </div>
    <!-- Sidebar -->
    <!-- aside
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
    </aside -->
    <!-- Header -->
    <header class="flex items-center px-4 lg:hidden">
      <div class="py-2.5">
        <B24Slideover
          side="left"
          title="Slideover with side"
          description="Some"
          :b24ui="{ content: 'max-w-64 p-2 bg-transparent dark:bg-transparent' }"
        >
          <B24Button
            color="link"
            :icon="MenuIcon"
            size="sm"
            @click="isMobileSidebarOpen = !isMobileSidebarOpen"
          />

          <template #content>
            <!-- Sidebar -->
            <div class="flex h-full flex-col rounded-lg bg-white dark:bg-base-900 ring-1 shadow-xs ring-base-950/5 dark:ring-white/10">
              <div class="-mb-3 px-4 pt-3">
                <B24ModalDialogClose>
                  <B24Button
                    :icon="Cross50Icon"
                  />
                </B24ModalDialogClose>
              </div>
              <nav class="flex h-full min-h-0 flex-col">
                <!-- Sidebar/Header -->
                <div class="p-4 flex flex-col border-b border-base-950/5 dark:border-white/5 [&>[data-slot=section]+[data-slot=section]]:mt-2.5">
                  <h2 class="text-xl font-semibold">
                    Dashboard
                  </h2>
                </div>
                <!-- Sidebar/Content -->
                <div class="p-4 flex flex-1 flex-col overflow-y-auto [&>[data-slot=section]+[data-slot=section]]:mt-8">
                  <!-- Sidebar/Nav Main -->
                  <div data-slot="section" class="flex flex-col gap-0.5">
                    <template v-for="item in navItems" :key="item.path">
                      <B24Link
                        :to="item.path"
                        class="flex items-center rounded-lg px-4 py-2.5 text-sm hover:bg-base-200 hover:text-white"
                      >
                        {{ item.title }}
                      </B24Link>
                    </template>
                  </div>

                  <!-- Sidebar/Nav Second -->
                  <!-- @todo max-lg:hidden -->
                  <div data-slot="section" class="max-lg:hidden flex flex-col gap-0.5">
                    <!-- @todo text/xs -->
                    <ProseH6 class="mb-1 px-2 text-xs/6 font-medium text-base-500 dark:text-base-400">
                      Upcoming Events
                    </ProseH6>
                    <template v-for="item in navItems" :key="item.path">
                      <B24Link
                        :to="item.path"
                        class="flex items-center rounded-lg px-4 py-2.5 text-sm hover:bg-base-200 hover:text-white"
                      >
                        {{ item.title }}
                      </B24Link>
                    </template>
                  </div>
                  <!-- Sidebar/Nav Delim -->
                  <div aria-hidden="true" class="mt-8 flex-1"></div>

                  <!-- Sidebar/Nav Last -->
                  <div data-slot="section" class="flex flex-col gap-0.5">
                    <template v-for="item in navItems" :key="item.path">
                      <B24Link
                        :to="item.path"
                        class="flex items-center rounded-lg px-4 py-2.5 text-sm hover:bg-base-200 hover:text-white"
                      >
                        {{ item.title }}
                      </B24Link>
                    </template>
                  </div>
                </div>
              </nav>
            </div>
          </template>
        </B24Slideover>
      </div>
      <div class="min-w-0 flex-1">
        <nav class="flex flex-1 items-center gap-4 py-2.5">
          <!-- @todo use normal html -->
          <div class="flex items-center gap-4">
            <slot name="header-left">
              <span class="text-xl font-semibold">
                {{ title }}
              </span>
            </slot>
          </div>
          <div aria-hidden="true" class="-ml-4 flex-1"></div>
          <div class="flex items-center gap-3">
            <slot name="header-right">
              <UserNav />
            </slot>
          </div>
        </nav>
      </div>
    </header>

    <!-- Page Content -->
    <main class="flex flex-1 flex-col pb-2 lg:min-w-0 lg:pt-2 lg:pr-2 lg:pl-64">
      <!-- @todo fix color -->
      <div class="grow p-6 lg:rounded-lg lg:bg-white dark:lg:bg-zinc-900 lg:p-10 lg:ring-1 lg:shadow-xs lg:ring-base-950/5 dark:lg:ring-white/10">
        <!-- @todo use container -->
        <div class="mx-auto max-w-[72rem]">
          <slot />
        </div>
      </div>
    </main>
  </div>
</template>
