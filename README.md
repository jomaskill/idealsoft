# Idealsoft Shopify Integration

Full-stack product catalog that syncs with a Shopify store.

| Stack | Tech | Path |
|---|---|---|
| Backend | Laravel 12, Lighthouse GraphQL | `idealsoft-shopify-api/` |
| Frontend | Nuxt 4, Tailwind CSS v4 | `idealsoft-shopify-app/` |

## Quick Start

```bash
# 1. Backend
cd idealsoft-shopify-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve --port=8000

# 2. Frontend (new terminal)
cd idealsoft-shopify-app
npm install
npm run dev
```

Open `http://localhost:3000`

## Environment Variables

Add Shopify credentials to `idealsoft-shopify-api/.env`:

```dotenv
SHOPIFY_STORE_DOMAIN=your-store.myshopify.com
SHOPIFY_API_VERSION=2024-01
SHOPIFY_ACCESS_TOKEN=shpat_xxxxx
SHOPIFY_WEBHOOK_SECRET=your-webhook-secret
```

See each project's README for more details.
