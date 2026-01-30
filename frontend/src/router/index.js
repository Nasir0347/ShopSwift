import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    {
        path: '/',
        name: 'Home',
        component: () => import('@/views/shop/HomeView.vue'),
        meta: { layout: 'ShopLayout' }
    },
    {
        path: '/catalog',
        name: 'Catalog',
        component: () => import('@/views/shop/ProductListView.vue'),
        meta: { layout: 'ShopLayout' }
    },
    {
        path: '/products/:id',
        name: 'ProductDetail',
        component: () => import('@/views/shop/ProductDetailView.vue'),
        meta: { layout: 'ShopLayout' }
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
        path: '/admin/orders/:id',
        name: 'AdminOrderDetails',
        component: () => import('@/views/admin/OrderDetails.vue'),
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
        meta: { layout: 'ShopLayout' }
    },
    {
        path: '/order-confirmation/:id',
        name: 'OrderConfirmation',
        component: () => import('@/views/storefront/OrderConfirmation.vue'),
        meta: { layout: 'ShopLayout' }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router
