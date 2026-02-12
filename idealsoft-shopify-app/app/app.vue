<script setup lang="ts">
import type { ProductStatus } from '~/types/product'

const { products, paginatorInfo, loading, error, filters, fetchProducts, goToPage, applyFilters, resetFilters } = useProducts()

const vendors = computed(() => {
  const set = new Set<string>()
  products.value.forEach(p => { if (p.vendor) set.add(p.vendor) })
  return Array.from(set).sort()
})

// Debounced search
let timer: ReturnType<typeof setTimeout>
function onSearch(e: Event) {
  const val = (e.target as HTMLInputElement).value
  clearTimeout(timer)
  timer = setTimeout(() => { filters.search = val }, 300)
}

// Pagination pages
const pages = computed(() => {
  if (!paginatorInfo.value) return []
  const { currentPage: cur, lastPage: last } = paginatorInfo.value
  const r: number[] = [1]
  const start = Math.max(2, cur - 2), end = Math.min(last - 1, cur + 2)
  if (start > 2) r.push(-1)
  for (let i = start; i <= end; i++) r.push(i)
  if (end < last - 1) r.push(-1)
  if (last > 1) r.push(last)
  return r
})

const statuses: Array<{ value: ProductStatus | ''; label: string }> = [
  { value: '', label: 'All Statuses' },
  { value: 'Active', label: 'Active' },
  { value: 'Draft', label: 'Draft' },
  { value: 'Archived', label: 'Archived' },
]

