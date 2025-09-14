<template>
  <v-app>
    <v-app-bar
      color="primary"
      dark
      prominent
      v-if="user"
    >
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      
      <v-toolbar-title>TimeFlow Order Management</v-toolbar-title>
      
      <v-spacer></v-spacer>
      
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn icon v-bind="props">
            <v-icon>mdi-account</v-icon>
          </v-btn>
        </template>
        <v-list>
          <v-list-item>
            <v-list-item-title>{{ user?.name || 'User' }}</v-list-item-title>
            <v-list-item-subtitle>{{ user?.email }}</v-list-item-subtitle>
          </v-list-item>
          <v-divider></v-divider>
          <v-list-item @click="handleLogout">
            <v-list-item-title>Logout</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <v-navigation-drawer
      v-model="drawer"
      temporary
    >
      <v-list>
        <v-list-item
          prepend-icon="mdi-view-dashboard"
          title="Dashboard"
          value="dashboard"
          to="/"
        ></v-list-item>
        
        <v-list-item
          prepend-icon="mdi-account-group"
          title="Customers"
          value="customers"
          to="/customers"
        ></v-list-item>
        
        <v-list-item
          prepend-icon="mdi-package-variant"
          title="Products"
          value="products"
          to="/products"
        ></v-list-item>
        
        <v-list-item
          prepend-icon="mdi-shopping"
          title="Orders"
          value="orders"
          to="/orders"
        ></v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <v-container fluid>
        <router-view />
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'App',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    const drawer = ref(false)
    
    const user = computed(() => authStore.user)
    
    const handleLogout = async () => {
      await authStore.logout()
      router.push('/login')
    }
    
    return {
      drawer,
      user,
      handleLogout
    }
  }
}
</script>
