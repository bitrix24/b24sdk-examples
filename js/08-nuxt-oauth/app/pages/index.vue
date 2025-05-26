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
  <div class="h-screen flex flex-col items-center justify-center gap-3">
    <template v-if="!isInit">
      <h1 class="font-b24-secondary text-h1 sm:text-8xl font-light">
        Loading ...
      </h1>
    </template>
    <template v-else-if="loggedIn">
      <div class="flex flex-col items-end justify-center gap-3">
        <B24Alert
          class="w-[600px] rounded-lg bg-white pr-4"
          orientation="horizontal"
          :avatar="{ src: user?.bitrix24?.photo || 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0MiA0MiIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0ic2l6ZS0yNCB0ZXh0LWJhc2UtbWFzdGVyIGRhcms6dGV4dC1iYXNlLTIwMCI+PHBhdGggZmlsbD0iY3VycmVudENvbG9yIiBkPSJNMjIuMDkgMTcuOTI2aC0xLjM4NnYzLjcxNmgzLjU1MXYtMS4zODZIMjIuMDl6bS0uNjE2IDcuMzU2YTQuNzE4IDQuNzE4IDAgMSAxIDAtOS40MzYgNC43MTggNC43MTggMCAwIDEgMCA5LjQzNm05LjE5NS02QTUuMTkgNS4xOSAwIDAgMCAyMy43MjEgMTRhNS4xOSA1LjE5IDAgMCAwLTkuODcyIDEuNjlBNi4yMzQgNi4yMzQgMCAwIDAgMTUuMjMzIDI4aDE0Ljc2MWMyLjQ0NCAwIDQuNDI1LTEuNzI0IDQuNDI1LTQuNDI1IDAtMy40OTctMy40MDYtNC4zNzktMy43NS00LjI5MyIvPjwvc3ZnPg==' }"
          :title="user?.bitrix24?.name"
          :description="[
            user?.bitrix24?.isAdmin ? 'Administrator' : '',
            user?.bitrix24?.targetOrigin ?? '?'
          ].join(' ')"
          :actions="[
            {
              label: 'My profile',
              color: 'ai',
              onClick: makeOpenSliderForUser
            },
            {
              label: 'Crm List',
              color: 'collab',
              onClick: makeCrmListShow
            }
          ]"
        />
        <div class="pr-4">
          <B24Button
            size="xs"
            label="Logout"
            color="default"
            @click.stop="logout"
          />
        </div>
      </div>
      <B24Slideover
        v-model:open="crmListShow"
        title="Company list"
        description="get from server side"
      >
        <template #body>
          <div class="mx-2 mt-2 mb-5 p-4 bg-white min-h-[calc(100vh_-_160px)] rounded">
            <CompanyList />
          </div>
        </template>

        <template #footer>
          <B24Button
            rounded
            label="Close"
            color="link"
            depth="dark"
            size="sm"
            @click="crmListShow = false"
          />
        </template>
      </B24Slideover>
    </template>
  </div>
</template>
