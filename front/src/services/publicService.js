import api from './api'

export default {
  properties: (params = {}) => api.get('/public/properties', { params }),
  featured: () => api.get('/public/properties/featured'),
  property: (slug) => api.get(`/public/properties/${slug}`),
  testimonials: () => api.get('/public/testimonials'),
  banners: () => api.get('/public/banners'),
  createLead: (payload) => api.post('/public/leads', payload)
}
