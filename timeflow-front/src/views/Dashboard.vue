<template>
  <div class="dashboard">
    <v-row class="mb-6">
      <v-col cols="12">
        <div class="welcome-header">
          <div class="welcome-content">
            <h1 class="welcome-title">
              Welcome back, {{ user?.name || 'User' }}!
            </h1>
            <p class="welcome-subtitle">
              Here's what's happening with your business today.
            </p>
          </div>
          <div class="welcome-icon">
            <v-icon size="60" color="primary">mdi-chart-line</v-icon>
          </div>
        </div>
      </v-col>
    </v-row>
    
    <v-row class="mb-6">
      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card customers-card" elevation="8">
          <v-card-text class="pa-6">
            <div class="d-flex align-center">
              <div class="stats-icon customers-icon">
                <v-icon size="40" color="white">mdi-account-group</v-icon>
              </div>
              <div class="ml-4">
                <div class="stats-number">{{ stats.customers }}</div>
                <div class="stats-label">Total Customers</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card products-card" elevation="8">
          <v-card-text class="pa-6">
            <div class="d-flex align-center">
              <div class="stats-icon products-icon">
                <v-icon size="40" color="white">mdi-package-variant</v-icon>
              </div>
              <div class="ml-4">
                <div class="stats-number">{{ stats.products }}</div>
                <div class="stats-label">Total Products</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card orders-card" elevation="8">
          <v-card-text class="pa-6">
            <div class="d-flex align-center">
              <div class="stats-icon orders-icon">
                <v-icon size="40" color="white">mdi-shopping</v-icon>
              </div>
              <div class="ml-4">
                <div class="stats-number">{{ stats.orders }}</div>
                <div class="stats-label">Total Orders</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card revenue-card" elevation="8">
          <v-card-text class="pa-6">
            <div class="d-flex align-center">
              <div class="stats-icon revenue-icon">
                <v-icon size="40" color="white">mdi-currency-usd</v-icon>
              </div>
              <div class="ml-4">
                <div class="stats-number">${{ stats.revenue.toLocaleString() }}</div>
                <div class="stats-label">Total Revenue</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    
    <v-row>
      <v-col cols="12" lg="6">
        <v-card class="chart-card" elevation="2">
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2" color="primary">mdi-chart-line</v-icon>
            <span class="text-h6">Daily Orders</span>
          </v-card-title>
          <v-card-text>
            <div v-if="loading" class="text-center py-8">
              <v-progress-circular indeterminate color="primary"></v-progress-circular>
              <p class="mt-2">Loading chart data...</p>
            </div>
            <div v-else-if="dailyLabels.length === 0" class="chart-placeholder">
              <v-icon size="80" color="grey-lighten-2">mdi-chart-line</v-icon>
              <p class="text-h6 text-grey mt-4">No Orders Data</p>
              <p class="text-grey">Create some orders to see the chart</p>
            </div>
            <div v-else>
              <canvas ref="ordersChart" width="400" height="200"></canvas>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" lg="6">
        <v-card class="chart-card" elevation="2">
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2" color="success">mdi-chart-bar</v-icon>
            <span class="text-h6">Revenue Overview</span>
          </v-card-title>
          <v-card-subtitle>This Month Statistics</v-card-subtitle>
          <v-card-text>
            <div class="text-h4 font-weight-bold mb-4">
              ${{ Math.round(dailyRevenueData.reduce((a, b) => a + b, 0)) }}
            </div>
            <div v-if="loading" class="text-center py-4">
              <v-progress-circular indeterminate color="success" size="24"></v-progress-circular>
            </div>
            <div v-else-if="dailyLabels.length === 0" class="text-center py-4">
              <v-icon size="40" color="grey-lighten-2">mdi-chart-bar</v-icon>
              <p class="text-grey mt-2">No Revenue Data</p>
            </div>
            <div v-else>
              <canvas ref="revenueChart" width="400" height="200"></canvas>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    
    <v-row class="mt-6">
      <v-col cols="12">
        <v-card class="actions-card" elevation="8">
          <v-card-title class="actions-title">
            <v-icon class="mr-2" color="primary">mdi-lightning-bolt</v-icon>
            Quick Actions
          </v-card-title>
          <v-card-text class="pa-6">
            <v-row>
              <v-col cols="12" sm="6" md="3">
                <v-btn
                  color="primary"
                  variant="elevated"
                  size="large"
                  block
                  class="action-btn"
                  to="/customers"
                >
                  <v-icon left>mdi-account-plus</v-icon>
                  Add Customer
                </v-btn>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-btn
                  color="success"
                  variant="elevated"
                  size="large"
                  block
                  class="action-btn"
                  to="/products"
                >
                  <v-icon left>mdi-package-plus</v-icon>
                  Add Product
                </v-btn>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-btn
                  color="#FF9800"
                  variant="elevated"
                  size="large"
                  block
                  class="action-btn"
                  to="/orders"
                >
                  <v-icon left>mdi-shopping-plus</v-icon>
                  Create Order
                </v-btn>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-btn
                  color="info"
                  variant="elevated"
                  size="large"
                  block
                  class="action-btn"
                  @click="refreshData"
                >
                  <v-icon left>mdi-refresh</v-icon>
                  Refresh Data
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { customerService } from '@/services/customerService'
import { productService } from '@/services/productService'
import { orderService } from '@/services/orderService'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

