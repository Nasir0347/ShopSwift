<script setup>
import { useCartStore } from '@/stores/cart'
import { XMarkIcon, MinusIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const cart = useCartStore()

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price || 0)
}
</script>

<template>
    <div v-if="cart.isOpen" class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="cart.toggleCart"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div class="pointer-events-auto w-screen max-w-md">
                        <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                            <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
                                <div class="flex items-start justify-between">
                                    <h2 class="text-lg font-medium text-gray-900" id="slide-over-title">Shopping cart</h2>
                                    <div class="ml-3 flex h-7 items-center">
                                        <button type="button" class="-m-2 p-2 text-gray-400 hover:text-gray-500" @click="cart.toggleCart">
                                            <span class="sr-only">Close panel</span>
                                            <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-8">
                                    <div class="flow-root">
                                        <ul role="list" class="-my-6 divide-y divide-gray-200">
                                            <li v-for="(item, index) in cart.items" :key="index" class="flex py-6">
                                                <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                                    <img :src="item.image || 'https://via.placeholder.com/150'" :alt="item.title" class="h-full w-full object-cover object-center" />
                                                </div>

                                                <div class="ml-4 flex flex-1 flex-col">
                                                    <div>
                                                        <div class="flex justify-between text-base font-medium text-gray-900">
                                                            <h3>
                                                                <a :href="`/products/${item.slug}`">{{ item.title }}</a>
                                                            </h3>
                                                            <p class="ml-4">{{ formatPrice(item.price * item.quantity) }}</p>
                                                        </div>
                                                        <p class="mt-1 text-sm text-gray-500">{{ item.variant_title }}</p>
                                                    </div>
                                                    <div class="flex flex-1 items-end justify-between text-sm">
                                                        <div class="flex items-center space-x-2">
                                                             <button @click="cart.updateQuantity(index, item.quantity - 1)" class="p-1 rounded-md hover:bg-gray-100">
                                                                <MinusIcon class="h-4 w-4 text-gray-500" />
                                                             </button>
                                                             <span class="font-medium text-gray-900">{{ item.quantity }}</span>
                                                             <button @click="cart.updateQuantity(index, item.quantity + 1)" class="p-1 rounded-md hover:bg-gray-100">
                                                                <PlusIcon class="h-4 w-4 text-gray-500" />
                                                             </button>
                                                        </div>

                                                        <div class="flex">
                                                            <button type="button" @click="cart.removeItem(index)" class="font-medium text-indigo-600 hover:text-indigo-500">Remove</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                             <li v-if="cart.items.length === 0" class="py-12 text-center text-gray-500">
                                                Your cart is empty.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 px-4 py-6 sm:px-6">
                                <div class="flex justify-between text-base font-medium text-gray-900">
                                    <p>Subtotal</p>
                                    <p>{{ formatPrice(cart.subtotal) }}</p>
                                </div>
                                <p class="mt-0.5 text-sm text-gray-500">Shipping and taxes calculated at checkout.</p>
                                <div class="mt-6">
                                    <router-link to="/checkout" @click="cart.toggleCart" class="flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700">Checkout</router-link>
                                </div>
                                <div class="mt-6 flex justify-center text-center text-sm text-gray-500">
                                    <p>
                                        or
                                        <button type="button" class="font-medium text-indigo-600 hover:text-indigo-500" @click="cart.toggleCart">
                                            Continue Shopping
                                            <span aria-hidden="true"> &rarr;</span>
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
