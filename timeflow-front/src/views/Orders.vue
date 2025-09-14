<template>
  <div class="orders-page">
    <v-row class="mb-6">
      <v-col cols="12">
        <div class="page-header">
          <div class="header-content">
            <div class="header-left">
              <v-icon size="48" color="primary" class="header-icon">mdi-shopping</v-icon>
              <div class="header-text">
                <h1 class="page-title">Order Management</h1>
                <p class="page-subtitle">Manage customer orders and track order status</p>
              </div>
            </div>
            <div class="header-right">
              <v-btn
                color="primary"
                size="large"
                prepend-icon="mdi-plus"
                @click="openCreateDialog"
                class="add-button"
                elevation="4"
              >
                Create Order
              </v-btn>
            </div>
          </div>
        </div>
      </v-col>
    </v-row>
    
    <v-row>
      <v-col cols="12">
        <v-card class="orders-card" elevation="8">
          <v-card-title class="table-header">
            <div class="header-left">
              <span class="table-title">Order List</span>
            </div>
            
            <div class="header-filters">
              <v-text-field
                v-model="searchQuery"
                label="Search orders..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                clearable
                @input="debouncedSearch"
                class="search-field"
                density="compact"
                style="min-width: 300px;"
              ></v-text-field>
              
              <v-select
                v-model="statusFilter"
                :items="statusOptions"
                label="Status"
                variant="outlined"
                @update:model-value="fetchOrders"
                class="status-filter"
                density="compact"
                style="max-width: 150px;"
              ></v-select>
            </div>
            
            <div class="header-right">
              <v-chip color="primary" variant="outlined">
                {{ pagination.total }} orders
              </v-chip>
            </div>
          </v-card-title>
          
          <v-data-table-server
            :headers="headers"
            :items="orders"
            :loading="loading"
            class="orders-table"
            item-key="id"
            :items-per-page="perPage"
            :items-length="pagination.total"
            :page="currentPage"
            :sort-by="sortBy"
            :sort-desc="sortDesc"
            :items-per-page-options="perPageOptions"
            @update:page="onPageChange"
            @update:items-per-page="onItemsPerPageChange"
            @update:sort-by="(newSortBy) => onSortChange(newSortBy, sortDesc)"
            @update:sort-desc="(newSortDesc) => onSortChange(sortBy, newSortDesc)"
          >
            <template v-slot:item.id="{ item }">
              <div class="order-id">
                <v-icon size="16" class="mr-1">mdi-receipt</v-icon>
                #{{ item.id }}
              </div>
            </template>

            <template v-slot:item.customer_name="{ item }">
              <div class="customer-info">
                <v-avatar size="32" color="primary" class="mr-2">
                  <span class="text-white text-caption">{{ getInitials(item.customer_name) }}</span>
                </v-avatar>
                <div>
                  <div class="customer-name">{{ item.customer_name }}</div>
                  <div class="customer-email">{{ item.customer_email }}</div>
                </div>
              </div>
            </template>

            <template v-slot:item.total_amount="{ item }">
              <div class="amount-info">
                <v-icon size="16" class="mr-1">mdi-currency-usd</v-icon>
                {{ formatPrice(item.total_amount) }}
              </div>
            </template>

            <template v-slot:item.status="{ item }">
              <v-chip
                :color="getStatusColor(item.status)"
                variant="flat"
                size="small"
                class="status-chip"
              >
                <v-icon 
                  :color="'white'" 
                  size="16" 
                  class="mr-1"
                >
                  {{ getStatusIcon(item.status) }}
                </v-icon>
                {{ formatStatus(item.status) }}
              </v-chip>
            </template>

            <template v-slot:item.order_date="{ item }">
              <div class="date-info">
                <v-icon size="16" class="mr-1">mdi-calendar-clock</v-icon>
                {{ formatDateTime(item.order_date) }}
              </div>
            </template>

            <template v-slot:item.actions="{ item }">
              <div class="action-buttons">
                <v-btn
                  icon="mdi-eye"
                  size="small"
                  color="info"
                  variant="text"
                  @click="viewOrder(item)"
                  class="action-btn"
                ></v-btn>
                <v-btn
                  icon="mdi-pencil"
                  size="small"
                  color="warning"
                  variant="text"
                  @click="editOrder(item)"
                  class="action-btn"
                ></v-btn>
                <v-btn
                  icon="mdi-delete"
                  size="small"
                  color="error"
                  variant="text"
                  @click="deleteOrder(item)"
                  class="action-btn"
                ></v-btn>
              </div>
            </template>
          </v-data-table-server>
        </v-card>
      </v-col>
    </v-row>

    <v-dialog v-model="dialog" max-width="800" persistent>
      <v-card class="order-dialog">
        <v-card-title class="dialog-header">
          <v-icon size="24" class="mr-2">
            {{ isEditing ? 'mdi-pencil' : 'mdi-plus' }}
          </v-icon>
          {{ isEditing ? 'Edit Order' : 'Create New Order' }}
        </v-card-title>
        
        <v-card-text class="pa-6">
          <v-form ref="form" @submit.prevent="saveOrder">
            <v-row>
              <v-col cols="12" md="6">
                <v-select
                  v-model="orderForm.customer_id"
                  :items="customers"
                  item-title="name"
                  item-value="id"
                  :label="isEditing ? 'Customer (Locked - order exists)' : 'Customer'"
                  :rules="customerRules"
                  variant="outlined"
                  :prepend-inner-icon="isEditing ? 'mdi-lock' : 'mdi-account'"
                  :readonly="isEditing"
                  :disabled="isEditing"
                  required
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="orderForm.status"
                  :items="formStatusOptions"
                  label="Status"
                  :rules="statusRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-flag"
                  required
                ></v-select>
              </v-col>
            </v-row>
            
            <v-row>
              <v-col cols="12">
                <h3 class="mb-3">
                  Products
                  <v-chip 
                    v-if="isProductsLocked" 
                    color="orange" 
                    size="small" 
                    class="ml-2"
                  >
                    <v-icon size="16" class="mr-1">mdi-lock</v-icon>
                    {{ getLockReason() }}
                  </v-chip>
                </h3>
                <div v-for="(product, index) in orderForm.products" :key="index" class="product-row" :class="{ locked: isProductsLocked }">
                  <v-row>
                    <v-col cols="12" md="6">
                      <v-select
                        v-model="product.product_id"
                        :items="products"
                        item-title="name"
                        item-value="id"
                        label="Product"
                        variant="outlined"
                        :readonly="isProductsLocked"
                        :disabled="isProductsLocked"
                        @update:model-value="updateProductPrice(index)"
                      ></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                      <v-text-field
                        v-model="product.quantity"
                        label="Quantity"
                        type="number"
                        min="1"
                        variant="outlined"
                        :readonly="isProductsLocked"
                        :disabled="isProductsLocked"
                        @input="calculateTotal"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="2">
                      <v-text-field
                        v-model="product.price"
                        label="Price"
                        type="number"
                        step="0.01"
                        variant="outlined"
                        readonly
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="1">
                      <v-btn
                        icon="mdi-delete"
                        color="error"
                        variant="text"
                        @click="removeProduct(index)"
                        :disabled="orderForm.products.length === 1 || isProductsLocked"
                      ></v-btn>
                    </v-col>
                  </v-row>
                </div>
                
                <v-btn
                  color="primary"
                  variant="outlined"
                  prepend-icon="mdi-plus"
                  @click="addProduct"
                  :disabled="isProductsLocked"
                  class="mt-2"
                >
                  Add Product
                </v-btn>
              </v-col>
            </v-row>
            
            <v-row>
              <v-col cols="12">
                <v-divider class="my-4"></v-divider>
                <div class="total-section">
                  <h3>Total Amount: {{ formatPrice(orderForm.total_amount) }}</h3>
                </div>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        
        <v-card-actions class="pa-6 pt-0">
          <v-spacer></v-spacer>
          <v-btn
            color="grey"
            variant="text"
            @click="closeDialog"
            :disabled="saving"
          >
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            @click="saveOrder"
            :loading="saving"
          >
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="viewDialog" max-width="600">
      <v-card>
        <v-card-title class="dialog-header">
          <v-icon size="24" class="mr-2">mdi-eye</v-icon>
          Order Details #{{ selectedOrder?.id }}
        </v-card-title>
        <v-card-text class="pa-6" v-if="selectedOrder">
          <v-row>
            <v-col cols="12" md="6">
              <h4>Customer Information</h4>
              <p><strong>Name:</strong> {{ selectedOrder.customer_name }}</p>
              <p><strong>Email:</strong> {{ selectedOrder.customer_email }}</p>
              <p><strong>Phone:</strong> {{ selectedOrder.customer_phone }}</p>
            </v-col>
            <v-col cols="12" md="6">
              <h4>Order Information</h4>
              <p><strong>Status:</strong> 
                <v-chip :color="getStatusColor(selectedOrder.status)" size="small">
                  {{ formatStatus(selectedOrder.status) }}
                </v-chip>
              </p>
              <p><strong>Date & Time:</strong> {{ formatDateTime(selectedOrder.order_date) }}</p>
              <p><strong>Total:</strong> {{ formatPrice(selectedOrder.total_amount) }}</p>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="viewDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h5">
          <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
          Delete Order
        </v-card-title>
        <v-card-text>
          Are you sure you want to delete Order #<strong>{{ selectedOrder?.id }}</strong>?
          This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey"
            variant="text"
            @click="deleteDialog = false"
            :disabled="deleting"
          >
            Cancel
          </v-btn>
          <v-btn
            color="error"
            @click="confirmDelete"
            :loading="deleting"
          >
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { orderService } from '@/services/orderService'
import { customerService } from '@/services/customerService'
import { productService } from '@/services/productService'

