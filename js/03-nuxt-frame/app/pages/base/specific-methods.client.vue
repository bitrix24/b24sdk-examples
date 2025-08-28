<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue'
import type { AccordionItem } from '@bitrix24/b24ui-nuxt'
import type { B24Frame, SelectedUser, TypePullMessage} from '@bitrix24/b24jssdk'
import TrashBinIcon from "@bitrix24/b24icons-vue/main/TrashBinIcon"
import Refresh7Icon from '@bitrix24/b24icons-vue/actions/Refresh7Icon'
import UserIcon from '@bitrix24/b24icons-vue/common-b24/UserIcon'
import VideoAndChatIcon from '@bitrix24/b24icons-vue/main/VideoAndChatIcon'
import CallChatIcon from '@bitrix24/b24icons-vue/main/CallChatIcon'
import MessengerIcon from '@bitrix24/b24icons-vue/social/MessengerIcon'
import DialogueIcon from '@bitrix24/b24icons-vue/crm/DialogueIcon'
import TelephonyHandset6Icon from '@bitrix24/b24icons-vue/main/TelephonyHandset6Icon'
import PulseIcon from '@bitrix24/b24icons-vue/main/PulseIcon'

definePageMeta({
  layout: false
})

const { t } = useI18n()
const route = useRoute()
route.meta.pageTitle = t('page.base_specific-methods.seo.title')
route.meta.pageDescription = t('page.base_specific-methods.seo.description')

// region Init ////
const { $logger, processErrorGlobal, reloadData, b24Helper, getRootSideBarApi, usePullClient, useSubscribePullClient, startPullClient } = useAppInit('SpecificMethodsPage')
const { $initializeB24Frame } = useNuxtApp()
const $b24: B24Frame = await $initializeB24Frame()
$logger.info('Hi from base/specific-methods')

const result = ref<Record<string, any>>({})

const isPullClientInit = ref(false)

watch(b24Helper, (newValue) => {
  if (newValue) {
    $logger.info('b24Helper ready')
    initPullClient()
  }
})

function initPullClient() {
  if (isPullClientInit.value) {
    return
  }

  isPullClientInit.value = true
  usePullClient()
  useSubscribePullClient(
    // @ts-expect-error Everything is fine
    makeSendPullCommandHandler.bind( this ),
    'main'
  )
  startPullClient()
}
// endregion ////

// const active = ref('0')

const infoItems = computed(() => [
  {
    label: t('page.base_specific-methods.message.selectUsers'),
    slot: 'selectUsers',
    icon: UserIcon,
    description: t('page.base_specific-methods.message.selectUsersDescription')
  },
  {
    label: t('page.base_specific-methods.message.makeImCallToVideo'),
    slot: 'makeImCallToVideo',
    icon: VideoAndChatIcon,
    description: t('page.base_specific-methods.message.makeImCallToVideoDescription')
  },
  {
    label: t('page.base_specific-methods.message.makeImCallToVoice'),
    slot: 'makeImCallToVoice',
    icon: CallChatIcon,
    description: t('page.base_specific-methods.message.makeImCallToVoiceDescription')
  },
  {
    label: t('page.base_specific-methods.message.makeImOpenMessenger'),
    slot: 'makeImOpenMessenger',
    icon: MessengerIcon,
    description: t('page.base_specific-methods.message.makeImOpenMessengerDescription')
  },
  {
    label: t('page.base_specific-methods.message.makeImOpenHistory'),
    slot: 'makeImOpenHistory',
    icon: DialogueIcon,
    description: t('page.base_specific-methods.message.makeImOpenHistoryDescription')
  },
  {
    label: t('page.base_specific-methods.message.makeImPhoneTo'),
    slot: 'makeImPhoneTo',
    icon: TelephonyHandset6Icon,
    description: t('page.base_specific-methods.message.makeImPhoneToDescription')
  },
  {
    label: t('page.base_specific-methods.message.makeSendPullCommand'),
    slot: 'makeSendPullCommand',
    icon: PulseIcon,
    description: t('page.base_specific-methods.message.makeSendPullCommandDescription')
  }
] satisfies AccordionItem[] )

// region Actions ////
const makeReloadWindow = async() => {
  try {
    result.value = {
      message: 'start reload'
    }

    await $b24.parent.reloadWindow()

    $logger.info(result.value)
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/base/specific-methods'
    })
  }
}

