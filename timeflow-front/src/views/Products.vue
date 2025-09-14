<template>
  <div class="products-page">
    <v-row class="mb-6">
      <v-col cols="12">
        <div class="page-header">
          <div class="header-content">
            <div class="header-left">
              <v-icon size="48" color="primary" class="header-icon">mdi-package-variant</v-icon>
              <div class="header-text">
                <h1 class="page-title">Product Management</h1>
                <p class="page-subtitle">Manage your product catalog and inventory</p>
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
                Add Product
              </v-btn>
            </div>
          </div>
        </div>
      </v-col>
    </v-row>
    
    <v-row>
      <v-col cols="12">
        <v-card class="products-card" elevation="8">
          <v-card-title class="table-header">
            <div class="header-left">
              <span class="table-title">Product List</span>
            </div>
            
            <div class="header-filters">
              <v-text-field
                v-model="searchQuery"
                label="Search products..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                clearable
                @input="debouncedSearch"
                class="search-field"
                density="compact"
                style="min-width: 300px;"
              ></v-text-field>
            </div>
            
            <div class="header-right">
              <v-chip color="primary" variant="outlined">
                {{ pagination.total }} products
              </v-chip>
            </div>
          </v-card-title>
          
          <v-data-table-server
            :headers="headers"
            :items="products"
            :loading="loading"
            class="products-table"
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
            <template v-slot:item.name="{ item }">
              <div class="product-info">
                <v-avatar size="40" color="primary" class="mr-3">
                  <v-icon color="white">mdi-package</v-icon>
                </v-avatar>
                <div>
                  <div class="product-name">{{ item.name }}</div>
                  <div class="product-sku">{{ item.sku }}</div>
                </div>
              </div>
            </template>

            <template v-slot:item.price="{ item }">
              <div class="price-info">
                <v-icon size="16" class="mr-1">mdi-currency-usd</v-icon>
                {{ formatPrice(item.price) }}
              </div>
            </template>

            <template v-slot:item.stock_quantity="{ item }">
              <v-chip
                :color="getStockColor(item.stock_quantity)"
                variant="flat"
                size="small"
                class="stock-chip"
              >
                <v-icon 
                  :color="getStockColor(item.stock_quantity) === 'red' ? 'white' : 'white'" 
                  size="16" 
                  class="mr-1"
                >
                  {{ getStockIcon(item.stock_quantity) }}
                </v-icon>
                {{ item.stock_quantity }}
              </v-chip>
            </template>


            <template v-slot:item.actions="{ item }">
              <div class="action-buttons">
                <v-btn
                  icon="mdi-pencil"
                  size="small"
                  color="warning"
                  variant="text"
                  @click="editProduct(item)"
                  class="action-btn"
                ></v-btn>
                <v-btn
                  icon="mdi-delete"
                  size="small"
                  color="error"
                  variant="text"
                  @click="deleteProduct(item)"
                  class="action-btn"
                ></v-btn>
              </div>
            </template>
          </v-data-table-server>
        </v-card>
      </v-col>
    </v-row>

    <v-dialog v-model="dialog" max-width="700" persistent>
      <v-card class="product-dialog">
        <v-card-title class="dialog-header">
          <v-icon size="24" class="mr-2">
            {{ isEditing ? 'mdi-pencil' : 'mdi-plus' }}
          </v-icon>
          {{ isEditing ? 'Edit Product' : 'Add New Product' }}
        </v-card-title>
        
        <v-card-text class="pa-6">
          <v-form ref="form" @submit.prevent="saveProduct">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="productForm.name"
                  label="Product Name"
                  :rules="nameRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-package"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="productForm.sku"
                  label="SKU"
                  :rules="skuRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-barcode"
                  required
                ></v-text-field>
              </v-col>
            </v-row>
            
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="productForm.price"
                  label="Price"
                  type="number"
                  step="0.01"
                  :rules="priceRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-currency-usd"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="productForm.stock_quantity"
                  label="Stock Quantity"
                  type="number"
                  :rules="stockRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-package-variant"
                  required
                ></v-text-field>
              </v-col>
            </v-row>
            
            
            <v-row>
              <v-col cols="12">
                <v-textarea
                  v-model="productForm.description"
                  label="Description"
                  variant="outlined"
                  prepend-inner-icon="mdi-text"
                  rows="3"
                ></v-textarea>
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
            @click="saveProduct"
            :loading="saving"
            :disabled="!isFormValid"
          >
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h5">
          <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
          Delete Product
        </v-card-title>
        <v-card-text>
          Are you sure you want to delete <strong>{{ selectedProduct?.name }}</strong>?
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
import { productService } from '@/services/productService'

