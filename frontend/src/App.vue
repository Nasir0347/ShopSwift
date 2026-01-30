<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useRoute } from 'vue-router'
import Toast from '@/components/Toast.vue'

const route = useRoute()

const AdminLayout = defineAsyncComponent(() => import('@/layouts/AdminLayout.vue'))
const ShopLayout = defineAsyncComponent(() => import('@/layouts/ShopLayout.vue'))
const AuthLayout = defineAsyncComponent(() => import('@/layouts/AuthLayout.vue'))

const layout = computed(() => {
  const layoutName = route.meta.layout
  if (layoutName === 'AdminLayout') return AdminLayout
  if (layoutName === 'AuthLayout') return AuthLayout
  return ShopLayout
})
</script>

<template>
  <component :is="layout">
    <router-view />
  </component>
  
  <!-- Global Toast Notifications -->
  <Toast />
</template>
