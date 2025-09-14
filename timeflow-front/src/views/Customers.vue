<template>
  <div class="customers-page">
    <v-row class="mb-6">
      <v-col cols="12">
        <div class="page-header">
          <div class="header-content">
            <h1 class="page-title">
              <v-icon class="mr-3" color="primary" size="40">mdi-account-group</v-icon>
              Customer Management
            </h1>
            <p class="page-subtitle">Manage your customer database</p>
          </div>
          <v-btn
            color="primary"
            size="large"
            prepend-icon="mdi-plus"
            @click="openCreateDialog"
            class="add-btn"
            elevation="4"
          >
            Add Customer
          </v-btn>
        </div>
      </v-col>
    </v-row>


    <v-row>
      <v-col cols="12">
        <v-card class="customers-card" elevation="8">
          <v-card-title class="table-header">
            <div class="header-left">
              <span class="table-title">Customer List</span>
            </div>
            
            <div class="header-filters">
              <v-text-field
                v-model="searchQuery"
                label="Search customers..."
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
                @update:model-value="fetchCustomers"
                class="status-filter"
                density="compact"
                style="max-width: 150px;"
              ></v-select>
            </div>
            
            <div class="header-right">
              <v-chip color="primary" variant="outlined">
                {{ pagination.total }} customers
              </v-chip>
            </div>
          </v-card-title>
          
          <v-data-table-server
            :headers="headers"
            :items="customers"
            :loading="loading"
            class="customers-table"
            item-key="id"
            :items-per-page="perPage"
            :items-length="totalCount"
            :page="currentPage"
            :sort-by="sortBy"
            :sort-desc="sortDesc"
            :items-per-page-options="perPageOptions"
            @update:page="onPageChange"
            @update:items-per-page="onItemsPerPageChange"
            @update:sort-by="(newSortBy) => onSortChange(newSortBy, sortDesc)"
            @update:sort-desc="(newSortDesc) => onSortChange(sortBy, newSortDesc)"
          >

            <template v-slot:item.name="{ item }">
              <div class="customer-info">
                <v-avatar size="40" color="primary" class="mr-3">
                  <span class="text-white">{{ item.name.charAt(0).toUpperCase() }}</span>
                </v-avatar>
                <div>
                  <div class="customer-name">{{ item.name }}</div>
                  <div class="customer-email">{{ item.email }}</div>
                </div>
              </div>
            </template>

            <template v-slot:item.is_active="{ item }">
              <v-chip
                :color="item.is_active ? 'green' : 'red'"
                variant="flat"
                size="small"
                class="status-chip"
              >
                <v-icon 
                  :color="item.is_active ? 'white' : 'white'" 
                  size="16" 
                  class="mr-1"
                >
                  {{ item.is_active ? 'mdi-check-circle' : 'mdi-close-circle' }}
                </v-icon>
                {{ item.is_active ? 'Active' : 'Inactive' }}
              </v-chip>
            </template>

            <template v-slot:item.created_at="{ item }">
              <div class="date-cell">
                <div class="date-text">{{ formatDate(item.created_at) }}</div>
                <div class="time-text">{{ formatTime(item.created_at) }}</div>
              </div>
            </template>

            <template v-slot:item.phone="{ item }">
              <div class="phone-info">
                <v-icon size="16" class="mr-1">mdi-phone</v-icon>
                {{ item.phone || 'N/A' }}
              </div>
            </template>


            <template v-slot:item.address="{ item }">
              <div class="address-info">
                <v-icon size="16" class="mr-1">mdi-map-marker</v-icon>
                {{ item.address || 'N/A' }}
              </div>
            </template>


            <template v-slot:item.actions="{ item }">
              <div class="action-buttons">
                <v-btn
                  icon="mdi-pencil"
                  size="small"
                  color="warning"
                  variant="text"
                  @click="editCustomer(item)"
                  class="action-btn"
                ></v-btn>
                <v-btn
                  icon="mdi-delete"
                  size="small"
                  color="error"
                  variant="text"
                  @click="deleteCustomer(item)"
                  class="action-btn"
                ></v-btn>
              </div>
            </template>
          </v-data-table-server>
        </v-card>
      </v-col>
    </v-row>

    <v-dialog v-model="dialog" max-width="600" persistent>
      <v-card class="customer-dialog">
        <v-card-title class="dialog-header">
          <v-icon class="mr-3" color="primary" size="30">
            {{ isEditing ? 'mdi-pencil' : 'mdi-plus' }}
          </v-icon>
          {{ isEditing ? 'Edit Customer' : 'Add New Customer' }}
        </v-card-title>
        
        <v-card-text class="pa-6">
          <v-form ref="form" @submit.prevent="saveCustomer">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="customerForm.name"
                  label="Full Name"
                  :rules="nameRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-account"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="customerForm.email"
                  label="Email Address"
                  type="email"
                  :rules="emailRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-email"
                  required
                ></v-text-field>
              </v-col>
            </v-row>
            
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="customerForm.phone"
                  label="Phone Number"
                  :rules="phoneRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-phone"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-switch
                  :model-value="Boolean(customerForm.is_active)"
                  @update:model-value="(value) => { customerForm.is_active = value;}"
                  label="Active Customer"
                  color="success"
                  hide-details
                  prepend-icon="mdi-account-check"
                ></v-switch>
              </v-col>
            </v-row>
            
            <v-row>
              <v-col cols="12">
                <v-textarea
                  v-model="customerForm.address"
                  label="Address"
                  variant="outlined"
                  prepend-inner-icon="mdi-map-marker"
                  rows="3"
                ></v-textarea>
              </v-col>
            </v-row>
            
            <v-row>
              <v-col cols="12">
                <v-alert v-if="favoriteProductName" type="info" variant="tonal">
                  <div class="d-flex align-center">
                    Favorite product: <strong>{{ favoriteProductName }}</strong>
                    <span v-if="favoriteProductQuantity"> ({{ favoriteProductQuantity }} total)</span>
                  </div>
                </v-alert>
                <v-skeleton-loader
                  v-else-if="profileLoading"
                  type="heading, text"
                ></v-skeleton-loader>
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
            @click="saveCustomer"
            :loading="saving"
            :disabled="!isFormValid"
          >
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>


    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h6">
          <v-icon color="error" class="mr-2">mdi-alert</v-icon>
          Confirm Delete
        </v-card-title>
        <v-card-text>
          Are you sure you want to delete <strong>{{ selectedCustomer?.name }}</strong>?
          This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="confirmDelete" :loading="deleting">
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { customerService } from '@/services/customerService'

