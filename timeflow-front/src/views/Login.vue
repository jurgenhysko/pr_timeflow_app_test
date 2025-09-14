<template>
  <div>
    <v-container fluid class="pa-4">
      <v-row justify="center" align="center" class="fill-height">
        <v-col cols="12" sm="10" md="8" lg="6" xl="4">
          <v-card class="login-card" elevation="20">
            <div class="login-header">
              <div class="logo-container">
                <h1 class="logo-text">TimeFlow</h1>
                <p class="logo-subtitle">Order Management System</p>
              </div>
            </div>
            
            <v-card-text class="pa-6">
              <v-form @submit.prevent="handleLogin" class="login-form">
                <v-text-field
                  v-model="form.email"
                  label="Email Address"
                  type="email"
                  required
                  :error-messages="errors.email"
                  prepend-inner-icon="mdi-email-outline"
                  variant="outlined"
                  class="mb-4"
                  color="primary"
                ></v-text-field>
                
                <v-text-field
                  v-model="form.password"
                  label="Password"
                  type="password"
                  required
                  :error-messages="errors.password"
                  prepend-inner-icon="mdi-lock-outline"
                  variant="outlined"
                  class="mb-6"
                  color="primary"
                ></v-text-field>
                
                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  block
                  :loading="loading"
                  class="login-btn mb-4"
                  elevation="4"
                >
                  <v-icon left>mdi-login</v-icon>
                  Sign In
                </v-btn>
                
                <v-divider class="my-6">
                  <span class="divider-text">OR</span>
                </v-divider>
                
                <v-btn
                  color="secondary"
                  variant="outlined"
                  size="large"
                  block
                  @click="showRegister = true"
                  class="register-btn"
                  elevation="2"
                >
                  <v-icon left>mdi-account-plus</v-icon>
                  Create New Account
                </v-btn>
              </v-form>
            </v-card-text>
        </v-card>
        
        <v-dialog v-model="showRegister" max-width="600" persistent>
            <v-card class="register-card">
              <v-card-title class="register-header">
                <v-icon size="40" color="primary" class="mr-3">mdi-account-plus</v-icon>
                <div>
                  <h2>Create Account</h2>
                  <p class="text-subtitle-1 text-grey">Join TimeFlow today</p>
                </div>
              </v-card-title>
              
              <v-card-text class="pa-6">
                <v-form @submit.prevent="handleRegister" class="register-form">
                  <v-text-field
                    v-model="registerForm.name"
                    label="Full Name"
                    required
                    :error-messages="registerErrors.name"
                    prepend-inner-icon="mdi-account-outline"
                    variant="outlined"
                    class="mb-4"
                    color="primary"
                  ></v-text-field>
                  
                  <v-text-field
                    v-model="registerForm.email"
                    label="Email Address"
                    type="email"
                    required
                    :error-messages="registerErrors.email"
                    prepend-inner-icon="mdi-email-outline"
                    variant="outlined"
                    class="mb-4"
                    color="primary"
                  ></v-text-field>
                  
                  <v-text-field
                    v-model="registerForm.password"
                    label="Password"
                    type="password"
                    required
                    :error-messages="registerErrors.password"
                    prepend-inner-icon="mdi-lock-outline"
                    variant="outlined"
                    class="mb-6"
                    color="primary"
                  ></v-text-field>
                  
                  <v-btn
                    type="submit"
                    color="primary"
                    size="large"
                    block
                    :loading="registerLoading"
                    class="register-submit-btn mb-3"
                    elevation="4"
                  >
                    <v-icon left>mdi-account-plus</v-icon>
                    Create Account
                  </v-btn>
                  
                  <v-btn
                    color="grey"
                    variant="text"
                    block
                    @click="showRegister = false"
                  >
                    Already have an account? Sign In
                  </v-btn>
                </v-form>
              </v-card-text>
            </v-card>
          </v-dialog>
      </v-col>
    </v-row>
  </v-container>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'Login',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    
    const loading = ref(false)
    const registerLoading = ref(false)
    const showRegister = ref(false)
    
    const form = reactive({
      email: '',
      password: ''
    })
    
    const registerForm = reactive({
      name: '',
      email: '',
      password: ''
    })
    
    const errors = ref({})
    const registerErrors = ref({})
    
    const handleLogin = async () => {
      loading.value = true
      errors.value = {}
      
      try {
        await authStore.login(form)
        router.push('/')
      } catch (error) {
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        } else {
          errors.value = { general: ['Login failed. Please try again.'] }
        }
      } finally {
        loading.value = false
      }
    }
    
    const handleRegister = async () => {
      registerLoading.value = true
      registerErrors.value = {}
      
      try {
        await authStore.register(registerForm)
        showRegister.value = false
        router.push('/')
      } catch (error) {
        if (error.response?.data?.errors) {
          registerErrors.value = error.response.data.errors
        } else {
          registerErrors.value = { general: ['Registration failed. Please try again.'] }
        }
      } finally {
        registerLoading.value = false
      }
    }
    
    return {
      form,
      registerForm,
      errors,
      registerErrors,
      loading,
      registerLoading,
      showRegister,
      handleLogin,
      handleRegister
    }
  }
}
</script>

<style scoped>

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  position: relative;
  z-index: 1;
  animation: slideUp 0.8s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.login-header {
  background: linear-gradient(135deg, #1976D2, #42A5F5);
  color: white;
  padding: 1.5rem;
  border-radius: 20px 20px 0 0;
  text-align: center;
}

.logo-container {
  animation: pulse 2s infinite;
}

.logo-icon {
  margin-bottom: 1rem;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
}

.logo-text {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.logo-subtitle {
  font-size: 1rem;
  margin: 0.5rem 0 0 0;
  opacity: 0.9;
}

.login-form {
  animation: fadeIn 1s ease-out 0.3s both;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.login-btn {
  background: linear-gradient(135deg, #1976D2, #42A5F5);
  border-radius: 12px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.login-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(25, 118, 210, 0.3);
}

.register-btn {
  border-radius: 12px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.register-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.divider-text {
  background: white;
  padding: 0 1rem;
  color: #666;
  font-weight: 500;
}

.register-card {
  border-radius: 20px;
  overflow: hidden;
}

.register-header {
  background: linear-gradient(135deg, #1976D2, #42A5F5);
  color: white;
  padding: 2rem;
  display: flex;
  align-items: center;
}

.register-submit-btn {
  background: linear-gradient(135deg, #1976D2, #42A5F5);
  border-radius: 12px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.register-submit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(25, 118, 210, 0.3);
}

/* Responsive Design */
@media (max-width: 600px) {
  .login-header {
    padding: 1.5rem;
  }
  
  .logo-text {
    font-size: 2rem;
  }
  
  .shape {
    display: none;
  }
}
</style>