export default {
  name: 'Dashboard',
  setup() {
    const authStore = useAuthStore()
    const loading = ref(false)
    
    const user = computed(() => authStore.user)
    
    const stats = ref({
      customers: 0,
      products: 0,
      orders: 0,
      revenue: 0
    })
    
    const dailyOrdersData = ref([])
    const dailyRevenueData = ref([])
    const dailyLabels = ref([])
    
    const ordersChart = ref(null)
    const revenueChart = ref(null)
    let ordersChartInstance = null
    let revenueChartInstance = null
    
    
    const fetchStats = async () => {
      loading.value = true
      try {
        const customersResponse = await customerService.getCustomers({ per_page: 1 })
        stats.value.customers = customersResponse.data.meta.total || 0
        
        const productsResponse = await productService.getProducts({ per_page: 1 })
        stats.value.products = productsResponse.data.meta.total || 0
        
        await fetchAllData()
        
      } catch (error) {
        console.error('Error fetching dashboard stats:', error)
      } finally {
        loading.value = false
      }
    }
    
    const fetchAllData = async () => {
      try {
        const ordersResponse = await orderService.getOrders({ per_page: 1000 })
        const orders = ordersResponse.data.data || []
        
        stats.value.orders = ordersResponse.data.meta.total || 0
        
        const now = new Date()
        const currentYear = now.getFullYear()
        const currentMonth = now.getMonth()
        const firstDay = new Date(currentYear, currentMonth, 1)
        const lastDay = new Date(currentYear, currentMonth + 1, 0)
        
        const daysInMonth = lastDay.getDate()
        const dailyData = {}
        
        for (let day = 1; day <= daysInMonth; day++) {
          const dateKey = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
          dailyData[dateKey] = {
            orders: 0,
            revenue: 0,
            label: day.toString()
          }
        }
        
        let monthlyRevenue = 0
        
        orders.forEach(order => {
          const orderDate = new Date(order.order_date)
          if (orderDate.getFullYear() === currentYear && orderDate.getMonth() === currentMonth) {
            const day = orderDate.getDate()
            const dateKey = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
            
            if (dailyData[dateKey]) {
              dailyData[dateKey].orders++
              dailyData[dateKey].revenue += parseFloat(order.total_amount) || 0
              monthlyRevenue += parseFloat(order.total_amount) || 0
            }
          }
        })
        
        stats.value.revenue = Math.round(monthlyRevenue * 100) / 100
        
        const sortedDays = Object.keys(dailyData).sort()
        const ordersData = sortedDays.map(key => dailyData[key].orders)
        const revenueData = sortedDays.map(key => dailyData[key].revenue)
        const labels = sortedDays.map(key => dailyData[key].label)
        
        dailyOrdersData.value = ordersData
        dailyRevenueData.value = revenueData
        dailyLabels.value = labels
        
      } catch (error) {
        console.error('Error fetching all data:', error)
        stats.value.orders = 0
        stats.value.revenue = 0
        dailyOrdersData.value = []
        dailyRevenueData.value = []
        dailyLabels.value = []
      }
    }
    
    
    const createOrdersChart = () => {
      if (ordersChartInstance) {
        ordersChartInstance.destroy()
      }
      
      const ctx = ordersChart.value.getContext('2d')
      ordersChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: dailyLabels.value,
          datasets: [{
            label: 'Daily Orders',
            data: dailyOrdersData.value,
            borderColor: '#1976d2',
            backgroundColor: 'rgba(25, 118, 210, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            },
            x: {
              title: {
                display: true,
                text: 'Day of Month'
              }
            }
          }
        }
      })
    }
    
    const createRevenueChart = () => {
      if (revenueChartInstance) {
        revenueChartInstance.destroy()
      }
      
      const ctx = revenueChart.value.getContext('2d')
      revenueChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: dailyLabels.value,
          datasets: [{
            label: 'Daily Revenue',
            data: dailyRevenueData.value,
            backgroundColor: '#4caf50',
            borderColor: '#388e3c',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return '$' + value
                }
              }
            },
            x: {
              title: {
                display: true,
                text: 'Day of Month'
              }
            }
          }
        }
      })
    }
    
    const updateCharts = async () => {
      await nextTick()
      createOrdersChart()
      createRevenueChart()
    }
    
    const refreshData = (async () => {
      await fetchStats()
      await updateCharts()
    })
    
    onMounted(async () => {
      await fetchStats()
      await updateCharts()
    })
    
    return {
      user,
      stats,
      loading,
      dailyOrdersData,
      dailyRevenueData,
      dailyLabels,
      ordersChart,
      revenueChart,
      refreshData
    }
  }
}
</script>

