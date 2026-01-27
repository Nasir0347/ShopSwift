<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const AdminLayout = defineAsyncComponent(() => import('@/layouts/AdminLayout.vue'))
const StoreLayout = defineAsyncComponent(() => import('@/layouts/StoreLayout.vue'))
const AuthLayout = defineAsyncComponent(() => import('@/layouts/AuthLayout.vue'))

const layout = computed(() => {
  const layoutName = route.meta.layout
  if (layoutName === 'AdminLayout') return AdminLayout
  if (layoutName === 'AuthLayout') return AuthLayout
  return StoreLayout
})
</script>

<template>
  <component :is="layout">
    <router-view />
  </component>
</template>