watch(() => [filters.search, filters.status, filters.vendor], () => applyFilters())
onMounted(() => fetchProducts())
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans text-slate-800">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white border-b border-slate-200">
      <div class="max-w-6xl mx-auto px-5 h-[60px] flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <Icon name="ph:storefront-duotone" size="24" class="text-indigo-500" />
          <h1 class="m-0 text-base font-bold">Product Catalog</h1>
        </div>
        <span v-if="paginatorInfo" class="text-sm text-slate-400">{{ paginatorInfo.total }} products</span>
      </div>
    </header>

    <main class="max-w-6xl mx-auto px-5 py-6 flex flex-col gap-5">
      <!-- Filters -->
      <div class="bg-white border border-slate-200 rounded-xl p-3.5">
        <div class="flex gap-2.5 items-center flex-wrap">
          <div class="relative flex-1 min-w-[200px]">
            <Icon name="ph:magnifying-glass" size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
            <input type="text" :value="filters.search" placeholder="Search products..." @input="onSearch"
              class="w-full py-2 pl-10 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 outline-none focus:border-indigo-500 placeholder:text-slate-400" />
          </div>
          <select :value="filters.status" @change="filters.status = ($event.target as HTMLSelectElement).value as ProductStatus | ''"
            class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 outline-none cursor-pointer min-w-[140px] focus:border-indigo-500">
            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
          <select :value="filters.vendor" @change="filters.vendor = ($event.target as HTMLSelectElement).value"
            class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 outline-none cursor-pointer min-w-[140px] focus:border-indigo-500">
            <option value="">All Vendors</option>
            <option v-for="v in vendors" :key="v" :value="v">{{ v }}</option>
          </select>
          <button v-if="filters.search || filters.status || filters.vendor" @click="resetFilters()"
            class="inline-flex items-center gap-1 py-2 px-3.5 bg-transparent border border-slate-200 rounded-lg text-xs text-slate-500 cursor-pointer hover:text-red-500 hover:border-red-300">
            <Icon name="ph:x-circle" size="16" /> Clear
          </button>
        </div>
      </div>

      <!-- Loading skeleton -->
      <div v-if="loading && !products.length" class="grid grid-cols-4 gap-4 max-lg:grid-cols-3 max-md:grid-cols-2 max-sm:grid-cols-1">
        <div v-for="i in 12" :key="i" class="bg-white border border-slate-200 rounded-xl overflow-hidden">
          <div class="aspect-square bg-slate-100 animate-pulse" />
          <div class="p-3.5 flex flex-col gap-2">
            <div class="h-3 w-2/5 bg-slate-100 rounded animate-pulse" />
            <div class="h-3 w-full bg-slate-100 rounded animate-pulse" />
            <div class="h-3 w-3/5 bg-slate-100 rounded animate-pulse" />
          </div>
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="flex flex-col items-center py-14 text-center">
        <Icon name="ph:warning-circle-duotone" size="48" class="text-red-400 mb-3" />
        <h3 class="m-0 mb-1.5 text-base font-semibold">Something went wrong</h3>
        <p class="m-0 mb-4 text-sm text-slate-500">{{ error }}</p>
        <button @click="fetchProducts()" class="inline-flex items-center gap-1.5 py-2 px-4 bg-indigo-500 border-none rounded-lg text-white text-sm font-semibold cursor-pointer hover:bg-indigo-600">
          <Icon name="ph:arrow-clockwise" size="16" /> Try again
        </button>
      </div>

      <!-- Empty -->
      <div v-else-if="!loading && !products.length" class="flex flex-col items-center py-14 text-center">
        <Icon name="ph:package-duotone" size="48" class="text-slate-300 mb-3" />
        <h3 class="m-0 mb-1.5 text-base font-semibold">No products found</h3>
        <p class="m-0 mb-4 text-sm text-slate-500">Try adjusting your search or filters</p>
      </div>

      <!-- Product grid -->
      <div v-else class="grid grid-cols-4 gap-4 max-lg:grid-cols-3 max-md:grid-cols-2 max-sm:grid-cols-1 transition-opacity" :class="{ 'opacity-50 pointer-events-none': loading }">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
      </div>

      <!-- Pagination -->
      <div v-if="paginatorInfo && paginatorInfo.lastPage > 1" class="flex items-center justify-between flex-wrap gap-3">
        <p class="m-0 text-sm text-slate-500">
          Showing <strong>{{ (paginatorInfo.currentPage - 1) * paginatorInfo.perPage + 1 }}</strong>–<strong>{{ Math.min(paginatorInfo.currentPage * paginatorInfo.perPage, paginatorInfo.total) }}</strong>
          of <strong>{{ paginatorInfo.total }}</strong>
        </p>
        <div class="flex items-center gap-1">
          <button @click="goToPage(paginatorInfo.currentPage - 1)" :disabled="paginatorInfo.currentPage === 1"
            class="inline-flex items-center justify-center w-[34px] h-[34px] bg-white border border-slate-200 rounded-lg text-sm text-slate-600 cursor-pointer disabled:opacity-35 disabled:cursor-not-allowed hover:border-indigo-500 hover:text-indigo-500">
            <Icon name="ph:caret-left-bold" size="14" />
          </button>
          <template v-for="(pg, idx) in pages" :key="idx">
            <span v-if="pg === -1" class="w-[34px] h-[34px] inline-flex items-center justify-center text-slate-400">…</span>
            <button v-else @click="goToPage(pg)"
              class="inline-flex items-center justify-center min-w-[34px] h-[34px] px-1.5 border rounded-lg text-sm font-medium cursor-pointer"
              :class="pg === paginatorInfo.currentPage ? 'bg-indigo-500 border-indigo-500 text-white' : 'bg-white border-slate-200 text-slate-600 hover:border-indigo-500 hover:text-indigo-500'">
              {{ pg }}
            </button>
          </template>
          <button @click="goToPage(paginatorInfo.currentPage + 1)" :disabled="!paginatorInfo.hasMorePages"
            class="inline-flex items-center justify-center w-[34px] h-[34px] bg-white border border-slate-200 rounded-lg text-sm text-slate-600 cursor-pointer disabled:opacity-35 disabled:cursor-not-allowed hover:border-indigo-500 hover:text-indigo-500">
            <Icon name="ph:caret-right-bold" size="14" />
          </button>
        </div>
      </div>
    </main>

  </div>
</template>
