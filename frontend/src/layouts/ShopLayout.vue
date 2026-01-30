<script setup>
import { RouterView, RouterLink } from 'vue-router'
import { ShoppingBagIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { useCartStore } from '@/stores/cart'
import CartDrawer from '@/components/shop/CartDrawer.vue'

const cart = useCartStore()
</script>

<template>
  <div class="min-h-screen flex flex-col bg-white">
    <!-- Navigation -->
    <header class="sticky top-0 z-40 bg-white border-b border-gray-200">
      <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <!-- Logo -->
          <div class="flex lg:min-w-0 lg:flex-1">
            <RouterLink to="/" class="-m-1.5 p-1.5 text-2xl font-bold tracking-tight text-gray-900">
              ShopSwift
            </RouterLink>
          </div>

          <!-- Links (Desktop) -->
          <div class="hidden lg:flex lg:gap-x-8">
            <RouterLink to="/" class="text-sm font-semibold leading-6 text-gray-900 hover:text-indigo-600">Home</RouterLink>
            <RouterLink to="/catalog" class="text-sm font-semibold leading-6 text-gray-900 hover:text-indigo-600">Catalog</RouterLink>
            <RouterLink to="/about" class="text-sm font-semibold leading-6 text-gray-900 hover:text-indigo-600">About</RouterLink>
          </div>

          <!-- Icons -->
          <div class="flex flex-1 items-center justify-end gap-x-6">
            <a href="#" class="text-gray-400 hover:text-gray-500">
              <span class="sr-only">Search</span>
              <MagnifyingGlassIcon class="h-6 w-6" aria-hidden="true" />
            </a>

            <div class="flow-root relative">
              <button @click="cart.toggleCart" class="group -m-2 flex items-center p-2">
                <ShoppingBagIcon class="h-6 w-6 flex-shrink-0 text-gray-400 group-hover:text-gray-500" aria-hidden="true" />
                <span class="ml-2 text-sm font-medium text-gray-700 group-hover:text-gray-800">{{ cart.totalItems }}</span>
                <span class="sr-only">items in cart, view bag</span>
              </button>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
      <RouterView />
    </main>

    <!-- Cart Drawer -->
    <CartDrawer />

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200">
      <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <p class="text-center text-xs leading-5 text-gray-500">&copy; 2026 ShopSwift, Inc. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>
