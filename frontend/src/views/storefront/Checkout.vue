<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import api from '@/composables/useApi'

const router = useRouter()
const cart = useCartStore()

const loading = ref(false)
const error = ref(null)

const form = ref({
    email: '',
    first_name: '',
    last_name: '',
    address1: '',
    address2: '',
    city: '',
    province: '',
    zip: '',
    country: 'United States',
    phone: ''
})

const paymentMethod = ref('credit_card')

const subtotal = computed(() => cart.subtotal)
// Simple mock shipping/tax calculation
const shipping = computed(() => subtotal.value > 100 ? 0 : 10)
const tax = computed(() => subtotal.value * 0.08)
const total = computed(() => subtotal.value + shipping.value + tax.value)

const placeOrder = async () => {
    loading.value = true
    error.value = null

    try {
        const payload = {
            items: cart.items.map(item => ({
                product_variant_id: item.variant_id || item.product_id, // Fallback if no variant ID but usually required
                quantity: item.quantity,
                price: item.price
            })),
            shipping_address: {
                first_name: form.value.first_name,
                last_name: form.value.last_name,
                address1: form.value.address1,
                address2: form.value.address2,
                city: form.value.city,
                province: form.value.province,
                zip: form.value.zip,
                country: form.value.country,
                phone: form.value.phone
            },
            payment_method: paymentMethod.value,
            // discount_code: ...
        }

        const response = await api.post('/orders', payload)
        
        // Success
        cart.clearCart()
        const orderId = response.data.order ? response.data.order.id : (response.data.data ? response.data.data.id : null)
        
        if (orderId) {
            router.push(`/order-confirmation/${orderId}`)
        } else {
            // Fallback if ID finding fails (should not happen with standard response)
            router.push('/')
        }

    } catch (e) {
        console.error('Checkout failed', e)
        error.value = e.response?.data?.message || 'Failed to place order. Please check your details.'
        // If validation errors
        if (e.response?.data?.errors) {
            error.value += ' ' + Object.values(e.response.data.errors).flat().join(', ')
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="bg-gray-50 min-h-screen pb-12">
        <div class="mx-auto max-w-7xl px-4 pt-16 pb-24 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Checkout</h1>
            
            <div class="mt-12 lg:grid lg:grid-cols-12 lg:items-start lg:gap-x-12 xl:gap-x-16">
                <!-- Order Summary (Right Column on Desktop, Top on Mobile typically but here logic order) -->
                <section aria-labelledby="cart-heading" class="lg:col-span-5 lg:col-start-8">
                    <div class="rounded-lg bg-white px-4 py-6 shadow-sm sm:px-6 lg:px-8">
                        <h2 id="cart-heading" class="text-lg font-medium text-gray-900">Order Summary</h2>
                        <ul role="list" class="divide-y divide-gray-200 mt-6">
                            <li v-for="item in cart.items" :key="item.variant_id || item.product_id" class="flex py-6">
                                <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                    <img :src="item.image" :alt="item.title" class="h-full w-full object-cover object-center" />
                                </div>
                                <div class="ml-4 flex flex-1 flex-col">
                                    <div>
                                        <div class="flex justify-between text-base font-medium text-gray-900">
                                            <h3>{{ item.title }}</h3>
                                            <p class="ml-4">${{ (item.price * item.quantity).toFixed(2) }}</p>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">{{ item.variant_title }}</p>
                                        <p class="mt-1 text-sm text-gray-500">Qty {{ item.quantity }}</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                         <dl class="mt-10 space-y-6 text-sm font-medium text-gray-500 border-t border-gray-200 pt-10">
                            <div class="flex justify-between">
                                <dt>Subtotal</dt>
                                <dd class="text-gray-900">${{ subtotal.toFixed(2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Shipping</dt>
                                <dd class="text-gray-900">{{ shipping === 0 ? 'Free' : '$' + shipping.toFixed(2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Taxes</dt>
                                <dd class="text-gray-900">${{ tax.toFixed(2) }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-6 text-base">
                                <dt class="text-gray-900">Total</dt>
                                <dd class="text-gray-900">${{ total.toFixed(2) }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <!-- Forms (Left Column) -->
                <section aria-labelledby="payment-heading" class="mt-16 lg:col-span-7 lg:mt-0">
                    <form @submit.prevent="placeOrder">
                        <!-- Contact Info -->
                         <div class="border-b border-gray-200 pb-10">
                            <h2 class="text-lg font-medium text-gray-900">Contact Information</h2>
                            <div class="mt-4">
                                <label for="email-address" class="block text-sm font-medium text-gray-700">Email address</label>
                                <div class="mt-1">
                                    <input type="email" id="email-address" v-model="form.email" autocomplete="email" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border" required />
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="mt-10 border-b border-gray-200 pb-10">
                            <h2 class="text-lg font-medium text-gray-900">Shipping Address</h2>
                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="first-name" class="block text-sm font-medium text-gray-700">First name</label>
                                    <div class="mt-1">
                                        <input type="text" id="first-name" v-model="form.first_name" autocomplete="given-name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border" required />
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="last-name" class="block text-sm font-medium text-gray-700">Last name</label>
                                    <div class="mt-1">
                                        <input type="text" id="last-name" v-model="form.last_name" autocomplete="family-name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border" required />
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                    <div class="mt-1">
                                        <input type="text" id="address" v-model="form.address1" autocomplete="street-address" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border" required />
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="apartment" class="block text-sm font-medium text-gray-700">Apartment, suite, etc.</label>
                                    <div class="mt-1">
                                        <input type="text" id="apartment" v-model="form.address2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border" />
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                    <div class="mt-1">
                                        <input type="text" id="city" v-model="form.city" autocomplete="address-level2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border" required />
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="region" class="block text-sm font-medium text-gray-700">State / Province</label>
                                    <div class="mt-1">
                                        <input type="text" id="region" v-model="form.province" autocomplete="address-level1" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border" />
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="postal-code" class="block text-sm font-medium text-gray-700">ZIP / Postal code</label>
                                    <div class="mt-1">
                                        <input type="text" id="postal-code" v-model="form.zip" autocomplete="postal-code" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Payment -->
                         <div class="mt-10 border-b border-gray-200 pb-10">
                            <h2 class="text-lg font-medium text-gray-900">Payment</h2>
                            <fieldset class="mt-4">
                                <legend class="sr-only">Payment type</legend>
                                <div class="space-y-4">
                                     <div class="flex items-center">
                                        <input id="credit-card" name="payment-type" type="radio" value="credit_card" v-model="paymentMethod" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500" checked />
                                        <label for="credit-card" class="ml-3 block text-sm font-medium text-gray-700">Credit card (Simulated)</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="paypal" name="payment-type" type="radio" value="paypal" v-model="paymentMethod" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        <label for="paypal" class="ml-3 block text-sm font-medium text-gray-700">PayPal (Simulated)</label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        
                        <div v-if="error" class="mt-4 p-4 rounded-md bg-red-50 text-red-700 text-sm">
                            {{ error }}
                        </div>

                        <div class="mt-10 border-t border-gray-200 pt-6 flex justify-end">
                            <button type="submit" :disabled="loading" class="rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50 disabled:opacity-50">
                                {{ loading ? 'Processing...' : 'Pay now' }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</template>
