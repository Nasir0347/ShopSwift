import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
    const toasts = ref([])
    let nextId = 1

    const addToast = (message, type = 'info', duration = 3000) => {
        const id = nextId++
        const toast = { id, message, type }

        toasts.value.push(toast)

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id)
            }, duration)
        }

        return id
    }

    const removeToast = (id) => {
        const index = toasts.value.findIndex(t => t.id === id)
        if (index > -1) {
            toasts.value.splice(index, 1)
        }
    }

    const success = (message, duration = 3000) => {
        return addToast(message, 'success', duration)
    }

    const error = (message, duration = 5000) => {
        return addToast(message, 'error', duration)
    }

    const warning = (message, duration = 4000) => {
        return addToast(message, 'warning', duration)
    }

    const info = (message, duration = 3000) => {
        return addToast(message, 'info', duration)
    }

    const clear = () => {
        toasts.value = []
    }

    return {
        toasts,
        addToast,
        removeToast,
        success,
        error,
        warning,
        info,
        clear
    }
})
