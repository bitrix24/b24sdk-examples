<script setup lang="ts">
import { computed } from 'vue'
import type { DescriptionListItem } from '@bitrix24/b24ui-nuxt'
import type { B24Frame } from '@bitrix24/b24jssdk'

const { t } = useI18n()
const route = useRoute()
route.meta.pageTitle = t('page.base_specific-parameters.seo.title')
route.meta.pageDescription = t('page.base_specific-parameters.seo.description')

// region Init ////
const { $logger, b24Helper, processErrorGlobal } = useAppInit('SpecificParametersPage')
const { $initializeB24Frame } = useNuxtApp()
const $b24: B24Frame = await $initializeB24Frame()
$logger.info('Hi from base/specific-parameters')
// endregion ////

const infoItems = computed(() => [
  {
    label: t('page.base_specific-parameters.message.isSelfHosted'),
    code: 'isSelfHosted',
    description: b24Helper.value?.isSelfHosted ? 'Y' : 'N'
  },
  {
    label: t('page.base_specific-parameters.message.primaryKeyIncrementValue'),
    code: 'primaryKeyIncrementValue',
    description: (b24Helper.value?.primaryKeyIncrementValue ?? 0).toString()
  },
  // makeOpenPage(b24Helper?.b24SpecificUrl.MainSettings)
  {
    label: t('page.base_specific-parameters.message.mainSettings'),
    code: 'mainSettings',
    description: (b24Helper.value?.b24SpecificUrl.MainSettings || '').toString()
  },
  {
    label: t('page.base_specific-parameters.message.ufList'),
    code: 'ufList',
    description: (b24Helper.value?.b24SpecificUrl.UfList || '').toString()
  },
  {
    label: t('page.base_specific-parameters.message.ufPage'),
    code: 'ufPage',
    description: (b24Helper.value?.b24SpecificUrl.UfPage || '').toString()
  }
] satisfies DescriptionListItem[] )

// region Actions ////
const makeOpenPage = async(url: string) => {
  try {
    const response = await $b24.slider.openPath(
      $b24.slider.getUrl(`${url}`),
      950
    )

    $logger.warn(response)
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/base/specific-parameters'
    })
  }
}

const makeOpenDealUfList = async(url: string) => {
  try {
    const path = $b24.slider.getUrl(url)
    path.searchParams.set('moduleId', 'crm')
    path.searchParams.set('entityId', 'CRM_DEAL')

    const response = await $b24.slider.openPath(
      path,
      950
    )

    $logger.warn(response)
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/base/specific-parameters'
    })
  }
}
// endregion ////
</script>

<template>
  <div>
    <AdviceBanner>
      <B24Advice
        class="w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        <ProseP>{{ $t('page.base_specific-parameters.message.line1') }}</ProseP>
      </B24Advice>
    </AdviceBanner>
    <ContainerWrapper class="px-[22px] py-[5px]">
      <B24DescriptionList
        :b24ui="{ container: 'mt-0' }"
        :items="infoItems"
      >
        <template #description="{ item }">
          <template v-if="item.code === 'isSelfHosted'">
            <B24Badge v-if="item.description === 'Y'" color="air-secondary-accent" label="Self Hosted" />
            <B24Badge v-if="item.description === 'N'" color="air-primary" label="Cloud" />
          </template>
          <template v-else-if="item.code === 'mainSettings'">
            <B24Badge
              color="air-secondary-accent-2"
              use-link
              :label="item.description"
              @click.stop="makeOpenPage(item.description)"
            />
          </template>
          <template v-else-if="item.code === 'ufList'">
            <B24Badge
              color="air-secondary-accent-2"
              use-link
              :label="item.description"
              @click.stop="makeOpenDealUfList(item.description)"
            />
          </template>
          <template v-else>
            <B24Badge
              color="air-tertiary"
              :label="item.description"
            />
          </template>
        </template>
      </B24DescriptionList>
    </ContainerWrapper>
  </div>
</template>
