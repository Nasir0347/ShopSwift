import { defineStore } from 'pinia'
import api from '@/composables/useApi'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        loading: false,
        error: null
    }),

    getters: {
        isAuthenticated: (state) => !!state.user,
        isAdmin: (state) => state.user?.role === 'super_admin' || state.user?.role === 'admin'
    },

    actions: {
        async getUser() {
            try {
                const response = await api.get('/me')
                this.user = response.data.data
            } catch (error) {
                // If 401, clear token
                if (error.response && error.response.status === 401) {
                    this.user = null
                    localStorage.removeItem('token')
                }
            }
        },

        async login(credentials) {
            this.loading = true
            this.error = null
            try {
                // No CSRF cookie needed for token interaction
                const response = await api.post('/login', credentials)

                // Save token
                const token = response.data.data.token
                const user = response.data.data.user

                localStorage.setItem('token', token)
                this.user = user

                return true
            } catch (err) {
                this.error = err.response?.data?.message || 'Login failed'
                return false
            } finally {
                this.loading = false
            }
        },

        async logout() {
            try {
                await api.post('/logout')
            } catch (e) {
                // Ignore logout errors
            } finally {
                this.user = null
                this.error = null
                localStorage.removeItem('token')
            }
        },

        // Initialize state from local storage
        initialize() {
            const token = localStorage.getItem('token')
            if (token) {
                this.getUser()
            }
        }
    }
})
