<script setup lang="ts">
import { ref } from 'vue'
import EditIcon from '@bitrix24/b24icons-vue/button/EditIcon'
import PlusIcon from '@bitrix24/b24icons-vue/button/PlusIcon'
import type {B24Frame, StatusClose} from "@bitrix24/b24jssdk";

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
      clearErrorHref: '/main'
    })
  } finally {
    getRootSideBarApi()?.setLoading(false)
  }
}

const makeOpenSliderEditCurrency = async(currencyCode: string) => {
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
      $logger.info("Slider is closed! Reinit the application")
      return reloadData()
    }
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/main'
    })
  } finally {
    getRootSideBarApi()?.setLoading(false)
  }
})
// endregion ////
</script>

<template>
  <dl class="divide-y divide-base-100">
    <div class="px-2 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
      <dt class="pt-2 text-sm font-medium leading-6 flex flex-row gap-2 items-start justify-start">
        <B24Button
          :icon="PlusIcon"
          @click.stop="makeOpenSliderAddCurrency()"
        />

        <div class="flex-1">Symbolic Identifier of the Base Currency</div>
      </dt>
      <dd class="mt-1 text-sm leading-6 text-base-700 sm:col-span-2 sm:mt-0">
        <div
          class="text-sm font-medium flex flex-row gap-2 items-center justify-start">
          <div>
            <B24InputNumber
              v-model.number="value"
              :step="101.023"
            />
          </div>
          <div class="flex-1">{{b24Helper?.currency.baseCurrency}}
          </div>
        </div>
      </dd>
    </div>
    <div
      v-for="(currencyCode) in b24Helper?.currency.currencyList"
      :key="currencyCode"
      class="px-2 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"
    >
      <dt class="text-sm font-medium leading-6 flex flex-row gap-2 items-start justify-start">
        <button
          class="text-base-400 hover:text-base-master hover:bg-base-100 rounded"
          @click.stop="makeOpenSliderEditCurrency(currencyCode)"
        >
          <EditIcon class="size-6"/>
        </button>
        <div class="flex-1">{{currencyCode}}
          • <span v-html="b24Helper?.currency.getCurrencyFullName(currencyCode, locale)"/>
          • <span v-html="b24Helper?.currency.getCurrencyLiteral(currencyCode)"/>
        </div>
      </dt>
      <dd class="mt-1 text-sm leading-6 text-base-700 sm:col-span-2 sm:mt-0">
        <span v-html="b24Helper?.currency.format(value, currencyCode, locale)"/>
      </dd>
    </div>
  </dl>
</template>
