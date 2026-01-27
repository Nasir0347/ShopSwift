import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    {
        path: '/',
        name: 'Home',
        component: () => import('@/views/storefront/Home.vue'),
        meta: { layout: 'StoreLayout' }
    },
    {
        path: '/products',
        name: 'StoreProducts',
        component: () => import('@/views/storefront/Products.vue'),
        meta: { layout: 'StoreLayout' }
    },
    {
        path: '/products/:slug',
        name: 'ProductDetail',
        component: () => import('@/views/storefront/ProductDetail.vue'),
        meta: { layout: 'StoreLayout' }
    },
    {
        path: '/about',
        name: 'About',
        component: () => import('@/views/storefront/About.vue'),
        meta: { layout: 'StoreLayout' }
    },
    {
        path: '/admin',
        name: 'AdminDashboard',
        component: () => import('@/views/admin/Dashboard.vue'),
        meta: { layout: 'AdminLayout' }
    },
    {
        path: '/admin/products',
        name: 'AdminProducts',
        component: () => import('@/views/admin/Products.vue'),
        meta: { layout: 'AdminLayout' }
    },
    {
        path: '/admin/products/create',
        name: 'AdminProductCreate',
        component: () => import('@/views/admin/ProductCreate.vue'),
        meta: { layout: 'AdminLayout', requiresAuth: true }
    },
    {
        path: '/admin/products/:id',
        name: 'AdminProductEdit',
        component: () => import('@/views/admin/ProductCreate.vue'),
        meta: { layout: 'AdminLayout', requiresAuth: true }
    },
    {
        path: '/admin/orders',
        name: 'AdminOrders',
        component: () => import('@/views/admin/Orders.vue'),
        meta: { layout: 'AdminLayout' }
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import('@/views/auth/Login.vue'),
        meta: { layout: 'AuthLayout' }
    },
    {
        path: '/checkout',
        name: 'Checkout',
        component: () => import('@/views/storefront/Checkout.vue'),
        meta: { layout: 'StoreLayout' }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router
