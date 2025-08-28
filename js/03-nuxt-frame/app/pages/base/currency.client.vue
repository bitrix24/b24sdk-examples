<script setup lang="ts">
import { ref } from 'vue'
import PlusMIcon from '@bitrix24/b24icons-vue/outline/PlusMIcon'
import type { B24Frame } from '@bitrix24/b24jssdk'

definePageMeta({
  layout: false
})

const { t, locale } = useI18n()
const route = useRoute()
route.meta.pageTitle = t('page.base_currency.seo.title')
route.meta.pageDescription = t('page.base_currency.seo.description')

// region Init ////
const { $logger, b24Helper, processErrorGlobal, reloadData, getRootSideBarApi } = useAppInit('CurrencyPage')
const { $initializeB24Frame } = useNuxtApp()

$logger.info('Hi from base/currency')
const $b24: B24Frame = await $initializeB24Frame()

const value = ref(123456.789)
// endregion ////

// region Actions ////
const makeOpenSliderAddCurrency = async() => {
  try {
    const response = await $b24.slider.openPath(
      $b24.slider.getUrl(`/crm/configs/currency/add/`),
      950
    )

    $logger.warn(response)

    if(
      !response.isOpenAtNewWindow
      && response.isClose
    )
    {
      $logger.info('Slider is closed! Reinit the application')
      getRootSideBarApi()?.setLoading(true)
      await reloadData()
    }
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/base/currency'
    })
  } finally {
    getRootSideBarApi()?.setLoading(false)
  }
}

const makeOpenSliderEditCurrency = async (currencyCode: string) => {
  try {
    const response = await $b24.slider.openPath(
      $b24.slider.getUrl(`/crm/configs/currency/edit/${currencyCode}/`),
      950
    )
    $logger.warn(response)
    if(
      !response.isOpenAtNewWindow
      && response.isClose
    )
    {
      $logger.info('Slider is closed! Reinit the application')
      getRootSideBarApi()?.setLoading(true)
      await reloadData()
    }
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/base/currency'
    })
  } finally {
    getRootSideBarApi()?.setLoading(false)
  }
}
// endregion ////
</script>

<template>
  <NuxtLayout name="default">
    <template #top-actions-start>
      <B24Button
        color="air-primary-success"
        :icon="PlusMIcon"
        :label="$t('page.base_currency.actions.create')"
        @click.stop="makeOpenSliderAddCurrency()"
      />
    </template>
    <AdviceBanner>
      <B24Advice
        class="w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        <ProseP>{{ $t('page.base_currency.message.line1') }}</ProseP>
      </B24Advice>
    </AdviceBanner>
    <ContainerWrapper>
      <B24TableWrapper
        row-hover
        class="overflow-x-auto w-full"
      >
        <table>
          <colgroup>
            <col class="w-[75px]">
            <col class="w-[275px]">
            <col class="w-[75px]">
            <col>
          </colgroup>
          <thead>
            <tr>
              <th>{{ $t('page.base_currency.list.code') }}</th>
              <th>{{ $t('page.base_currency.list.title') }}</th>
              <th>{{ $t('page.base_currency.list.literal') }}</th>
              <th>
                <div class="ps-[56px]">
                  {{ $t('page.base_currency.list.value') }}
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(currencyCode) in b24Helper?.currency.currencyList"
              :key="currencyCode"
            >
              <th>
                <template v-if="b24Helper?.currency.baseCurrency === currencyCode">
                  <B24Badge :label="currencyCode" />
                </template>
                <template v-else>
                  <B24Badge :label="currencyCode" color="air-tertiary" />
                </template>
              </th>
              <!-- eslint-disable-next-line vue/no-v-html -->
              <td><B24Link @click.stop="makeOpenSliderEditCurrency(currencyCode)"><span v-html="b24Helper?.currency.getCurrencyFullName(currencyCode, locale)" /></B24Link></td>
              <!-- eslint-disable-next-line vue/no-v-html -->
              <td><span v-html="b24Helper?.currency.getCurrencyLiteral(currencyCode)" /></td>
              <!-- eslint-disable-next-line vue/no-v-html -->
              <td><span class="ms-[56px]" v-html="b24Helper?.currency.format(value, currencyCode, locale)" /></td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3">&nbsp;</th>
              <th>
                <B24ButtonGroup size="sm" no-split>
                  <B24InputNumber
                    v-model.number="value"
                    :step="101.023"
                    class="w-[180px]"
                  />
                  <B24Tooltip
                    :text="$t('page.base_currency.list.baseCurrency')"
                    :content="{ side: 'top' }"
                    :delay-duration="0"
                  >
                    <B24Button color="air-secondary-no-accent" :label="b24Helper?.currency.baseCurrency" />
                  </B24Tooltip>
                </B24ButtonGroup>
              </th>
            </tr>
          </tfoot>
        </table>
      </B24TableWrapper>
    </ContainerWrapper>
  </NuxtLayout>
</template>