export default {
  name: 'Customers',
  setup() {

    const customers = ref([])
    const loading = ref(false)
    const saving = ref(false)
    const deleting = ref(false)
    const profileLoading = ref(false)
    const dialog = ref(false)
    const deleteDialog = ref(false)
    const formValid = ref(false)
    const form = ref(null)
    const isEditing = ref(false)
    const searchQuery = ref('')
    const statusFilter = ref('active')
    const perPage = ref(10)
    const currentPage = ref(1)
    const selectedCustomer = ref(null)
    const favoriteProductName = ref('')
    const favoriteProductQuantity = ref(null)
    const sortBy = ref(['created_at'])
    const sortDesc = ref([true])

    const pagination = ref({
      total: 0,
      per_page: 10,
      current_page: 1,
      last_page: 1
    })
    
    const totalCount = computed(() => pagination.value.total)
    

    const customerForm = reactive({
      name: '',
      email: '',
      phone: '',
      address: '',
      is_active: true
    })
    

    const headers = [
      { title: 'Customer', key: 'name', sortable: true },
      { title: 'Status', key: 'is_active', sortable: true },
      { title: 'Phone', key: 'phone', sortable: true },
      { title: 'Address', key: 'address', sortable: true },
      { title: 'Created', key: 'created_at', sortable: true },
      { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
    ]
    

    const statusOptions = [
      { title: 'Active', value: 'active' },
      { title: 'All', value: 'all' }
    ]
    
    const perPageOptions = [5, 10, 25, 50]
    

    const nameRules = [
      v => !!v || 'Name is required',
      v => (v && v.length >= 2) || 'Name must be at least 2 characters'
    ]
    
    const emailRules = [
      v => !!v || 'Email is required',
      v => /.+@.+\..+/.test(v) || 'Email must be valid'
    ]
    
    const phoneRules = [
      v => !v || /^[\+]?[1-9][\d]{0,15}$/.test(v) || 'Phone must be valid'
    ]
    

    const isFormValid = computed(() => {
      const isValid = customerForm.name && 
             customerForm.email && 
             /.+@.+\..+/.test(customerForm.email)
      return isValid
    })
    
    const switchValue = computed(() => {
      return customerForm.is_active
    })
    

    const fetchCustomers = async () => {
      loading.value = true
      try {
        const params = {
          per_page: perPage.value,
          page: currentPage.value
        }
        
        if (searchQuery.value) {
          params.qs = searchQuery.value
        }
        
        if (statusFilter.value === 'all') {
          params.with_inactive = true
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
                
        const response = await customerService.getCustomers(params)
        
        const apiCustomers = response.data.data || []
        customers.value = apiCustomers.map(customer => ({
          ...customer,
          is_active: Boolean(customer.is_active)
        }))
        pagination.value = {
          total: response.data.meta.total || 0,
          per_page: response.data.meta.per_page || 10,
          current_page: response.data.meta.current_page || 1,
          last_page: response.data.meta.last_page || 1
        }
                
      } catch (error) {
        console.error('Error fetching customers:', error)
      } finally {
        loading.value = false
      }
    }
    
    const debouncedSearch = (() => {
      let timeout
      return () => {
        clearTimeout(timeout)
        timeout = setTimeout(() => {
          currentPage.value = 1
          fetchCustomers()
        }, 500)
      }
    })()
    
    const onPageChange = (page) => {
      currentPage.value = page
      fetchCustomers()
    }
    
    const onItemsPerPageChange = (itemsPerPage) => {
      perPage.value = itemsPerPage
      currentPage.value = 1
      fetchCustomers()
    }
    
    const onSortChange = (newSortBy, newSortDesc) => {
      sortBy.value = newSortBy
      sortDesc.value = newSortDesc
      currentPage.value = 1
      fetchCustomers()
    }
    
    const formatDate = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
    
    const formatTime = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    
    const openCreateDialog = () => {
      isEditing.value = false
      resetForm()
      dialog.value = true
    }
    
    const editCustomer = async (customer) => {
      isEditing.value = true
      selectedCustomer.value = customer
      
      const formData = {
        ...customer,
        is_active: Boolean(customer.is_active)
      }
      
      Object.assign(customerForm, formData)
      dialog.value = true

      favoriteProductName.value = ''
      favoriteProductQuantity.value = null
      profileLoading.value = true
      try {
        const resp = await customerService.getCustomerProfile(customer.id)
        const payload = resp.data?.data || {}
        const fav = payload.favorite_product || null
        const products = payload.products || []
        if (fav) {
          const matched = products.find(p => p.id === fav.product_id)
          favoriteProductName.value = matched ? matched.name : `Product #${fav.product_id}`
          favoriteProductQuantity.value = fav.total_quantity ?? null
        }
      } catch (e) {
        console.error('Failed to load customer profile', e)
      } finally {
        profileLoading.value = false
      }
    }
    
    const viewCustomer = (customer) => {
      alert(`Customer Details:\nName: ${customer.name}\nEmail: ${customer.email}\nPhone: ${customer.phone}\nAddress: ${customer.address}\nStatus: ${customer.is_active ? 'Active' : 'Inactive'}`)
    }
    
    const deleteCustomer = (customer) => {
      selectedCustomer.value = customer
      deleteDialog.value = true
    }
    
    const confirmDelete = async () => {
      deleting.value = true
      try {
        await customerService.deleteCustomer(selectedCustomer.value.id)
        await fetchCustomers()
        deleteDialog.value = false
        selectedCustomer.value = null
      } catch (error) {
        console.error('Error deleting customer:', error)
      } finally {
        deleting.value = false
      }
    }
    
    const saveCustomer = async () => {
      if (!customerForm.name || !customerForm.email) {
        return
      }
      
      if (!/.+@.+\..+/.test(customerForm.email)) {
        return
      }
      
      const formData = {
        ...customerForm,
        is_active: customerForm.is_active ? 1 : 0
      }
      
      
      saving.value = true
      try {
        if (isEditing.value) {
          await customerService.updateCustomer(selectedCustomer.value.id, formData)
        } else {
          await customerService.createCustomer(formData)
        }
        
        await fetchCustomers()
        closeDialog()
      } catch (error) {
        console.error('Error saving customer:', error)
        closeDialog()
      } finally {
        saving.value = false
      }
    }
    
    const closeDialog = () => {
      dialog.value = false
      resetForm()
      selectedCustomer.value = null
      favoriteProductName.value = ''
      favoriteProductQuantity.value = null
      profileLoading.value = false
    }
    
    const resetForm = () => {
      Object.assign(customerForm, {
        name: '',
        email: '',
        phone: '',
        address: '',
        is_active: true
      })
    }
    
    watch(() => customerForm.is_active, (newValue, oldValue) => {
    })
    
    onMounted(() => {
      fetchCustomers()
    })
    
    return {
      customers,
      loading,
      saving,
      deleting,
      dialog,
      deleteDialog,
      formValid,
      form,
      isFormValid,
      switchValue,
      isEditing,
      searchQuery,
      statusFilter,
      perPage,
      currentPage,
      selectedCustomer,
      customerForm,
      pagination,
      
      headers,
      statusOptions,
      perPageOptions,
      
      nameRules,
      emailRules,
      phoneRules,
      
      fetchCustomers,
      debouncedSearch,
      onPageChange,
      onItemsPerPageChange,
      onSortChange,
      openCreateDialog,
      editCustomer,
      viewCustomer,
      deleteCustomer,
      confirmDelete,
      saveCustomer,
      closeDialog,
      formatDate,
      formatTime,
      sortBy,
      sortDesc,
      totalCount
      ,
      // profile related
      profileLoading,
      favoriteProductName,
      favoriteProductQuantity
    }
  }
}
</script>

<style scoped>
.customers-page {
  padding: 1rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #1976D2, #42A5F5);
  color: white;
  padding: 2rem;
  border-radius: 16px;
  margin-bottom: 2rem;
  box-shadow: 0 8px 32px rgba(25, 118, 210, 0.3);
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.page-subtitle {
  font-size: 1.1rem;
  margin: 0.5rem 0 0 0;
  opacity: 0.9;
}

.add-btn {
  border-radius: 12px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.add-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255, 255, 255, 0.3);
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
  min-width: 80px;
  justify-content: center;
}

.status-chip .v-icon {
  margin-right: 4px;
}

.customers-card {
  border-radius: 16px;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.table-header {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-radius: 16px 16px 0 0;
  font-weight: 600;
  color: #495057;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-left {
  display: flex;
  align-items: center;
}

.header-filters {
  display: flex;
  align-items: center;
  gap: 1rem;
  min-width: 300px;
  flex-wrap: wrap;
}

.header-right {
  display: flex;
  align-items: center;
}

.table-title {
  font-size: 1.2rem;
  font-weight: 600;
}

.customers-table {
  border-radius: 0 0 16px 16px;
}

.customer-info {
  display: flex;
  align-items: center;
}

.customer-name {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.25rem;
}

.customer-email {
  font-size: 0.9rem;
  color: #6c757d;
}

.phone-info, .address-info {
  display: flex;
  align-items: center;
  color: #6c757d;
  font-size: 0.9rem;
}

.date-cell {
  display: flex;
  flex-direction: column;
}

.date-text {
  font-weight: 500;
  font-size: 0.875rem;
}

.time-text {
  font-size: 0.75rem;
  color: #666;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  transition: all 0.2s ease;
}

.action-btn:hover {
  transform: scale(1.1);
}

.customer-dialog {
  border-radius: 16px;
  overflow: hidden;
}

.dialog-header {
  background: linear-gradient(135deg, #1976D2, #42A5F5);
  color: white;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  font-weight: 600;
}

@media (max-width: 960px) {
  .page-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .page-title {
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
  .customers-page {
    padding: 0.5rem;
  }
  
  .page-header {
    padding: 1.5rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 0.25rem;
  }
}
</style>
