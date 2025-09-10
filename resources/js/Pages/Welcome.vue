<script setup>
import { Head, Link } from '@inertiajs/vue3'

const { canLogin, canRegister, laravelVersion, phpVersion, products } = defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: { type: String, required: true },
  phpVersion: { type: String, required: true },
  products: { type: Array, default: () => [] }
})

// Paprasta helper funkcija atsarginiam paveikslėliui
const imgSrc = (p) => p?.photo && p.photo.length > 3
  ? p.photo
  : `https://picsum.photos/seed/${p?.id ?? Math.random()}/300/200`
</script>

<template>
  <Head title="Welcome" />

  <div class="min-h-screen bg-gray-50 text-gray-800 dark:bg-black dark:text-gray-100">
    <!-- Header -->
    <header class="border-b border-gray-200/60 dark:border-white/10">
      <div class="mx-auto max-w-7xl px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center justify-center rounded bg-red-500/10 p-2">
            <svg class="h-6 w-6 text-red-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M12 2c2.5 3.5-1 5.5 1.5 7.5S20 12 20 16.5C20 19.5 17.5 22 14.5 22S9 20 9 16.5c0-2.5 1.5-4.5 1.5-6.5S9.5 5 12 2z"/>
            </svg>
          </span>
          <span class="text-lg font-semibold">Products</span>
        </div>

        <nav v-if="canLogin" class="flex items-center gap-3">
          <template v-if="$page.props.auth?.user">
            <Link :href="route('dashboard')" class="px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-white/10 transition">
              Dashboard
            </Link>
          </template>
          <template v-else>
            <Link :href="route('login')" class="px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-white/10 transition">
              Log in
            </Link>
            <Link v-if="canRegister" :href="route('register')" class="px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-white/10 transition">
              Register
            </Link>
          </template>
        </nav>
      </div>
    </header>

    <!-- Hero -->
    <main class="mx-auto max-w-7xl px-6 py-16">
      <section class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
          <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">
            Product Catalog with Real-Time Stock
          </h1>
          <p class="mt-4 text-gray-600 dark:text-gray-300">
            Naršyk produktus, matyk susijusius pasiūlymus ir tikrąjį sandėlio likutį
            realiu laiku. Sukurta naudojant Laravel 12 + Inertia + Vue.
          </p>

          <div class="mt-6 flex flex-wrap items-center gap-3">
            <Link :href="route('products.index')" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-blue-600 text-white hover:bg-blue-700 shadow transition">
              Browse Products
            </Link>

            <a href="/api/info" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md border border-gray-300 dark:border-white/20 hover:bg-gray-100 dark:hover:bg-white/10 transition">
              Public JSON API
            </a>
          </div>

          <div class="mt-8 grid grid-cols-3 gap-4 text-sm">
            <div class="rounded-lg border border-gray-200 dark:border-white/10 p-4">
              <div class="text-xs text-gray-500 dark:text-gray-400">Framework</div>
              <div class="mt-1 font-medium">Laravel v{{ laravelVersion }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 dark:border-white/10 p-4">
              <div class="text-xs text-gray-500 dark:text-gray-400">PHP</div>
              <div class="mt-1 font-medium">v{{ phpVersion }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 dark:border-white/10 p-4">
              <div class="text-xs text-gray-500 dark:text-gray-400">Stack</div>
              <div class="mt-1 font-medium">Inertia + Vue + Vite</div>
            </div>
          </div>
        </div>

        <div class="relative">
          <div class="aspect-video w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white/60 dark:bg-white/5 backdrop-blur-sm p-6">
            <h2 class="text-2xl font-bold mb-6">Random Products</h2>

            <!-- Grid: responsyvus stulpelių skaičius -->
            <div class="h-full w-full grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <div
                v-for="p in products"
                :key="p.id"
                class="border rounded overflow-hidden bg-white/60 dark:bg-white/5"
            >
                <!-- Vaizdo konteineris su fiksuotu santykiu -->
                <div class="relative aspect-[4/3] w-full">
                <img
                    :src="p.photo || `https://picsum.photos/seed/${p.id}/600/450`"
                    alt="Product photo"
                    class="absolute inset-0 w-full h-full object-cover"
                    loading="lazy"
                />
                </div>

                <!-- Apatinė dalis -->
                <div class="p-2 text-center text-sm font-medium truncate">
                {{ p.sku ?? ('Product #' + p.id) }}
                </div>
            </div>
            </div>

          </div>
          <div class="pointer-events-none absolute -inset-3 -z-10 rounded-2xl bg-gradient-to-tr from-blue-500/10 to-purple-500/10"></div>
        </div>
      </section>

      <!-- Quick links -->
      <section class="mt-16 grid gap-6 sm:grid-cols-2">
        <a :href="route('products.index')" class="block rounded-lg border border-gray-200 dark:border-white/10 p-6 hover:shadow transition">
          <h3 class="font-semibold">All Products</h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            Peržiūrėk visus produktus su paginacija ir filtrais.
          </p>
        </a>

        <a href="/api/info" class="block rounded-lg border border-gray-200 dark:border-white/10 p-6 hover:shadow transition">
          <h3 class="font-semibold">Public JSON API</h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            JSON endpoint’as, kurį gali naudoti integracijoms ar testams.
          </p>
        </a>
      </section>
    </main>

    <footer class="mx-auto max-w-7xl px-6 py-10 text-sm text-gray-500 dark:text-gray-400">
      © {{ new Date().getFullYear() }} Products Demo. Built with Laravel & Vue.
    </footer>
  </div>
</template>
