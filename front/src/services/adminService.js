import api from './api'

const multipart = (payload) => payload instanceof FormData
  ? { headers: { 'Content-Type': 'multipart/form-data' } }
  : undefined
  
export default {
  dashboard: () => api.get('/dashboard'),
  properties: (params = {}) => api.get('/properties', { params }),
  saveProperty (payload, id) {
    if (payload instanceof FormData && id) payload.append('_method', 'PUT')
    if (id && !(payload instanceof FormData)) return api.put(`/properties/${id}`, payload)
    return api.post(id ? `/properties/${id}` : '/properties', payload, multipart(payload))
  },
  deleteProperty: (id) => api.delete(`/properties/${id}`),
  uploadPropertyImage: (id, formData) => api.post(`/properties/${id}/images`, formData, multipart(formData)),
  setMainImage: (id) => api.patch(`/property-images/${id}/main`),
  deletePropertyImage: (id) => api.delete(`/property-images/${id}`),
  savePropertyBuyer: (id, payload) => api.post(`/properties/${id}/buyer`, payload),
  propertyPayments: (id) => api.get(`/properties/${id}/payments`),
  savePropertyPayment: (id, payload) => api.post(`/properties/${id}/payments`, payload),
  deletePropertyPayment: (id) => api.delete(`/property-payments/${id}`),

  leads: () => api.get('/leads'),
  updateLead: (id, payload) => api.patch(`/leads/${id}`, payload),

  users: () => api.get('/users'),
  saveUser: (payload, id) => id ? api.put(`/users/${id}`, payload) : api.post('/users', payload),
  deleteUser: (id) => api.delete(`/users/${id}`),

  testimonials: () => api.get('/testimonials'),
  saveTestimonial (payload, id) {
    if (payload instanceof FormData && id) payload.append('_method', 'PUT')
    if (id && !(payload instanceof FormData)) return api.put(`/testimonials/${id}`, payload)
    return api.post(id ? `/testimonials/${id}` : '/testimonials', payload, multipart(payload))
  },
  deleteTestimonial: (id) => api.delete(`/testimonials/${id}`),

  banners: () => api.get('/banners'),
  saveBanner (payload, id) {
    if (payload instanceof FormData && id) payload.append('_method', 'PUT')
    if (id && !(payload instanceof FormData)) return api.put(`/banners/${id}`, payload)
    return api.post(id ? `/banners/${id}` : '/banners', payload, multipart(payload))
  },
  deleteBanner: (id) => api.delete(`/banners/${id}`)
}
