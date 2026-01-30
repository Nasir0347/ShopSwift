<template>
  <span 
    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset"
    :class="variantClasses[variant]"
  >
    <slot></slot>
  </span>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'neutral',
    validator: (value) => ['neutral', 'success', 'warning', 'critical', 'info'].includes(value)
  }
})

const variantClasses = {
  neutral: 'bg-surface-subdued text-content-subdued ring-border',
  success: 'bg-success-subdued text-success ring-success/20',
  warning: 'bg-warning-subdued text-warning-700 ring-warning/20', // warning-700 for better contrast
  critical: 'bg-critical-subdued text-critical ring-critical/20',
  info: 'bg-blue-50 text-blue-700 ring-blue-700/10'
}
</script>
