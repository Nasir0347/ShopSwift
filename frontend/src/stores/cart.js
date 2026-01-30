import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCartStore = defineStore('cart', () => {
    const items = ref(JSON.parse(localStorage.getItem('cart_items')) || [])
    const isOpen = ref(false)

    const totalItems = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0))
    const subtotal = computed(() => items.value.reduce((sum, item) => sum + (item.price * item.quantity), 0))

    function addItem(product, variant = null, quantity = 1) {
        const existingItem = items.value.find(item =>
            item.product_id === product.id &&
            item.variant_id === (variant?.id || null)
        )

        if (existingItem) {
            existingItem.quantity += quantity
        } else {
            items.value.push({
                product_id: product.id,
                variant_id: variant?.id || null,
                title: product.title,
                variant_title: variant?.title || null,
                price: parseFloat(variant?.price || product.price || 0), // Use variant price if available
                image: product.image || (product.images && product.images[0]?.image_path) || null,
                quantity: quantity,
                slug: product.slug
            })
        }
        saveCart()
        isOpen.value = true // Open drawer on add
    }

    function removeItem(index) {
        items.value.splice(index, 1)
        saveCart()
    }

    function updateQuantity(index, quantity) {
        if (quantity < 1) return removeItem(index)
        items.value[index].quantity = quantity
        saveCart()
    }

    function clearCart() {
        items.value = []
        saveCart()
    }

    function saveCart() {
        localStorage.setItem('cart_items', JSON.stringify(items.value))
    }

    function toggleCart() {
        isOpen.value = !isOpen.value
    }

    return {
        items,
        isOpen,
        totalItems,
        subtotal,
        addItem,
        removeItem,
        updateQuantity,
        clearCart,
        toggleCart
    }
})
