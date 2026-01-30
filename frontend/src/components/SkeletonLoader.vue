<template>
  <div class="skeleton-loader">
    <div class="animate-pulse">
      <!-- Product Card Skeleton -->
      <div v-if="type === 'product-card'" class="bg-white rounded-lg overflow-hidden shadow">
        <div class="bg-gray-200 h-64"></div>
        <div class="p-4 space-y-3">
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          <div class="h-4 bg-gray-200 rounded w-1/2"></div>
          <div class="h-4 bg-gray-200 rounded w-1/4"></div>
        </div>
      </div>

      <!-- Product Grid Skeleton -->
      <div v-else-if="type === 'product-grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div v-for="n in count" :key="n" class="bg-white rounded-lg overflow-hidden shadow">
          <div class="bg-gray-200 h-64"></div>
          <div class="p-4 space-y-3">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
          </div>
        </div>
      </div>

      <!-- Table Row Skeleton -->
      <div v-else-if="type === 'table-row'" class="space-y-2">
        <div v-for="n in count" :key="n" class="flex items-center gap-4 p-4 bg-white border-b">
          <div class="h-12 w-12 bg-gray-200 rounded"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
            <div class="h-3 bg-gray-200 rounded w-1/3"></div>
          </div>
          <div class="h-8 bg-gray-200 rounded w-20"></div>
        </div>
      </div>

      <!-- Text Lines Skeleton -->
      <div v-else-if="type === 'text'" class="space-y-3">
        <div v-for="n in count" :key="n" class="h-4 bg-gray-200 rounded" :class="n === count ? 'w-2/3' : 'w-full'"></div>
      </div>

      <!-- Default: Simple Box -->
      <div v-else class="h-64 bg-gray-200 rounded"></div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'box',
    validator: (value) => ['product-card', 'product-grid', 'table-row', 'text', 'box'].includes(value)
  },
  count: {
    type: Number,
    default: 1
  }
})
</script>

<style scoped>
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
