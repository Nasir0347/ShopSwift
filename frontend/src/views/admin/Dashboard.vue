<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  ArrowUpIcon, 
  ArrowDownIcon, 
  CurrencyDollarIcon,
  ShoppingCartIcon,
  UsersIcon,
  EllipsisHorizontalIcon,
  CalendarIcon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'
import BaseCard from '@/components/ui/BaseCard.vue'
import api from '@/composables/useApi'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'


ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const stats = ref([
  { name: 'Total Sales', stat: '$0.00', change: '0%', changeType: 'neutral', icon: CurrencyDollarIcon },
  { name: 'Total Orders', stat: '0', change: '0%', changeType: 'neutral', icon: ShoppingCartIcon },
  { name: 'Avg. Order Value', stat: '$0.00', change: '0%', changeType: 'neutral', icon: CurrencyDollarIcon }, // Using dollar icon again or maybe something else
])

const recentOrders = ref([])
const activities = ref([])
const router = useRouter()

const chartData = ref({
  labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
  datasets: []
})

// Shopify-like clean chart options
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      mode: 'index',
      intersect: false,
      backgroundColor: '#fff',
      titleColor: '#1f2937',
      bodyColor: '#1f2937',
      borderColor: '#e5e7eb',
      borderWidth: 1,
      padding: 12,
      cornerRadius: 8,
      displayColors: false,
      callbacks: {
        label: function(context) {
          let label = context.dataset.label || '';
          if (label) {
            label += ': ';
          }
          if (context.parsed.y !== null) {
            label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
          }
          return label;
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { 
        borderDash: [4, 4], 
        color: '#f3f4f6', 
        drawBorder: false 
      },
      ticks: { 
        callback: (value) => '$' + value,
        font: { size: 11 },
        color: '#6b7280'
      }
    },
    x: { 
      grid: { display: false },
      ticks: {
        font: { size: 11 },
        color: '#6b7280'
      }
    }
  },
  elements: {
    line: {
      tension: 0.4 // Smooth curves
    }
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: 'numeric'
  })
}