<style scoped>
.dashboard {
  padding: 1rem;
}

.welcome-header {
  background: linear-gradient(135deg, #1976D2, #42A5F5);
  color: white;
  padding: 2rem;
  border-radius: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  box-shadow: 0 8px 32px rgba(25, 118, 210, 0.3);
}

.welcome-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.welcome-subtitle {
  font-size: 1.1rem;
  margin: 0.5rem 0 0 0;
  opacity: 0.9;
}

.welcome-icon {
  opacity: 0.8;
}

.stats-card {
  border-radius: 16px;
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.stats-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.customers-card {
  background: linear-gradient(135deg, #4CAF50, #66BB6A);
}

.products-card {
  background: linear-gradient(135deg, #FF9800, #FFB74D);
}

.orders-card {
  background: linear-gradient(135deg, #9C27B0, #BA68C8);
}

.revenue-card {
  background: linear-gradient(135deg, #F44336, #EF5350);
}

.stats-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.customers-icon {
  background: linear-gradient(135deg, #2E7D32, #4CAF50);
}

.products-icon {
  background: linear-gradient(135deg, #E65100, #FF9800);
}

.orders-icon {
  background: linear-gradient(135deg, #6A1B9A, #9C27B0);
}

.revenue-icon {
  background: linear-gradient(135deg, #C62828, #F44336);
}

.stats-number {
  font-size: 2.5rem;
  font-weight: 700;
  color: white;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stats-label {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
  margin-bottom: 0.5rem;
}


.chart-card, .activity-card, .actions-card {
  border-radius: 16px;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.chart-title, .activity-title, .actions-title {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-radius: 16px 16px 0 0;
  font-weight: 600;
  color: #495057;
}

.chart-placeholder {
  text-align: center;
  padding: 3rem 1rem;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-radius: 12px;
  border: 2px dashed #dee2e6;
}

.chart-loading {
  text-align: center;
  padding: 3rem 1rem;
}

.chart-container {
  position: relative;
  height: 300px;
  width: 100%;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-radius: 12px;
  padding: 1rem;
}

.chart-wrapper {
  width: 100%;
  height: 100%;
  background: white;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.chart-card {
  border-radius: 12px;
  transition: all 0.3s ease;
}

.chart-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

canvas {
  max-height: 200px;
}



.activity-list {
  max-height: 400px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #f1f3f4;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-text {
  font-weight: 500;
  color: #495057;
  margin-bottom: 0.25rem;
}

.activity-time {
  font-size: 0.8rem;
  color: #6c757d;
}

.action-btn {
  border-radius: 12px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  color: white !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

/* Responsive Design */
@media (max-width: 960px) {
  .welcome-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .welcome-title {
    font-size: 2rem;
  }
  
  .stats-number {
    font-size: 2rem;
  }
}

@media (max-width: 600px) {
  .dashboard {
    padding: 0.5rem;
  }
  
  .welcome-header {
    padding: 1.5rem;
  }
  
  .welcome-title {
    font-size: 1.5rem;
  }
  
  .stats-icon {
    width: 50px;
    height: 50px;
  }
  
  .stats-number {
    font-size: 1.8rem;
  }
}
</style>