const makeSelectUsers = async() => {
  try {
    result.value = {}

    result.value.selectedUsers = await $b24.dialog.selectUsers()

    result.value.list = result.value.selectedUsers.map((row: SelectedUser): string =>
    {
      return [
        `[id: ${row.id}]`,
        row.name,
      ].join(' ')
    })

    if(result.value.list.length < 1)
    {
      result.value.list.push('~ empty ~')
    }

    $logger.info(result.value)

  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/base/specific-methods'
    })
  }
}

const makeImCallTo = async(isVideo: boolean = true) => {
  try {
    result.value = {}

    result.value.selectedUser = await $b24.dialog.selectUser()

    if (!result.value.selectedUser) {
      result.value.error = new Error(t('page.base_specific-methods.error.notSelect'))
      return
    }

    if(Number(result.value.selectedUser.id) === (b24Helper.value?.profileInfo.data.id || 0))
    {
      result.value.error = new Error(t('page.base_specific-methods.error.tryCallToSelf'))
      return
    }

    await $b24.parent.imCallTo(
      Number(result.value.selectedUser.id),
      isVideo
    )

    $logger.info(result.value)
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/base/specific-methods'
    })
  }
}

const makeImOpenMessenger = async() => {
  try {
    result.value = {}

    result.value.promptDialogId = prompt(
      'Please provide dialogId (number|`chat${number}`|`sg${number}`|`imol|${number}`|undefined)'
    )

    if (null === result.value.promptDialogId) {
      return
    }

    result.value.dialogId = String(result.value.promptDialogId)

    if (result.value.dialogId.length < 1) {
      result.value.dialogId = undefined
    } else if(
      !result.value.dialogId.startsWith('chat')
      && !result.value.dialogId.startsWith('imol')
      && !result.value.dialogId.startsWith('sg')
    ) {
      result.value.dialogId = Number(result.value.dialogId)
    }

    await $b24.parent.imOpenMessenger( result.value.dialogId )

    $logger.info(result.value)
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/base/specific-methods'
    })
  }
}

const makeImOpenHistory = async() => {
  try {
    result.value = {}

    result.value.promptDialogId = prompt(
      'Please provide dialogId (number|`chat${number}`|`imol|${number})'
    )

    if (null === result.value.promptDialogId) {
      return
    }

    result.value.dialogId = String(result.value.promptDialogId)

    if (
      !result.value.dialogId.startsWith('chat')
      && !result.value.dialogId.startsWith('imol')
    ) {
      result.value.dialogId = Number(result.value.dialogId)
    }

    await $b24.parent.imOpenHistory( result.value.dialogId )

    $logger.info(result.value)
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/base/specific-methods'
    })
  }
}

const makeImPhoneTo = async() => {
  try {
    result.value = {}

    result.value.promptPhone = prompt(
      'Please provide phone'
    )

    if (null === result.value.promptPhone) {
      return
    }

    result.value.phone = String(result.value.promptPhone)

    if(result.value.phone.length < 1)
    {
      result.value.error = new Error(t('page.base_specific-methods.error.emptyNumber'))
      return
    }

    await $b24.parent.imPhoneTo( result.value.phone )

    $logger.info(result.value)
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/base/specific-methods'
    })
  }
}

const makeSendPullCommand = async(command: string, params: Record<string, any> = {}) => {
  try {
    result.value = {}

    getRootSideBarApi()?.setLoading(true)

    result.value.selectedUsers = await $b24.dialog.selectUsers()
    result.value.list = result.value.selectedUsers.map((row: SelectedUser): string => {
      return [
        `[id: ${row.id}]`,
        row.name,
      ].join(' ')
    })

    if (result.value.list.length < 1) {
      result.value.list.push('~ empty ~')
    }

    params.userList = [...result.value.list]

    result.value = {}
    result.value.sendInfo = {
      command,
      params
    }

    $logger.warn('>> pull.send >>>', params)

    await $b24.callMethod(
      'pull.application.event.add',
      {
        COMMAND: command,
        PARAMS: params,
        MODULE_ID: b24Helper.value?.getModuleIdPullClient()
      }
    )

    $logger.info(result.value)
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: true,
      clearErrorHref: '/base/specific-methods'
    })
  }
}

const makeSendPullCommandHandler = (message: TypePullMessage): void => {
  $logger.warn('<< pull.get <<<', message)

  if (message.command === 'reload.options') {
    $logger.info("Get pull command for update. Reinit the application")
    reloadData()
    return
  }

  result.value.resultInfo = {
    command: message.command,
    params: message.params
  }

  getRootSideBarApi()?.setLoading(false)
}
// endregion ////