export default {
  name: 'Orders',
  setup() {
    const orders = ref([])
    const customers = ref([])
    const products = ref([])
    const loading = ref(false)
    const saving = ref(false)
    const deleting = ref(false)
    const dialog = ref(false)
    const viewDialog = ref(false)
    const deleteDialog = ref(false)
    const formValid = ref(false)
    const form = ref(null)
    const isEditing = ref(false)
    const searchQuery = ref('')
    const statusFilter = ref('all')
    const perPage = ref(10)
    const currentPage = ref(1)
    const selectedOrder = ref(null)
    const sortBy = ref(['order_date'])
    const sortDesc = ref([true])
    
    const pagination = ref({
      total: 0,
      per_page: 10,
      current_page: 1,
      last_page: 1
    })
    
    const orderForm = reactive({
      customer_id: '',
      status: 'processing',
      total_amount: 0,
      products: [{ product_id: '', quantity: 1, price: 0 }]
    })
    
    const headers = [
      { title: 'Order ID', key: 'id', sortable: true },
      { title: 'Customer', key: 'customer_name', sortable: true },
      { title: 'Total', key: 'total_amount', sortable: true },
      { title: 'Status', key: 'status', sortable: true },
      { title: 'Date & Time', key: 'order_date', sortable: true },
      { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
    ]
    
    const statusOptions = [
      { title: 'All', value: 'all' },
      { title: 'Processing', value: 'processing' },
      { title: 'Shipped', value: 'shipped' },
      { title: 'Delivered', value: 'delivered' },
      { title: 'Cancelled', value: 'cancelled' }
    ]
    
    const formStatusOptions = [
      { title: 'Processing', value: 'processing' },
      { title: 'Shipped', value: 'shipped' },
      { title: 'Delivered', value: 'delivered' },
      { title: 'Cancelled', value: 'cancelled' }
    ]
    
    const perPageOptions = [5, 10, 25, 50]
    
    const customerRules = [
      v => !!v || 'Customer is required'
    ]
    
    const statusRules = [
      v => !!v || 'Status is required'
    ]
    
    const isFormValid = computed(() => {
      return orderForm.customer_id && 
             orderForm.status &&
             orderForm.products.every(p => p.product_id && p.quantity > 0)
    })
    
    const isProductsLocked = computed(() => {
      return isEditing.value && (orderForm.status === 'shipped' || orderForm.status === 'delivered')
    })
    
    const fetchOrders = async () => {
      loading.value = true
      try {
        const params = {
          per_page: perPage.value,
          page: currentPage.value
        }
        
        if (searchQuery.value) {
          params.qs = searchQuery.value
        }
        
        if (statusFilter.value !== 'all') {
          params.status = statusFilter.value
        }
        
        if (sortBy.value.length > 0) {
          const sortField = sortBy.value[0]
          if (typeof sortField === 'object' && sortField.key) {
            params.sort_by = sortField.key
            params.sort_direction = sortField.order || (sortDesc.value[0] ? 'desc' : 'asc')
          } else {
            params.sort_by = sortField
            params.sort_direction = sortDesc.value[0] ? 'desc' : 'asc'
          }
        }
        
        const response = await orderService.getOrders(params)
                
        orders.value = response.data.data || []
        pagination.value = {
          total: response.data.meta.total || 0,
          per_page: response.data.meta.per_page || 10,
          current_page: response.data.meta.current_page || 1,
          last_page: response.data.meta.last_page || 1
        }
      } catch (error) {
        console.error('Error fetching orders:', error)
      } finally {
        loading.value = false
      }
    }
    
    const fetchCustomers = async () => {
      try {
        const response = await customerService.getCustomers({ per_page: 100 })
        customers.value = response.data.data || []
      } catch (error) {
        console.error('Error fetching customers:', error)
      }
    }
    
    const fetchProducts = async () => {
      try {
        const response = await productService.getProducts({ per_page: 100 })
        products.value = response.data.data || []
      } catch (error) {
        console.error('Error fetching products:', error)
      }
    }
    
    const debouncedSearch = (() => {
      let timeout
      return () => {
        clearTimeout(timeout)
        timeout = setTimeout(() => {
          currentPage.value = 1
          fetchOrders()
        }, 500)
      }
    })()
    
    const onPageChange = (page) => {
      currentPage.value = page
      fetchOrders()
    }
    
    const onItemsPerPageChange = (itemsPerPage) => {
      perPage.value = itemsPerPage
      currentPage.value = 1
      fetchOrders()
    }
    
    const onSortChange = (newSortBy, newSortDesc) => {
      sortBy.value = newSortBy
      sortDesc.value = newSortDesc
      currentPage.value = 1
      fetchOrders()
    }
    
    const openCreateDialog = () => {
      isEditing.value = false
      resetForm()
      dialog.value = true
    }
    
    const editOrder = async (order) => {
      isEditing.value = true
      selectedOrder.value = order
      
      try {
        const response = await orderService.getOrder(order.id)
        const fullOrder = response.data
        
        Object.assign(orderForm, {
          customer_id: fullOrder.customer_id,
          status: fullOrder.status,
          total_amount: fullOrder.total_amount,
          products: fullOrder.products.map(product => ({
            product_id: product.id,
            quantity: product.pivot.quantity,
            price: product.pivot.price
          }))
        })
        
        dialog.value = true
      } catch (error) {
        console.error('Error fetching order details:', error)
        dialog.value = true
      }
    }
    
    const viewOrder = (order) => {
      selectedOrder.value = order
      viewDialog.value = true
    }
    
    const deleteOrder = (order) => {
      selectedOrder.value = order
      deleteDialog.value = true
    }
    
    const confirmDelete = async () => {
      deleting.value = true
      try {
        await orderService.deleteOrder(selectedOrder.value.id)
        await fetchOrders()
        deleteDialog.value = false
        selectedOrder.value = null
      } catch (error) {
        console.error('Error deleting order:', error)
      } finally {
        deleting.value = false
      }
    }
    
    const saveOrder = async () => {
      // if (!isFormValid.value) return
      
      const formData = {
        ...orderForm,
        total_amount: orderForm.total_amount
      }
      
      saving.value = true
      try {
        if (isEditing.value) {
          await orderService.updateOrder(selectedOrder.value.id, formData)
        } else {
          await orderService.createOrder(formData)
        }
        
        await fetchOrders()
        closeDialog()
      } catch (error) {
        console.error('Error saving order:', error)
        closeDialog()
      } finally {
        saving.value = false
      }
    }
    
    const closeDialog = () => {
      dialog.value = false
      resetForm()
      selectedOrder.value = null
    }
    
    const resetForm = () => {
      Object.assign(orderForm, {
        customer_id: '',
        status: 'processing',
        total_amount: 0,
        products: [{ product_id: '', quantity: 1, price: 0 }]
      })
    }
    
    const addProduct = () => {
      orderForm.products.push({ product_id: '', quantity: 1, price: 0 })
    }
    
    const removeProduct = (index) => {
      if (orderForm.products.length > 1) {
        orderForm.products.splice(index, 1)
        calculateTotal()
      }
    }
    
    const updateProductPrice = (index) => {
      const product = products.value.find(p => p.id === orderForm.products[index].product_id)
      if (product) {
        orderForm.products[index].price = product.price
        calculateTotal()
      }
    }
    
    const calculateTotal = () => {
      orderForm.total_amount = orderForm.products.reduce((total, product) => {
        return total + (product.price * product.quantity)
      }, 0)
    }
    
    const formatPrice = (price) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(price)
    }
    
    const formatDate = (date) => {
      return new Date(date).toLocaleDateString()
    }
    
    const formatDateTime = (date) => {
      return new Date(date).toLocaleString()
    }
    
    const formatStatus = (status) => {
      return status.charAt(0).toUpperCase() + status.slice(1)
    }
    
    const getStatusColor = (status) => {
      const colors = {
        processing: 'orange',
        shipped: 'blue',
        delivered: 'green',
        cancelled: 'red'
      }
      return colors[status] || 'grey'
    }
    
    const getStatusIcon = (status) => {
      const icons = {
        processing: 'mdi-clock',
        shipped: 'mdi-truck',
        delivered: 'mdi-check-circle',
        cancelled: 'mdi-close-circle'
      }
      return icons[status] || 'mdi-help-circle'
    }
    
    const getInitials = (name) => {
      return name ? name.split(' ').map(n => n[0]).join('').toUpperCase() : '?'
    }
    
    const getLockReason = () => {
      if (orderForm.status === 'shipped') {
        return 'Order shipped'
      } else if (orderForm.status === 'delivered') {
        return 'Order delivered'
      }
      return 'Cannot be changed'
    }
    
    onMounted(() => {
      fetchOrders()
      fetchCustomers()
      fetchProducts()
    })
    
    return {
      orders,
      customers,
      products,
      loading,
      saving,
      deleting,
      dialog,
      viewDialog,
      deleteDialog,
      formValid,
      form,
      isFormValid,
      isProductsLocked,
      isEditing,
      searchQuery,
      statusFilter,
      perPage,
      currentPage,
      selectedOrder,
      orderForm,
      pagination,
      
      headers,
      statusOptions,
      formStatusOptions,
      perPageOptions,
      
      customerRules,
      statusRules,
      
      fetchOrders,
      debouncedSearch,
      onPageChange,
      onItemsPerPageChange,
      onSortChange,
      openCreateDialog,
      editOrder,
      viewOrder,
      deleteOrder,
      confirmDelete,
      saveOrder,
      sortBy,
      sortDesc,
      closeDialog,
      addProduct,
      removeProduct,
      updateProductPrice,
      calculateTotal,
      formatPrice,
      formatDate,
      formatDateTime,
      formatStatus,
      getStatusColor,
      getStatusIcon,
      getInitials,
      getLockReason
    }
  }
}
</script>

