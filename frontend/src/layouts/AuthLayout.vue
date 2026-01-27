<script setup>
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-slate-900">
          <slot name="title">ShopSwift</slot>
        </h2>
        <p class="mt-2 text-center text-sm text-slate-600">
          <slot name="subtitle"></slot>
        </p>
      </div>
      <slot />
    </div>
  </div>
</template>
