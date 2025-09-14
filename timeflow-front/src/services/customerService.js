import api from './api'

export const customerService = {
  getCustomers(params = {}) {
    return api.get('/customers', { params })
  },

  getCustomer(id) {
    return api.get(`/customers/${id}`)
  },

  getCustomerProfile(id) {
    return api.get(`/customers/${id}/profile`)
  },

  createCustomer(customerData) {
    return api.post('/customers', customerData)
  },

  updateCustomer(id, customerData) {
    return api.put(`/customers/${id}`, customerData)
  },

  deleteCustomer(id) {
    return api.delete(`/customers/${id}`)
  }
}
