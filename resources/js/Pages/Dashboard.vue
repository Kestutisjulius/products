<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  stats: Object,
});
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
        Valdymo skydas
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <StatCard title="Produktų" :value="props.stats.products" />
          <StatCard title="Vartotojų" :value="props.stats.users" />
          <StatCard title="Sandėlio įrašų" :value="props.stats.stockEntries" />
          <StatCard title="Viso likutis" :value="props.stats.totalStock" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
