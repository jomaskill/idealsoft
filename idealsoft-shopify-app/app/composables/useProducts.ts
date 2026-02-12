import { PRODUCTS_QUERY } from '~/graphql/queries'
import type { Product, ProductStatus, ProductsResponse, PaginatorInfo } from '~/types/product'

interface Filters {
    search: string
    status: ProductStatus | ''
    vendor: string
}

async function gql<T>(queryString: string, variables?: Record<string, unknown>): Promise<T> {
    const res = await $fetch<{ data: T; errors?: { message: string }[] }>('/api/graphql', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: { query: queryString, variables },
    })
    if (res.errors?.length) throw new Error(res.errors[0].message)
    return res.data
}

export function useProducts() {
    const products = ref<Product[]>([])
    const paginatorInfo = ref<PaginatorInfo | null>(null)
    const loading = ref(false)
    const error = ref<string | null>(null)
    const currentPage = ref(1)
    const perPage = ref(12)

    const filters = reactive<Filters>({
        search: '',
        status: '',
        vendor: '',
    })

    async function fetchProducts(): Promise<void> {
        loading.value = true
        error.value = null
        try {
            const variables: Record<string, unknown> = {
                first: perPage.value,
                page: currentPage.value,
            }
            if (filters.search) variables.search = filters.search
            if (filters.status) variables.status = filters.status.toUpperCase()
            if (filters.vendor) variables.vendor = filters.vendor

            const data = await gql<ProductsResponse>(PRODUCTS_QUERY, variables)
            products.value = data.products.data
            paginatorInfo.value = data.products.paginatorInfo
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Failed to fetch products'
            products.value = []
            paginatorInfo.value = null
        } finally {
            loading.value = false
        }
    }

    function goToPage(page: number) {
        currentPage.value = page
        fetchProducts()
    }

    function applyFilters() {
        currentPage.value = 1
        fetchProducts()
    }

    function resetFilters() {
        filters.search = ''
        filters.status = ''
        filters.vendor = ''
        currentPage.value = 1
        fetchProducts()
    }

    return { products, paginatorInfo, loading, error, filters, fetchProducts, goToPage, applyFilters, resetFilters }
}
