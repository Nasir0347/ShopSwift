<script setup>
import { ref, onMounted, computed, watch } from 'vue' // Added computed, watch
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
} from '@heroicons/vue/24/outline' // Icons
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

const router = useRouter()
const allOrders = ref([])
const stats = ref([])
const recentOrders = ref([])
const activities = ref([])
const chartData = ref({ labels: [], datasets: [] })

// Date Filtering
const dateRange = ref('7') // Default 7 days
const showDateMenu = ref(false)

const setRange = (days) => {
    dateRange.value = days
    showDateMenu.value = false
    calculateStats()
}

// Filter orders based on selected range
const filteredOrders = computed(() => {
    if (!allOrders.value.length) return []
    
    const cutoffDate = new Date()
    cutoffDate.setDate(cutoffDate.getDate() - parseInt(dateRange.value))
    
    return allOrders.value.filter(order => new Date(order.created_at) >= cutoffDate)
})

const calculateStats = () => {
    const orders = filteredOrders.value
    
    // Analytics Calculation
    const totalRevenue = orders.reduce((sum, order) => sum + parseFloat(order.total || 0), 0)
    const totalOrdersCount = orders.length
    const avgOrderValue = totalOrdersCount > 0 ? totalRevenue / totalOrdersCount : 0

    // Update Stats
    stats.value = [
      { 
        name: 'Total Sales', 
        stat: '$' + totalRevenue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }), 
        change: 'N/A', 
        changeType: 'neutral',
        icon: CurrencyDollarIcon
      },
      { 
        name: 'Total Orders', 
        stat: totalOrdersCount.toString(), 
        change: 'N/A', 
        changeType: 'neutral',
        icon: ShoppingCartIcon
      },
       { 
        name: 'Avg. Order Value', 
        stat: '$' + avgOrderValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }), 
        change: 'N/A', 
        changeType: 'neutral',
        icon: UsersIcon 
      },
    ]

    // Chart Data (Group by date)
    const daysMap = {}
    const daysButtons = parseInt(dateRange.value)
    
    // Initialize map with 0 for last X days
    for (let i = daysButtons - 1; i >= 0; i--) {
        const d = new Date()
        d.setDate(d.getDate() - i)
        const dateStr = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
        daysMap[dateStr] = 0
    }
    
    // Fill with boolean revenue
    orders.forEach(order => {
        const d = new Date(order.created_at)
        const dateStr = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
        if (daysMap.hasOwnProperty(dateStr)) {
            daysMap[dateStr] += parseFloat(order.total || 0)
        }
    })

    const labels = Object.keys(daysMap)
    const data = Object.values(daysMap)

    chartData.value = {
      labels: labels,
      datasets: [
        {
          label: 'Sales',
          backgroundColor: (context) => {
            const ctx = context.chart.ctx;
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)'); 
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
            return gradient;
          },
          borderColor: '#10b981',
          pointBackgroundColor: '#ffffff',
          pointBorderColor: '#10b981',
          pointBorderWidth: 2,
          borderWidth: 2,
          pointRadius: 3,
          fill: true,
          data: data
        }
      ]
    }
}

// Chart Options (Keep existing)
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) label += ': ';
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
            grid: { borderDash: [4, 4], color: '#f3f4f6', drawBorder: false },
            ticks: { callback: (value) => '$' + value, font: { size: 10 }, color: '#6b7280' }
        },
        x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#6b7280' } }
    },
    elements: { line: { tension: 0.4 } }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric'
  })
}

onMounted(async () => {
  try {
    // Fetch MORE orders to allow decent CLIENT-SIDE filtering
    // In production, you'd use a real analytics endpoint
    const response = await api.get('/orders?per_page=1000') 
    allOrders.value = response.data.orders.data || []
    
    // Initial Calc
    calculateStats()

    // Recent Orders (Always show top 5 actual orders)
    recentOrders.value = allOrders.value.slice(0, 5)

    // Activity Feed
    activities.value = allOrders.value.slice(0, 10).map(order => ({
      id: order.id,
      type: 'order',
      content: `Order #${order.order_number || order.id} placed by ${order.customer ? order.customer.name : 'Guest'}`,
      amount: '$' + parseFloat(order.total || 0).toFixed(2),
      date: order.created_at,
      status: order.status
    }))

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
      <div class="mt-4 flex sm:ml-4 sm:mt-0 relative">
        <button @click="showDateMenu = !showDateMenu" type="button" class="inline-flex items-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
          <CalendarIcon class="-ml-0.5 h-5 w-5 text-gray-400" aria-hidden="true" />
          Last {{ dateRange }} Days
          <ChevronDownIcon class="-mr-1 h-5 w-5 text-gray-400" aria-hidden="true" />
        </button>
        
        <!-- Dropdown menu -->
        <div v-if="showDateMenu" class="absolute right-0 top-full mt-2 w-40 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-10 focus:outline-none">
            <div class="py-1">
                <a href="#" @click.prevent="setRange('7')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Last 7 Days</a>
                <a href="#" @click.prevent="setRange('15')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Last 15 Days</a>
                <a href="#" @click.prevent="setRange('30')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Last 30 Days</a>
            </div>
        </div>
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
            </dd>
          </div>
        </dl>

        <!-- Main Chart -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-base font-semibold leading-6 text-gray-900">Total Sales</h3>
                <p class="text-sm text-gray-500">Sales over last {{ dateRange }} days</p>
              </div>
            </div>
            <div class="mt-6 h-80">
               <Line v-if="chartData.datasets.length" :data="chartData" :options="chartOptions" />
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Activity Feed ONLY -->
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
        
        <!-- Removed 'Things to do' Section -->

      </div>
    </div>
  </div>
</template>
