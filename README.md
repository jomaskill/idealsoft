# Idealsoft Shopify Integration

Full-stack product catalog that syncs with a Shopify store.

- **Backend:** Laravel 12 + Lighthouse GraphQL 
- **Frontend:** Nuxt 4 + Tailwind CSS v4

---

## Prerequisites

- PHP 8.2+
- Node.js 18+
- Composer

## Backend Setup

```bash
cd idealsoft-shopify-api

composer install
cp .env.example .env
php artisan key:generate
```

### Configure Shopify credentials in `.env`

```dotenv
SHOPIFY_STORE_DOMAIN=your-store.myshopify.com
SHOPIFY_API_VERSION=2024-01
SHOPIFY_ACCESS_TOKEN=shpat_xxxxx
```

> You can get the access token from your Shopify admin → Settings → Apps → Develop apps → Create/select app → API credentials.

### Database & Seed

```bash
php artisan migrate
php artisan db:seed          # creates 50 sample products for testing without Shopify
```

### Sync from Shopify (optional, requires valid credentials)

```bash
php artisan shopify:sync-products
```

### Start backend

```bash
php artisan serve --port=8000
```

The GraphQL playground is available at `http://localhost:8000/graphiql`.

---

## Frontend Setup

```bash
cd idealsoft-shopify-app

npm install
```

### Start frontend

```bash
npm run dev
```

Open `http://localhost:3000` — it proxies GraphQL requests to the backend automatically.

---

## Project Structure

```
idealsoft-shopify-api/          # Laravel backend
├── app/Domains/Shopify/
│   ├── Services/               # ShopifyClientService, ProductSyncService
│   ├── Jobs/                   # FetchShopifyProductsJob, SyncProductJob
│   ├── Models/Product.php
│   └── DTOs/ShopifyProductData.php
├── graphql/schema.graphql      # GraphQL schema
└── config/shopify.php          # 3 env keys

idealsoft-shopify-app/          # Nuxt frontend
├── app/
│   ├── app.vue                 # Main page (filters, grid, pagination)
│   ├── components/
│   │   ├── ProductCard.vue     # Product grid card
│   │   └── ProductModal.vue    # Product detail modal
│   ├── composables/useProducts.ts
│   ├── graphql/queries.ts
│   └── types/product.ts
└── server/api/graphql.post.ts  # Proxy to avoid CORS
```

---

## Testing the GraphQL API

```bash
curl -X POST http://localhost:8000/graphql \
  -H "Content-Type: application/json" \
  -d '{"query":"{ products(first: 5) { data { id title status vendor } paginatorInfo { total } } }"}'
```
