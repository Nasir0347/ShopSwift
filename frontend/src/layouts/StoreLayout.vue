<script setup>
import { ref } from 'vue'
import { Dialog, DialogPanel } from '@headlessui/vue'
import { Bars3Icon, XMarkIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline'
import { useCartStore } from '@/stores/cart'
import CartDrawer from '@/components/storefront/CartDrawer.vue'

const mobileMenuOpen = ref(false)
const cartStore = useCartStore()
</script>

<template>
  <div class="bg-white">
    <CartDrawer />
    <header class="absolute inset-x-0 top-0 z-50">
      <nav class="flex items-center justify-between p-6 lg:px-8" aria-label="Global">
        <div class="flex lg:flex-1">
          <router-link to="/" class="-m-1.5 p-1.5">
            <span class="sr-only">ShopSwift</span>
            <span class="text-xl font-bold text-slate-900">ShopSwift</span>
          </router-link>
        </div>
        <div class="flex lg:hidden">
          <button type="button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700" @click="mobileMenuOpen = true">
            <span class="sr-only">Open main menu</span>
            <Bars3Icon class="h-6 w-6" aria-hidden="true" />
          </button>
        </div>
        <div class="hidden lg:flex lg:gap-x-12">
          <router-link to="/" class="text-sm font-semibold leading-6 text-gray-900">Home</router-link>
          <router-link to="/products" class="text-sm font-semibold leading-6 text-gray-900">Products</router-link>
          <router-link to="/about" class="text-sm font-semibold leading-6 text-gray-900">About</router-link>
        </div>
        <div class="hidden lg:flex lg:flex-1 lg:justify-end gap-x-4">
          <a href="/login" class="text-sm font-semibold leading-6 text-gray-900">Log in</a>
          <button class="text-gray-900 relative" @click="cartStore.isOpen = true">
             <ShoppingBagIcon class="h-6 w-6" />
             <span v-if="cartStore.totalItems > 0" class="absolute -top-2 -right-2 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">{{ cartStore.totalItems }}</span>
          </button>
        </div>
      </nav>
      <Dialog as="div" class="lg:hidden" @close="mobileMenuOpen = false" :open="mobileMenuOpen">
        <div class="fixed inset-0 z-50" />
        <DialogPanel class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-white px-6 py-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10">
          <div class="flex items-center justify-between">
            <a href="#" class="-m-1.5 p-1.5">
              <span class="sr-only">ShopSwift</span>
              <span class="text-xl font-bold">ShopSwift</span>
            </a>
            <button type="button" class="-m-2.5 rounded-md p-2.5 text-gray-700" @click="mobileMenuOpen = false">
              <span class="sr-only">Close menu</span>
              <XMarkIcon class="h-6 w-6" aria-hidden="true" />
            </button>
          </div>
          <div class="mt-6 flow-root">
            <div class="-my-6 divide-y divide-gray-500/10">
              <div class="space-y-2 py-6">
                <router-link to="/" class="-mx-3 block rounded-lg px-3 py-2 text-base font-semibold leading-7 text-gray-900 hover:bg-gray-50">Home</router-link>
                <router-link to="/products" class="-mx-3 block rounded-lg px-3 py-2 text-base font-semibold leading-7 text-gray-900 hover:bg-gray-50">Products</router-link>
              </div>
              <div class="py-6">
                <a href="/login" class="-mx-3 block rounded-lg px-3 py-2.5 text-base font-semibold leading-7 text-gray-900 hover:bg-gray-50">Log in</a>
              </div>
            </div>
          </div>
        </DialogPanel>
      </Dialog>
    </header>

    <div class="relative isolate pt-14">
      <main>
        <slot />
      </main>
    </div>
  </div>
</template>
