<script setup lang="ts">
import type { Product } from '~/types/product'

const props = defineProps<{ product: Product }>()

const primaryImage = computed(() => {
  const imgs = props.product.images
  if (!imgs?.length) return null
  return imgs.sort((a, b) => (a.position ?? 0) - (b.position ?? 0))[0]
})

const priceRange = computed(() => {
  const prices = (props.product.variants ?? []).map(v => parseFloat(v.price)).filter(p => !isNaN(p))
  if (!prices.length) return null
  const min = Math.min(...prices), max = Math.max(...prices)
  return min === max ? `$${min.toFixed(2)}` : `$${min.toFixed(2)} – $${max.toFixed(2)}`
})

const badgeClasses: Record<string, string> = {
  Active: 'bg-green-100 text-green-600',
  Draft: 'bg-amber-100 text-amber-600',
  Archived: 'bg-slate-100 text-slate-500',
}
</script>

<template>
  <article class="bg-white border border-slate-200 rounded-xl overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-lg">
    <div class="relative aspect-square overflow-hidden bg-slate-50">
      <img v-if="primaryImage" :src="primaryImage.src" :alt="primaryImage.alt || product.title" loading="lazy" class="w-full h-full object-cover" />
      <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
        <Icon name="ph:image-duotone" size="40" />
      </div>
      <span class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full text-[0.7rem] font-semibold uppercase tracking-wide" :class="badgeClasses[product.status]">{{ product.status }}</span>
    </div>
    <div class="p-3.5">
      <p v-if="product.vendor" class="m-0 mb-1 text-[0.72rem] font-semibold text-indigo-500 uppercase tracking-wide">{{ product.vendor }}</p>
      <h3 class="m-0 mb-2.5 text-sm font-semibold text-slate-800 leading-snug line-clamp-2">{{ product.title }}</h3>
      <div class="flex items-center justify-between">
        <span class="text-[0.95rem] font-bold" :class="priceRange ? 'text-slate-800' : 'text-slate-400 font-medium'">{{ priceRange ?? 'N/A' }}</span>
        <span v-if="product.variants?.length" class="text-[0.72rem] text-slate-400">
          {{ product.variants.length }} variant{{ product.variants.length > 1 ? 's' : '' }}
        </span>
      </div>
    </div>
  </article>
</template>
