<script setup lang="ts">
import type { BitrixCompany } from '~/types'

const { user } = useUserSession()

const companies = ref<BitrixCompany[]>([])
const page = ref(1)
const hasMore = ref(true)
const loading = ref(false)
const error = ref<string | null>(null)

const loadMore = async () => {
  if (!hasMore.value) return

  try {
    loading.value = true
    error.value = null

    const { data } = await useFetch<{
      nextPage: number | null
      list: BitrixCompany[]
      total: number
    }>('/api/companies', {
      method: 'GET',
      query: { page: page.value }
    })

    if (data.value) {
      companies.value = [...companies.value, ...data.value.list]
      hasMore.value = !!data.value.nextPage
      page.value = data.value.nextPage || page.value
    }
  } catch (err) {
    error.value = 'Failed to load companies'
    console.error('Error:', err)
  } finally {
    loading.value = false
  }
}

const resetPagination = () => {
  page.value = 1
  hasMore.value = true
  companies.value = []
}

const addCompany = async (count: number = 1000) => {
  if (loading.value) return

  try {
    loading.value = true
    error.value = null

    await useFetch('/api/companies.add', {
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
    `${user.value?.targetOrigin}/crm/company/details/${companyId}/`
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
  <div>
    <B24Alert v-if="error" :title="error" color="danger" />
    <div v-if="companies.length">
      <ul class="list-inside list-decimal">
        <li v-for="company in companies" :key="company.id">
          <B24Link is-action target="_blank" @click.stop="makeOpenSliderForCompany(company.id)">
            {{ company.title }}
          </B24Link>
          <small class="pl-1">[id: {{ company.id }}]</small>
        </li>
      </ul>
      <div class="w-full mt-4 flex flex-row justify-center">
        <B24Button
          v-if="hasMore"
          color="link"
          depth="dark"
          loading-auto
          use-clock
          label="Load more"
          size="xs"
          rounded
          @click="loadMore"
        />
      </div>
    </div>
    <B24Alert v-else-if="companies.length < 1 && !loading" title="No companies found" color="danger" />
  </div>
</template>