export default {
  name: 'Products',
  setup() {
    const products = ref([])
    const loading = ref(false)
    const saving = ref(false)
    const deleting = ref(false)
    const dialog = ref(false)
    const deleteDialog = ref(false)
    const formValid = ref(false)
    const form = ref(null)
    const isEditing = ref(false)
    const searchQuery = ref('')
    const perPage = ref(10)
    const currentPage = ref(1)
    const selectedProduct = ref(null)
    const sortBy = ref(['created_at'])
    const sortDesc = ref([true])
    
    const pagination = ref({
      total: 0,
      per_page: 10,
      current_page: 1,
      last_page: 1
    })
    
    const productForm = reactive({
      name: '',
      sku: '',
      price: '',
      stock_quantity: '',
      description: ''
    })
    
    const headers = [
      { title: 'Product', key: 'name', sortable: true },
      { title: 'Price', key: 'price', sortable: true },
      { title: 'Stock', key: 'stock_quantity', sortable: true },
      { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
    ]
    
    const perPageOptions = [5, 10, 25, 50]
    
    const nameRules = [
      v => !!v || 'Product name is required',
      v => (v && v.length >= 2) || 'Product name must be at least 2 characters'
    ]
    
    const skuRules = [
      v => !!v || 'SKU is required',
      v => (v && v.length >= 3) || 'SKU must be at least 3 characters'
    ]
    
    const priceRules = [
      v => !!v || 'Price is required',
      v => (v && v > 0) || 'Price must be greater than 0'
    ]
    
    const stockRules = [
      v => v !== '' || 'Stock quantity is required',
      v => (v >= 0) || 'Stock quantity cannot be negative'
    ]
    
    const isFormValid = computed(() => {
      const isValid = productForm.name && 
             productForm.sku && 
             productForm.price && 
             productForm.stock_quantity !== '' &&
             productForm.price > 0 &&
             productForm.stock_quantity >= 0
      return isValid
    })
    
    const fetchProducts = async () => {
      loading.value = true
      try {
        const params = {
          per_page: perPage.value,
          page: currentPage.value
        }
        
        if (searchQuery.value) {
          params.qs = searchQuery.value
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
        
        const response = await productService.getProducts(params)
        products.value = response.data.data || []
        pagination.value = {
          total: response.data.meta.total || 0,
          per_page: response.data.meta.per_page || 10,
          current_page: response.data.meta.current_page || 1,
          last_page: response.data.meta.last_page || 1
        }
      } catch (error) {
        console.error('Error fetching products:', error)
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
          fetchProducts()
        }, 500)
      }
    })()
    
    const onPageChange = (page) => {
      currentPage.value = page
      fetchProducts()
    }
    
    const onItemsPerPageChange = (itemsPerPage) => {
      perPage.value = itemsPerPage
      currentPage.value = 1
      fetchProducts()
    }
    
    const onSortChange = (newSortBy, newSortDesc) => {
      sortBy.value = newSortBy
      sortDesc.value = newSortDesc
      currentPage.value = 1
      fetchProducts()
    }
    
    const openCreateDialog = () => {
      isEditing.value = false
      resetForm()
      dialog.value = true
    }
    
    const editProduct = (product) => {
      isEditing.value = true
      selectedProduct.value = product
      Object.assign(productForm, product)
      dialog.value = true
    }
    
    const deleteProduct = (product) => {
      selectedProduct.value = product
      deleteDialog.value = true
    }
    
    const confirmDelete = async () => {
      deleting.value = true
      try {
        await productService.deleteProduct(selectedProduct.value.id)
        await fetchProducts()
        deleteDialog.value = false
        selectedProduct.value = null
      } catch (error) {
        console.error('Error deleting product:', error)
      } finally {
        deleting.value = false
      }
    }
    
    const saveProduct = async () => {
      if (!productForm.name || !productForm.sku || !productForm.price || productForm.stock_quantity === '') {
        return
      }
      
      if (productForm.price <= 0 || productForm.stock_quantity < 0) {
        return
      }
      
      const formData = {
        ...productForm,
        price: parseFloat(productForm.price),
        stock_quantity: parseInt(productForm.stock_quantity)
      }
      
      saving.value = true
      try {
        if (isEditing.value) {
          await productService.updateProduct(selectedProduct.value.id, formData)
        } else {
          await productService.createProduct(formData)
        }
        
        await fetchProducts()
        closeDialog()
      } catch (error) {
        console.error('Error saving product:', error)
        closeDialog()
      } finally {
        saving.value = false
      }
    }
    
    const closeDialog = () => {
      dialog.value = false
      resetForm()
      selectedProduct.value = null
    }
    
    const resetForm = () => {
      Object.assign(productForm, {
        name: '',
        sku: '',
        price: '',
        stock_quantity: '',
        description: ''
      })
    }
    
    const formatPrice = (price) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(price)
    }
    
    const getStockColor = (stock) => {
      if (stock === 0) return 'red'
      if (stock < 10) return 'orange'
      return 'green'
    }
    
    const getStockIcon = (stock) => {
      if (stock === 0) return 'mdi-close-circle'
      if (stock < 10) return 'mdi-alert-circle'
      return 'mdi-check-circle'
    }
    
    
    onMounted(() => {
      fetchProducts()
    })
    
    return {
      products,
      loading,
      saving,
      deleting,
      dialog,
      deleteDialog,
      formValid,
      form,
      isFormValid,
      isEditing,
      searchQuery,
      perPage,
      currentPage,
      selectedProduct,
      productForm,
      pagination,
      
      headers,
      perPageOptions,
      
      nameRules,
      skuRules,
      priceRules,
      stockRules,
      
      fetchProducts,
      debouncedSearch,
      onPageChange,
      onItemsPerPageChange,
      onSortChange,
      openCreateDialog,
      editProduct,
      deleteProduct,
      confirmDelete,
      saveProduct,
      sortBy,
      sortDesc,
      closeDialog,
      formatPrice,
      getStockColor,
      getStockIcon
    }
  }
}
</script>

<style scoped>
.products-page {
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


.stock-chip {
  font-weight: 600;
  min-width: 60px;
  justify-content: center;
}

.stock-chip .v-icon {
  margin-right: 4px;
}

.products-card {
  border-radius: 16px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.products-table {
  background: white;
}

.product-info {
  display: flex;
  align-items: center;
}

.product-name {
  font-weight: 600;
  color: #212529;
  font-size: 0.95rem;
}

.product-sku {
  font-size: 0.8rem;
  color: #6c757d;
  font-weight: 500;
}

.price-info {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: #28a745;
  font-size: 0.95rem;
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

.product-dialog {
  border-radius: 16px;
}

.dialog-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-weight: 600;
  font-size: 1.25rem;
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
  
  .search-field {
    max-width: 100% !important;
  }
}

@media (max-width: 600px) {
  .products-page {
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