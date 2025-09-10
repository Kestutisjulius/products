<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  product: Object,
})
</script>

<template>
  <Head :title="`Prekė – ${product.sku}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
          Prekės informacija
        </h2>
        <Link href="/products" class="text-sm text-indigo-600 hover:underline">← Grįžti į sąrašą</Link>
      </div>
    </template>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 space-y-6">
        <!-- Foto -->
        <div class="w-full max-w-md mx-auto">
          <img
            :src="product.photo"
            alt="Nuotrauka"
            class="w-full h-64 object-cover rounded shadow"
            @error="e => e.target.src = '/images/no-image.png'"
          />
        </div>

        <!-- Bazinė informacija -->
        <div class="space-y-2 text-gray-800 dark:text-gray-100">
          <div><strong>SKU:</strong> <span class="font-mono">{{ product.sku }}</span></div>
          <div><strong>Aprašymas:</strong> {{ product.description || '—' }}</div>
          <div v-if="product.size"><strong>Dydis:</strong> {{ product.size }}</div>
        </div>

        <!-- Tag'ai -->
        <div v-if="product.tags?.length" class="pt-4">
          <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Žymos:</h3>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="tag in product.tags"
              :key="tag.id"
              class="bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 text-xs px-2 py-0.5 rounded"
            >
              {{ tag.title }}
            </span>
          </div>
        </div>

        <!-- Miestai ir likučiai -->
        <div v-if="product.stocks?.length" class="pt-4">
          <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Sandėlio likučiai:</h3>
          <ul class="list-disc list-inside text-sm">
            <li v-for="stock in product.stocks" :key="stock.id">
              <strong>{{ stock.city }}:</strong> {{ stock.stock }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
