export interface ProductVariant {
    title: string | null
    sku: string | null
    price: string
    compare_at_price: string | null
    inventory_quantity: number | null
}

export interface ProductImage {
    src: string
    alt: string | null
    position: number | null
    width: number | null
    height: number | null
}

export type ProductStatus = 'Active' | 'Draft' | 'Archived'

export interface Product {
    id: string
    shopify_id: string
    title: string
    description: string | null
    handle: string
    vendor: string | null
    product_type: string | null
    status: ProductStatus
    tags: string[] | null
    variants: ProductVariant[] | null
    images: ProductImage[] | null
    published_at: string | null
    created_at: string
    updated_at: string
}

export interface PaginatorInfo {
    total: number
    currentPage: number
    lastPage: number
    perPage: number
    hasMorePages: boolean
}

export interface ProductsResponse {
    products: {
        data: Product[]
        paginatorInfo: PaginatorInfo
    }
}

export interface ProductResponse {
    product: Product | null
}
