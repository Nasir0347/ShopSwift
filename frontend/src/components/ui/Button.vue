<template>
  <button 
    :class="[
      'inline-flex items-center justify-center rounded transition-all duration-200 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-1',
      variantClasses[variant],
      sizeClasses[size],
      { 'opacity-50 cursor-not-allowed': disabled || loading }
    ]" 
    :disabled="disabled || loading"
    @click="$emit('click')"
  >
    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <slot name="prefix"></slot>
    <slot></slot>
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'plain', 'critical', 'outline'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  disabled: Boolean,
  loading: Boolean
})

const variantClasses = {
  primary: 'bg-primary text-white hover:bg-primary-hover shadow-sm border border-transparent focus:ring-primary',
  secondary: 'bg-white text-content border border-border shadow-sm hover:bg-surface-hover focus:ring-border',
  outline: 'bg-transparent text-content border border-border hover:bg-surface-hover',
  plain: 'bg-transparent text-primary hover:underline px-0',
  critical: 'bg-critical text-white hover:bg-red-700 shadow-sm focus:ring-critical'
}

const sizeClasses = {
  sm: 'px-3 py-1.5 text-xs',
  md: 'px-4 py-2',
  lg: 'px-6 py-3 text-base'
}
</script>
