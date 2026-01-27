import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCartStore = defineStore('cart', () => {
    const items = ref([])
    const isOpen = ref(false)

    const totalItems = computed(() => items.value.reduce((total, item) => total + item.quantity, 0))
    const subtotal = computed(() => items.value.reduce((total, item) => total + (item.price * item.quantity), 0))

    function addItem(product) {
        const existingItem = items.value.find(item => item.id === product.id)
        if (existingItem) {
            existingItem.quantity++
        } else {
            items.value.push({ ...product, quantity: 1 })
        }
        isOpen.value = true
    }

    function removeItem(productId) {
        const index = items.value.findIndex(item => item.id === productId)
        if (index > -1) {
            items.value.splice(index, 1)
        }
    }

    function updateQuantity(productId, quantity) {
        const item = items.value.find(item => item.id === productId)
        if (item) {
            item.quantity = quantity
            if (item.quantity <= 0) {
                removeItem(productId)
            }
        }
    }

    return {
        items,
        isOpen,
        totalItems,
        subtotal,
        addItem,
        removeItem,
        updateQuantity
    }
})