<style scoped>
.orders-page {
  padding: 1rem;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
}

.page-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  padding: 2rem;
  color: white;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-icon {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  padding: 0.5rem;
}

.header-text h1 {
  margin: 0;
  font-size: 2.5rem;
  font-weight: 700;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-text p {
  margin: 0.5rem 0 0 0;
  font-size: 1.1rem;
  opacity: 0.9;
}

.add-button {
  background: rgba(255, 255, 255, 0.2) !important;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white !important;
  font-weight: 600;
  text-transform: none;
  border-radius: 12px;
  padding: 0 2rem;
  height: 48px;
}

.add-button:hover {
  background: rgba(255, 255, 255, 0.3) !important;
  transform: translateY(-2px);
  transition: all 0.3s ease;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 2px solid #dee2e6;
}

.header-left {
  display: flex;
  align-items: center;
}

.table-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #495057;
}

.header-filters {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.header-right {
  display: flex;
  align-items: center;
}

.search-field {
  border-radius: 12px;
}

.status-filter {
  border-radius: 12px;
}

.status-chip {
  font-weight: 600;
  text-transform: capitalize;
  min-width: 100px;
  justify-content: center;
}

.status-chip .v-icon {
  margin-right: 4px;
}

.orders-card {
  border-radius: 16px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.orders-table {
  background: white;
}

.order-id {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: #1976D2;
}

.customer-info {
  display: flex;
  align-items: center;
}

.customer-name {
  font-weight: 600;
  color: #212529;
  font-size: 0.95rem;
}

.customer-email {
  font-size: 0.8rem;
  color: #6c757d;
  font-weight: 500;
}

.amount-info {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: #28a745;
  font-size: 0.95rem;
}

.date-info {
  display: flex;
  align-items: center;
  font-size: 0.9rem;
  color: #6c757d;
  white-space: nowrap;
}

.date-info .v-icon {
  color: #28a745;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.action-btn {
  border-radius: 8px;
  transition: all 0.2s ease;
}

.action-btn:hover {
  transform: scale(1.1);
}

.order-dialog {
  border-radius: 16px;
}

.dialog-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-weight: 600;
  font-size: 1.25rem;
}

.product-row {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
  background: #fafafa;
  transition: all 0.3s ease;
}

.product-row.locked {
  background: #fff3e0;
  border-color: #ff9800;
  opacity: 0.8;
}

.total-section {
  text-align: right;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  border: 2px solid #28a745;
}

.total-section h3 {
  color: #28a745;
  margin: 0;
  font-size: 1.5rem;
}

@media (max-width: 960px) {
  .page-header {
    padding: 1.5rem;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
  }
  
  .header-text h1 {
    font-size: 2rem;
  }
  
  .table-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .header-filters {
    justify-content: center;
  }
  
  .search-field, .status-filter {
    max-width: 100% !important;
  }
}

@media (max-width: 600px) {
  .orders-page {
    padding: 0.5rem;
  }
  
  .page-header {
    padding: 1rem;
    border-radius: 16px;
  }
  
  .header-text h1 {
    font-size: 1.75rem;
  }
  
  .header-text p {
    font-size: 1rem;
  }
  
  .add-button {
    width: 100%;
    margin-top: 1rem;
  }
}
</style>