export const PRODUCTS_QUERY = `
  query GetProducts($first: Int, $page: Int, $search: String, $status: ProductStatus, $vendor: String) {
    products(first: $first, page: $page, search: $search, status: $status, vendor: $vendor) {
      data {
        id
        shopify_id
        title
        handle
        vendor
        product_type
        status
        tags
        variants {
          title
          sku
          price
          compare_at_price
          inventory_quantity
        }
        images {
          src
          alt
          position
        }
        published_at
      }
      paginatorInfo {
        total
        currentPage
        lastPage
        perPage
        hasMorePages
      }
    }
  }
`
