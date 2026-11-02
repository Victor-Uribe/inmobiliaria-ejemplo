import api from './api'
import authStore from '@/store/authStore'

export default {
  async login (credentials) {
    const response = await api.post('/auth/login', credentials)
    const data = response.data.data || response.data
    const token = data.access_token || data.token || data.jwt
    const user = data.user || data.auth_user || data
    authStore.setSession(token, user)
    return data
  },
  async logout () {
    try {
      await api.post('/auth/logout')
    } finally {
      authStore.logout()
    }
  }
}
