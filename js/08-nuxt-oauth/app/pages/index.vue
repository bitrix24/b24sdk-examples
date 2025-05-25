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

const companiesList = ref<null | {
  loadMore: () => Promise<void>
  addCompany: (companyId: number) => Promise<void>
}>(null)

const $logger = LoggerBrowser.build('OAut.index', true)
const isInit = ref(false)
const crmListShow = ref(false)

onMounted(async () => {
  isInit.value = true
})

const makeOpenSliderForUser = () => {
  window.open(
    `${user.value?.targetOrigin}/company/personal/user/${user.value?.id}/`
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

async function addCompany(count: number = 1000) {
  if (companiesList.value) {
    await companiesList.value.addCompany(count)
  }
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
          :avatar="{ src: user?.photo || '' }"
          :title="[
            user?.lastName,
            user?.name
          ].join(' ')"
          :description="[
            user?.isAdmin ? 'Administrator' : '',
            user?.targetOrigin ?? '?'
          ].join(' ')"
          :actions="[
            {
              label: 'My profile',
              color: 'ai',
              onClick() {
                makeOpenSliderForUser()
              }
            },
            {
              label: 'Crm List',
              color: 'collab',
              onClick() {
                makeCrmListShow()
              }
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
          <div class="mx-2 mt-2 mb-5 p-4 bg-white min-h-screen rounded">
            <CompanyList ref="companiesList" />
          </div>
        </template>

        <template #footer>
          <B24Button
            rounded
            label="+100"
            color="ai"
            size="sm"
            use-clock
            loading-auto
            @click.stop="addCompany(100)"
          />
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
    <template v-else>
      <B24Button
        v-if="!loggedIn"
        color="ai"
        to="/auth"
      >
        OAuth.start
      </B24Button>
    </template>
  </div>
</template>
