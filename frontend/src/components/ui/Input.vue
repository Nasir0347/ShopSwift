<template>
  <div class="w-full">
    <label v-if="label" class="block text-sm font-medium text-content mb-1">
      {{ label }} <span v-if="required" class="text-critical">*</span>
    </label>
    <div class="relative rounded-md shadow-sm">
      <div v-if="$slots.prefix" class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <slot name="prefix"></slot>
      </div>
      <input
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="[
          'block w-full rounded border-border py-2 text-content placeholder-content-subdued focus:border-primary focus:ring-primary sm:text-sm',
          { 'pl-10': $slots.prefix, 'border-critical focus:border-critical focus:ring-critical': error }
        ]"
      />
      <div v-if="error" class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
        <svg class="h-5 w-5 text-critical" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
        </svg>
      </div>
    </div>
    <p v-if="error" class="mt-1 text-sm text-critical">{{ error }}</p>
    <p v-if="helpText && !error" class="mt-1 text-sm text-content-subdued">{{ helpText }}</p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: [String, Number],
  label: String,
  type: {
    type: String,
    default: 'text'
  },
  placeholder: String,
  required: Boolean,
  disabled: Boolean,
  error: String,
  helpText: String
})

defineEmits(['update:modelValue'])
</script>
