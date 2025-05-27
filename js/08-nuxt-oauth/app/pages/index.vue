<script setup lang="ts">
import { ref } from 'vue'
import { LoggerBrowser } from '@bitrix24/b24jssdk'

useHead({
  title: 'Bitrix24 UI - OAut'
})

definePageMeta({
  middleware: ['auth']
})

const { loggedIn, user, session, clear: clearSession } = useUserSession()

const $logger = LoggerBrowser.build('OAut.index', true)
const isInit = ref(false)
const crmListShow = ref(false)

onMounted(async () => {
  isInit.value = true
})

const makeOpenSliderForUser = () => {
  window.open(
    `${user.value?.bitrix24?.targetOrigin}/company/personal/user/${user.value?.bitrix24?.id}/`
  )

  return Promise.resolve()
}

const makeCrmListShow = () => {
  crmListShow.value = true
}

async function logout() {
  isInit.value = false
  await clearSession()
  await navigateTo('/auth')
}

onMounted(() => {
  $logger.info({
    user: user.value,
    session: session.value
  })
})
</script>

<template>
  <div class="w-full h-svh overflow-hidden flex flex-col items-center justify-center gap-3">
    <template v-if="!isInit">
      <h1 class="font-b24-secondary text-h1 sm:text-8xl font-light">
        Loading ...
      </h1>
    </template>
    <template v-else-if="loggedIn">
      <div class="shrink-0 flex flex-col items-end justify-center gap-3">
        <B24Alert
          class="shrink-0 rounded-lg bg-white pr-4"
          orientation="horizontal"
          :avatar="{ src: user?.bitrix24?.photo || 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0MiA0MiIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0ic2l6ZS0yNCB0ZXh0LWJhc2UtbWFzdGVyIGRhcms6dGV4dC1iYXNlLTIwMCI+PHBhdGggZmlsbD0iY3VycmVudENvbG9yIiBkPSJNMjIuMDkgMTcuOTI2aC0xLjM4NnYzLjcxNmgzLjU1MXYtMS4zODZIMjIuMDl6bS0uNjE2IDcuMzU2YTQuNzE4IDQuNzE4IDAgMSAxIDAtOS40MzYgNC43MTggNC43MTggMCAwIDEgMCA5LjQzNm05LjE5NS02QTUuMTkgNS4xOSAwIDAgMCAyMy43MjEgMTRhNS4xOSA1LjE5IDAgMCAwLTkuODcyIDEuNjlBNi4yMzQgNi4yMzQgMCAwIDAgMTUuMjMzIDI4aDE0Ljc2MWMyLjQ0NCAwIDQuNDI1LTEuNzI0IDQuNDI1LTQuNDI1IDAtMy40OTctMy40MDYtNC4zNzktMy43NS00LjI5MyIvPjwvc3ZnPg==' }"
          :title="user?.bitrix24?.name"
          :actions="[
            {
              label: 'Profile',
              color: 'ai',
              onClick: makeOpenSliderForUser
            }
          ]"
        >
          <template #description>
            <ul class="text-sm">
              <li>{{ user?.bitrix24?.isAdmin ? 'Administrator' : '' }}</li>
              <li>{{ user?.bitrix24?.targetOrigin ?? '?' }}</li>
            </ul>
          </template>
        </B24Alert>
        <div class="w-full px-4 flex flex-row items-center justify-between gap-4">
          <B24Button
            size="xs"
            label="Logout"
            color="link"
            @click.stop="logout"
          />
          <B24Button
            size="xs"
            label="Company List"
            color="collab"
            @click.stop="makeCrmListShow"
          />
        </div>
      </div>
      <B24Slideover
        v-model:open="crmListShow"
        title="Company list"
        description="get from server side"
        class="w-full md:w-1/3 max-w-[calc(100%_-_45px)]"
      >
        <template #body>
          <div class="mx-2">
            <CompanyList />
          </div>
        </template>
      </B24Slideover>
    </template>
  </div>
</template>
