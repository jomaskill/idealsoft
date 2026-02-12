# FRONTEND APP

Nuxt 4 product catalog UI with Tailwind CSS v4.

## Setup

```bash
npm install
npm run dev
```

Open `http://localhost:3000`

> The frontend proxies GraphQL requests to the backend at `http://localhost:8000` via `server/api/graphql.post.ts` to avoid CORS issues.

## Project Structure

```
app/
├── app.vue                    # Main page: filters, product grid, pagination
├── components/
│   └── ProductCard.vue        # Product card with image, price, status badge
├── composables/
│   └── useProducts.ts         # Data fetching, filters, pagination state
├── graphql/
│   └── queries.ts             # GraphQL query strings
└── types/
    └── product.ts             # TypeScript interfaces
server/
└── api/
    └── graphql.post.ts        # Proxy to backend GraphQL API
```

## Features

- Search by product title
- Filter by status (Active / Draft / Archived)
- Filter by vendor
- Paginated product grid
- Responsive layout (1–4 columns)
