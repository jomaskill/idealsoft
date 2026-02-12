# BACKEND API

Laravel 12 + Lighthouse GraphQL

---

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed          # 50 sample products (no Shopify needed)
php artisan serve --port=8000
```

### Shopify Credentials

Add to `.env`:

```dotenv
SHOPIFY_STORE_DOMAIN=your-store.myshopify.com
SHOPIFY_API_VERSION=2024-01
SHOPIFY_ACCESS_TOKEN=shpat_xxxxx
SHOPIFY_WEBHOOK_SECRET=your-webhook-secret
```

| Key | Description |
|---|---|
| `SHOPIFY_STORE_DOMAIN` | Your `*.myshopify.com` domain |
| `SHOPIFY_API_VERSION` | Shopify API version (default: `2024-01`) |
| `SHOPIFY_ACCESS_TOKEN` | Admin API access token from your Shopify app |
| `SHOPIFY_WEBHOOK_SECRET` | Secret for verifying incoming webhook signatures |

---

## Database

Uses SQLite by default (`DB_CONNECTION=sqlite`).

### Products Table

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | auto-increment PK |
| `shopify_id` | string | unique, maps to Shopify product ID |
| `title` | string | |
| `description` | text | nullable, from `body_html` |
| `handle` | string | indexed |
| `vendor` | string | nullable, indexed |
| `product_type` | string | nullable |
| `status` | string | `active`, `draft`, `archived` — indexed |
| `tags` | json | nullable |
| `variants` | json | nullable, array of `{title, sku, price, compare_at_price, inventory_quantity}` |
| `images` | json | nullable, array of `{src, alt, position, width, height}` |
| `published_at` | timestamp | nullable |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

## GraphQL API

Playground: `http://localhost:8000/graphiql`

### Queries

**List products** (paginated):

```graphql
query {
  products(first: 15, page: 1, search: "wax", status: ACTIVE, vendor: "Idealsoft") {
    data {
      id
      shopify_id
      title
      description
      handle
      vendor
      product_type
      status
      tags
      variants { title sku price compare_at_price inventory_quantity }
      images { src alt position width height }
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
```

| Argument | Type | Description |
|---|---|---|
| `first` | Int | Items per page (default: 15) |
| `page` | Int | Page number |
| `search` | String | Search title, vendor, or handle |
| `status` | `ACTIVE` \| `DRAFT` \| `ARCHIVED` | Filter by status |
| `vendor` | String | Exact vendor match |

**Single product:**

```graphql
query {
  product(id: 1) {
    id
    title
    status
    variants { title price }
  }
}
```

### cURL example

```bash
curl -X POST http://localhost:8000/graphql \
  -H "Content-Type: application/json" \
  -d '{"query":"{ products(first: 5) { data { id title status vendor } paginatorInfo { total } } }"}'
```

---

## Queue Architecture

Uses Laravel's database queue (`QUEUE_CONNECTION=database`) to sync products from Shopify without blocking HTTP requests.

### How the sync works

```
artisan shopify:sync-products
        │
        ▼
┌──────────────────────────┐
│ FetchShopifyProductsJob  │  Fetches one page (50 products) from
│                          │  Shopify REST API
└──────────┬───────────────┘
           │
           ├──► SyncProductJob (product 1)   ─► updateOrCreate in DB
           ├──► SyncProductJob (product 2)   ─► updateOrCreate in DB
           ├──► ...
           ├──► SyncProductJob (product 50)  ─► updateOrCreate in DB
           │
           │   Has next page?
           │
           └──► FetchShopifyProductsJob (next pageInfo)
                        │
                        ├──► SyncProductJob (product 51)
                        ├──► ...
                        └──► (repeats until no more pages)
```

### Job details

| Job | Purpose |
|---|---|
| `FetchShopifyProductsJob` | Calls the Shopify REST API to fetch one page of products (50 per page). Dispatches a `SyncProductJob` for each product. If there are more pages, dispatches another `FetchShopifyProductsJob` with the next cursor (`pageInfo`). |
| `SyncProductJob` | Receives raw product data from Shopify, maps it to a `ShopifyProductData` DTO, and upserts it into the local database via `updateOrCreate`. |

### Running the queue worker

The sync command dispatches jobs to the queue — you need a worker running to process them:

```bash
# Terminal 1: start the worker
php artisan queue:work

# Terminal 2: trigger the sync
php artisan shopify:sync-products
```

To sync a single product by Shopify ID (runs synchronously, no queue needed):

```bash
php artisan shopify:sync-products --product-id=1234567890
```

---

## Webhooks

Shopify can notify the app in real-time when products change. The webhook endpoint is:

```
POST /api/webhooks/shopify
```

It handles three topics:

| Topic | Action |
|---|---|
| `products/create` | Dispatches `SyncProductJob` to queue |
| `products/update` | Dispatches `SyncProductJob` to queue |
| `products/delete` | Deletes the product from the local database |

All incoming webhooks are verified via HMAC signature using `SHOPIFY_WEBHOOK_SECRET` before processing.

---

## Artisan Commands

| Command | Description |
|---|---|
| `php artisan shopify:sync-products` | Dispatch full product sync to queue |
| `php artisan shopify:sync-products --product-id=ID` | Sync a single product (synchronous) |

---

## Project Structure

```
app/Domains/Shopify/
├── Console/SyncShopifyProductsCommand.php
├── DTOs/
│   ├── ShopifyProductData.php
│   └── SyncResult.php
├── Enums/ProductStatus.php
├── Http/
│   ├── Controllers/ShopifyWebhookController.php
│   └── Middleware/VerifyShopifyWebhook.php
├── Jobs/
│   ├── FetchShopifyProductsJob.php
│   └── SyncProductJob.php
├── Models/Product.php
└── Services/
    ├── ShopifyClientService.php
    └── ProductSyncService.php
config/shopify.php
graphql/schema.graphql
routes/api.php
```
