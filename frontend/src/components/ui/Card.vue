<template>
  <div class="bg-surface rounded-lg shadow-card border border-border overflow-hidden">
    <div v-if="$slots.header || title" class="px-4 py-3 border-b border-border flex justify-between items-center bg-white">
      <h3 v-if="title" class="text-sm font-semibold text-content">{{ title }}</h3>
      <slot name="header"></slot>
    </div>
    <div class="p-4" :class="bodyClass">
      <slot></slot>
    </div>
    <div v-if="$slots.footer" class="px-4 py-3 border-t border-border bg-surface-subdued">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  },
  bodyClass: {
    type: String,
    default: ''
  }
})
</script>