onMounted(async () => {
  if (b24Helper.value) {
    initPullClient()
  }
})
</script>

<template>
  <NuxtLayout name="default">
    <template #header-actions>
      <B24ButtonGroup size="xs" no-split>
        <B24Button
          color="air-secondary-no-accent"
          size="xs"
          :label="$t('page.base_specific-methods.actions.reload')"
          :icon="Refresh7Icon"
          @click.stop="makeReloadWindow"
        />
        <B24Button
          color="air-secondary-no-accent"
          size="xs"
          :label="$t('page.base_specific-methods.actions.clear')"
          :icon="TrashBinIcon"
          @click.stop="result = {}; console.clear()"
        />
      </B24ButtonGroup>
    </template>
    <AdviceBanner>
      <B24Advice
        class="w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        <ProseP>{{ $t('page.base_specific-methods.message.line1') }}</ProseP>
      </B24Advice>
    </AdviceBanner>
    <div class="flex flex-col sm:flex-row items-start justify-between gap-[10px]">
      <ContainerWrapper class="px-[16px] w-[300px]">
        <B24Accordion data-model="active" :items="infoItems">
          <template #selectUsers="{ item }">
            <div class="pb-[12px]">
              <ProseP small accent="less-more">{{ item.description }}</ProseP>
              <B24Button
                :label="t('page.base_specific-methods.actions.select')"
                color="air-selection"
                size="xs"
                loading-auto
                @click="makeSelectUsers"
              />
            </div>
          </template>
          <template #makeImCallToVideo="{ item }">
            <div class="pb-[12px]">
              <ProseP small accent="less-more">{{ item.description }}</ProseP>
              <B24Button
                :label="t('page.base_specific-methods.actions.videoCall')"
                color="air-secondary-accent-2"
                size="xs"
                loading-auto
                @click="makeImCallTo(true)"
              />
            </div>
          </template>
          <template #makeImCallToVoice="{ item }">
            <div class="pb-[12px]">
              <ProseP small accent="less-more">{{ item.description }}</ProseP>
              <B24Button
                :label="t('page.base_specific-methods.actions.voiceCall')"
                color="air-secondary-accent-2"
                size="xs"
                loading-auto
                @click="makeImCallTo(false)"
              />
            </div>
          </template>
          <template #makeImOpenMessenger="{ item }">
            <div class="pb-[12px]">
              <ProseP small accent="less-more">{{ item.description }}</ProseP>
              <B24Button
                :label="t('page.base_specific-methods.actions.imOpenMessenger')"
                color="air-secondary-accent-2"
                size="xs"
                loading-auto
                @click="makeImOpenMessenger()"
              />
            </div>
          </template>
          <template #makeImOpenHistory="{ item }">
            <div class="pb-[12px]">
              <ProseP small accent="less-more">{{ item.description }}</ProseP>
              <B24Button
                :label="t('page.base_specific-methods.actions.imOpenMessengerHistory')"
                color="air-secondary-accent-2"
                size="xs"
                loading-auto
                @click="makeImOpenHistory()"
              />
            </div>
          </template>
          <template #makeImPhoneTo="{ item }">
            <div class="pb-[12px]">
              <ProseP small accent="less-more">{{ item.description }}</ProseP>
              <B24Button
                :label="t('page.base_specific-methods.actions.makeImPhoneTo')"
                color="air-secondary-accent-2"
                size="xs"
                loading-auto
                @click="makeImPhoneTo()"
              />
            </div>
          </template>
          <template #makeSendPullCommand="{ item }">
            <div class="pb-[12px]">
              <ProseP small accent="less-more">{{ item.description }}</ProseP>
              <B24Button
                :label="t('page.base_specific-methods.actions.makeSendPullCommand')"
                color="air-secondary-accent-2"
                size="xs"
                loading-auto
                :disabled="!isPullClientInit"
                @click="makeSendPullCommand('test', {data: Date.now()})"
              />
            </div>
          </template>
        </B24Accordion>
      </ContainerWrapper>
      <div class="w-[600px] flex-auto">
        <ProsePre class="mt-0 overflow-x-auto">{{ result }}</ProsePre>
      </div>
    </div>
  </NuxtLayout>
</template>
