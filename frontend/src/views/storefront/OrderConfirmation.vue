<script setup>
import { useRoute } from 'vue-router'
import { CheckCircleIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const orderId = route.params.id
</script>

<template>
    <div class="bg-white min-h-full px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <div class="max-w-xl">
                <h1 class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Thank you!</h1>
                <p class="mt-2 text-4xl font-bold tracking-tight sm:text-5xl">It's on the way!</p>
                <p class="mt-2 text-base text-gray-500">Your order #{{ orderId }} has processed and will be with you soon.</p>

                <div class="mt-10 border-t border-gray-200 pt-10">
                    <router-link to="/" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Continue Shopping <span aria-hidden="true"> &rarr;</span>
                    </router-link>
                </div>
            </div>
             
             <div class="mt-10 flex justify-center">
                 <CheckCircleIcon class="h-48 w-48 text-green-500 opacity-20" />
             </div>
        </div>
    </div>
</template>
