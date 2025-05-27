<script setup lang="ts">
import type { BitrixCompany } from '~/types'
import SpinnerIcon from '@bitrix24/b24icons-vue/specialized/SpinnerIcon'

const { user } = useUserSession()

const companies = ref<BitrixCompany[]>([])
const page = ref(1)
const hasMore = ref(true)
const isInited = ref(false)
const loading = ref(false)
const total = ref(0)
const error = ref<string | null>(null)

const resetPagination = () => {
  total.value = 0
  page.value = 1
  hasMore.value = true
  companies.value = []
}

const loadMore = async () => {
  if (!hasMore.value) return

  try {
    loading.value = true
    error.value = null

    const response = await $fetch<{
      nextPage: number | null
      list: BitrixCompany[]
      total: number
    }>('/api/companies', {
      method: 'GET',
      query: { page: page.value }
    })

    if (response) {
      companies.value = [...companies.value, ...response.list]
      hasMore.value = !!response.nextPage
      page.value = response.nextPage || page.value
      total.value = response.total
    }
  } catch (err) {
    error.value = 'Failed to load companies'
    console.error('Error:', err)
  } finally {
    loading.value = false
    isInited.value = true
  }
}

const addCompany = async (count: number = 1000) => {
  if (!isInited.value) return

  try {
    isInited.value = false
    error.value = null

    await $fetch('/api/companies.add', {
      method: 'GET',
      query: { count: count }
    })
  } catch (err) {
    error.value = 'Failed to add companies'
    console.error('Error:', err)
  }

  resetPagination()

  await loadMore()
}

const makeOpenSliderForCompany = (companyId: number) => {
  window.open(
    `${user.value?.bitrix24?.targetOrigin}/crm/company/details/${companyId}/`
  )

  return Promise.resolve()
}

onMounted(loadMore)

defineExpose({
  loadMore,
  addCompany
})
</script>

<template>
  <div class="relative h-[calc(100vh_-_90px)] overflow-y-hidden">
    <template v-if="!isInited">
      <div class="h-[calc(100vh_-_90px)] flex flex-col items-center justify-center">
        <SpinnerIcon class="animate-spin stroke-2 size-[50px]" />
      </div>
    </template>
    <template v-else>
      <B24Alert v-if="error" :title="error" color="danger" class="mb-3" />
      <B24TableWrapper
        v-if="companies.length"
        class="bg-white overflow-y-auto w-full h-[calc(100vh_-_90px)]"
        pin-rows
        row-hover
        rounded
      >
        <table>
          <colgroup>
            <col style="width: 20px">
            <col style="min-width: 200px">
            <col style="min-width: 60px">
          </colgroup>
          <thead>
            <tr>
              <th>#</th>
              <td>Title</td>
              <td>Id</td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(company, companyIndex) in companies" :key="company.id">
              <th>{{ companyIndex + 1 }}.</th>
              <td>
                <B24Link is-action target="_blank" @click.stop="makeOpenSliderForCompany(company.id)">
                  {{ company.title }}
                </B24Link>
              </td>
              <td>{{ company.id }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3">
                <div class="flex flex-row items-center justify-between gap-4">
                  <small>
                    {{ companies.length }} / {{ total }}
                  </small>
                  <B24Button
                    v-if="hasMore"
                    color="link"
                    depth="dark"
                    loading-auto
                    use-spin
                    label="Load more"
                    size="xs"
                    rounded
                    @click="loadMore"
                  />
                  <B24Button
                    rounded
                    label="+100"
                    color="ai"
                    size="xs"
                    @click.stop="addCompany(100)"
                  />
                </div>
              </th>
            </tr>
          </tfoot>
        </table>
      </B24TableWrapper>
      <B24Alert v-else-if="!error && companies.length < 1 && !loading" title="No companies found" color="warning" />
    </template>
  </div>
</template>
