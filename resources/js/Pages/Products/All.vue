<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, Link } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
  products: { type: Object, required: true },     // Laravel paginator
  filters: { type: Object, default: () => ({}) }, // search, sort, direction, tag
  popularTags: { type: Array, default: () => [] } // [{ title, products_count }]
})

const search    = ref(props.filters.search ?? '')
const sort      = ref(props.filters.sort ?? 'updated_at')
const direction = ref(props.filters.direction ?? 'desc')
const tag       = ref(props.filters.tag ?? '')
const view      = ref('table') // 'table' | 'grid'

// paprastas debounce, kad nekviestume per dažnai
let debounceTimer = null
function triggerRefresh() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => refresh(), 300)
}

watch([search, sort, direction, tag], triggerRefresh)

function refresh(page = 1) {
  router.get(route('products.index'), {
    search: search.value || null,
    sort: sort.value,
    direction: direction.value,
    tag: tag.value || null,
    page
  }, {
    preserveScroll: true,
    replace: true
  })
}

function goToPage(url) {
  const page = new URL(url).searchParams.get('page')
  refresh(page)
}
</script>

<template>
  <Head title="Produktų sąrašas" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Produktai</h2>
        <div class="flex gap-2">
          <button
            @click="view = 'table'"
            :class="['px-3 py-1 rounded border text-sm', view === 'table' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-900']"
          >
            Lentelė
          </button>
          <button
            @click="view = 'grid'"
            :class="['px-3 py-1 rounded border text-sm', view === 'grid' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-900']"
          >
            Tinklelis
          </button>
        </div>
      </div>
    </template>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
      <!-- Filtrai -->
      <div class="flex flex-wrap gap-4 items-center">
        <input
          v-model="search"
          type="text"
          placeholder="Ieškoti pagal SKU ar aprašymą..."
          class="w-full sm:w-64 px-3 py-2 border rounded"
        />

        <select v-model="sort" class="px-2 py-2 border rounded">
          <option value="updated_at">Naujausi</option>
          <option value="sku">SKU</option>
        </select>

        <select v-model="direction" class="px-2 py-2 border rounded">
          <option value="desc">Mažėjančiai</option>
          <option value="asc">Didėjančiai</option>
        </select>
      </div>

      <!-- Populiariausios žymos -->
      <div v-if="popularTags?.length" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-3">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Populiariausios žymos</h3>
          <button
            v-if="tag"
            @click="tag = ''"
            class="text-xs px-2 py-1 rounded border hover:bg-gray-50 dark:hover:bg-gray-800"
            title="Išvalyti žymos filtrą"
          >
            Išvalyti
          </button>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="t in popularTags"
            :key="t.title"
            @click="tag = (tag === t.title ? '' : t.title)"
            class="text-xs px-2 py-1 rounded border"
            :class="tag === t.title ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100'"
            :title="`Produktų: ${t.products_count}`"
          >
            {{ t.title }} <span class="opacity-70">({{ t.products_count }})</span>
          </button>
        </div>
      </div>

      <!-- Tinklelio vaizdas -->
      <div v-if="view === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6 mt-2">
        <div
          v-for="product in products.data"
          :key="product.id"
          class="bg-white dark:bg-gray-900 rounded-xl shadow hover:shadow-lg transition overflow-hidden"
        >
          <img
            :src="product.photo"
            alt="Product photo"
            class="w-full h-40 object-cover"
            @error="e => (e.target.src = '/images/no-image.png')"
          />
          <div class="p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">SKU: {{ product.sku }}</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100 truncate mb-1">
              {{ product.description || 'Be aprašymo' }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
              <span v-for="stock in product.stocks" :key="stock.id" class="mr-1">
                {{ stock.city }}: {{ stock.stock }}
              </span>
            </div>
            <div class="flex flex-wrap gap-1">
              <span
                v-for="tagObj in product.tags"
                :key="tagObj.id"
                class="bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 text-xs px-2 py-0.5 rounded"
              >
                {{ tagObj.title }}
              </span>
            </div>

            <Link
              :href="route('products.show', product.sku)"
              class="mt-3 inline-block text-sm text-indigo-600 hover:underline"
            >
              Peržiūrėti
            </Link>
          </div>
        </div>
      </div>

      <!-- Lentelės vaizdas -->
      <div v-else class="overflow-x-auto rounded-xl bg-white dark:bg-gray-900 shadow-sm">
        <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-800">
          <thead class="bg-gray-100 dark:bg-gray-800 text-left text-xs uppercase text-gray-500 dark:text-gray-300">
            <tr>
              <th class="p-3">Nuotrauka</th>
              <th class="p-3">SKU</th>
              <th class="p-3">Aprašymas</th>
              <th class="p-3">Miestai</th>
              <th class="p-3">Tag’ai</th>
              <th class="p-3">Veiksmai</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="product in products.data"
              :key="product.id"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/40"
            >
              <td class="p-3">
                <img :src="product.photo" class="h-12 w-12 object-cover rounded" @error="e => (e.target.src = '/images/no-image.png')" />
              </td>
              <td class="p-3 font-mono">{{ product.sku }}</td>
              <td class="p-3">{{ product.description || 'Be aprašymo' }}</td>
              <td class="p-3">
                <ul>
                  <li v-for="stock in product.stocks" :key="stock.id">
                    <strong>{{ stock.city }}</strong>: {{ stock.stock }}
                  </li>
                </ul>
              </td>
              <td class="p-3">
                <ul class="flex flex-wrap gap-1">
                  <li v-for="tagObj in product.tags" :key="tagObj.id" class="text-xs bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 px-2 py-0.5 rounded">
                    {{ tagObj.title }}
                  </li>
                </ul>
              </td>
              <td class="p-3">
                <Link :href="route('products.show', product.sku)" class="text-indigo-600 hover:underline text-sm">
                  Peržiūrėti
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Puslapiavimas -->
      <div class="mt-6 flex justify-center gap-1">
        <button
          v-for="(link, idx) in products.links"
          :key="`${link.label}-${idx}`"
          :disabled="!link.url"
          @click="() => goToPage(link.url)"
          class="px-3 py-1 text-sm border rounded"
          :class="{
            'bg-indigo-600 text-white border-indigo-600': link.active,
            'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700': !link.active,
            'opacity-50 cursor-not-allowed': !link.url
          }"
          v-html="link.label"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
