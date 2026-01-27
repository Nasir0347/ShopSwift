<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'outline', 'danger'].includes(value)
  },
  block: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  to: {
    type: [String, Object],
    default: null
  }
})

const variants = {
  primary: 'bg-slate-900 text-white hover:bg-slate-800 focus:ring-slate-500',
  secondary: 'bg-white text-slate-700 border border-gray-300 hover:bg-gray-50 focus:ring-slate-500 shadow-sm',
  outline: 'border border-slate-300 text-slate-700 hover:bg-gray-50 focus:ring-slate-500',
  danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500'
}
</script>

<template>
  <component
    :is="to ? 'router-link' : 'button'"
    :to="to"
    :class="[
      'inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200',
      variants[variant],
      block ? 'w-full' : '',
      loading ? 'opacity-75 cursor-not-allowed' : ''
    ]"
    :disabled="loading"
  >
    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <slot />
  </component>
</template>
