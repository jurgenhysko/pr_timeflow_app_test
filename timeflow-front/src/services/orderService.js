import api from './api'

export const orderService = {
  getOrders(params = {}) {
    return api.get('/orders', { params })
  },

  getOrder(id) {
    return api.get(`/orders/${id}`)
  },

  createOrder(orderData) {
    return api.post('/orders', orderData)
  },

  updateOrder(id, orderData) {
    return api.put(`/orders/${id}`, orderData)
  },

  deleteOrder(id) {
    return api.delete(`/orders/${id}`)
  }
}