onMounted(async () => {
  try {
    const response = await api.get('/orders')
    const orders = response.data.orders.data || []
    
    // Sort orders by date desc
    orders.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))

    // Analytics Calculation
    const totalRevenue = orders.reduce((sum, order) => sum + parseFloat(order.total_amount), 0)
    const totalOrdersCount = orders.length
    const avgOrderValue = totalOrdersCount > 0 ? totalRevenue / totalOrdersCount : 0

    // Update Stats
    stats.value = [
      { 
        name: 'Total Sales', 
        stat: '$' + totalRevenue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }), 
        change: '+12.5%', 
        changeType: 'increase',
        icon: CurrencyDollarIcon
      },
      { 
        name: 'Total Orders', 
        stat: totalOrdersCount.toString(), 
        change: '+8.1%', 
        changeType: 'increase',
        icon: ShoppingCartIcon
      },
       { 
        name: 'Avg. Order Value', 
        stat: '$' + avgOrderValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }), 
        change: '+4.2%', 
        changeType: 'increase',
        icon: UsersIcon 
      },
    ]

    // Set Recent Orders (Top 5)
    recentOrders.value = orders.slice(0, 5)

    // Build Activity Feed from Orders
    activities.value = orders.slice(0, 10).map(order => ({
      id: order.id,
      type: 'order',
      content: `Order #${order.id} placed by ${order.user ? order.user.name : 'Guest'}`,
      amount: '$' + parseFloat(order.total_amount).toFixed(2),
      date: order.created_at,
      status: order.status
    }))

    // Chart Data (Mocking daily distribution based on total)
    // In a real app, we'd group orders by date
    const dailyData = [0, 0, 0, 0, 0, 0, 0]
    // Distribute total revenue somewhat randomly for visualization or 0 if empty
    if (totalRevenue > 0) {
        const base = totalRevenue / 7
        dailyData.fill(base)
        // Add some noise
        dailyData.forEach((val, i) => dailyData[i] = val + (Math.random() * val * 0.5 - val * 0.25))
    }

    chartData.value = {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      datasets: [
        {
          label: 'Sales',
          backgroundColor: (context) => {
            const ctx = context.chart.ctx;
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)'); // Emerald-500 low opacity
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
            return gradient;
          },
          borderColor: '#10b981', // Emerald-500
          pointBackgroundColor: '#ffffff',
          pointBorderColor: '#10b981',
          pointBorderWidth: 2,
          borderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          fill: true,
          data: dailyData 
        }
      ]
    }
  } catch (error) {
    console.error('Failed to fetch dashboard data', error)
  }
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header with Date Picker -->
    <div class="sm:flex sm:items-center sm:justify-between">
      <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Dashboard</h1>
      <div class="mt-4 flex sm:ml-4 sm:mt-0">
        <button type="button" class="inline-flex items-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
          <CalendarIcon class="-ml-0.5 h-5 w-5 text-gray-400" aria-hidden="true" />
          Last 7 Days
          <ChevronDownIcon class="-mr-1 h-5 w-5 text-gray-400" aria-hidden="true" />
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Left Column: Metrics & Chart -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Metrics Grid -->
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
          <div v-for="item in stats" :key="item.name" class="relative overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:px-6 sm:py-6">
            <dt>
              <div class="absolute rounded-md bg-indigo-50 p-3">
                <component :is="item.icon" class="h-6 w-6 text-indigo-600" aria-hidden="true" />
              </div>
              <p class="ml-16 truncate text-sm font-medium text-gray-500">{{ item.name }}</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-1 sm:pb-2">
              <p class="text-2xl font-semibold text-gray-900">{{ item.stat }}</p>
              <p :class="[item.changeType === 'increase' ? 'text-green-600' : 'text-red-600', 'ml-2 flex items-baseline text-sm font-semibold']">
                <component :is="item.changeType === 'increase' ? ArrowUpIcon : ArrowDownIcon" class="h-4 w-4 flex-shrink-0 self-center" aria-hidden="true" />
                <span class="sr-only"> {{ item.changeType === 'increase' ? 'Increased' : 'Decreased' }} by </span>
                {{ item.change }}
              </p>
            </dd>
          </div>
        </dl>

        <!-- Main Chart -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-base font-semibold leading-6 text-gray-900">Total Sales</h3>
                <p class="text-sm text-gray-500">Sales over time</p>
              </div>
              <div class="flex items-center space-x-2">
                 <button type="button" class="rounded p-1 hover:bg-gray-100">
                    <EllipsisHorizontalIcon class="h-5 w-5 text-gray-500" />
                 </button>
              </div>
            </div>
            <div class="mt-6 h-80">
               <Line :data="chartData" :options="chartOptions" />
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Activity Feed -->
      <div class="lg:col-span-1 space-y-6">
        
        <!-- Recent Activity Card -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900">Recent Activity</h3>
            <div class="mt-6 flow-root">
              <ul role="list" class="-my-5 divide-y divide-gray-200">
                <li v-for="activity in activities" :key="activity.id" class="py-4 cursor-pointer hover:bg-gray-50 transition p-2 rounded-md" @click="router.push(`/admin/orders/${activity.id}`)">
                  <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                       <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                          <ShoppingCartIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                       </span>
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="truncate text-sm font-medium text-gray-900">{{ activity.content }}</p>
                      <p class="truncate text-sm text-gray-500">{{ formatDate(activity.date) }}</p>
                    </div>
                    <div>
                      <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                        {{ activity.amount }}
                      </span>
                    </div>
                  </div>
                </li>
                <li v-if="activities.length === 0" class="py-4 text-sm text-gray-500 italic">
                  No recent activity
                </li>
              </ul>
            </div>
            <div class="mt-6">
              <router-link to="/admin/orders" class="flex w-full items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                View all orders
              </router-link>
            </div>
          </div>
        </div>

        <!-- Things to do Card (Mock) -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="p-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900">Things to do</h3>
                <ul class="mt-4 space-y-4">
                    <li class="flex items-start gap-3">
                        <div class="flex h-6 items-center">
                            <input id="comments" name="comments" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" disabled checked />
                        </div>
                        <div class="text-sm leading-6">
                            <label for="comments" class="font-medium text-gray-900 line-through text-gray-500">Add your first product</label>
                            <p class="text-gray-500">You've successfully added products to your store.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="flex h-6 items-center">
                            <input id="candidates" name="candidates" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
                        </div>
                        <div class="text-sm leading-6">
                            <label for="candidates" class="font-medium text-gray-900">Customize your theme</label>
                            <p class="text-gray-500">Make your store look perfect for your brand.</p>
                        </div>
                    </li>
                     <li class="flex items-start gap-3">
                        <div class="flex h-6 items-center">
                            <input id="domain" name="domain" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
                        </div>
                        <div class="text-sm leading-6">
                            <label for="domain" class="font-medium text-gray-900">Add a custom domain</label>
                            <p class="text-gray-500">Strengthen your brand with a custom domain.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

      </div>
    </div>
  </div>
</template>
