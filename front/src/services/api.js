import axios from 'axios'
import authStore from '@/store/authStore'
import loaderBus from '@/utils/loaderBus'

const api = axios.create({
  baseURL: process.env.VUE_APP_API_URL || 'https://api.maviko.com.mx/inmobiliaria/public/api',
  headers: {
    Accept: 'application/json'
  }
})

api.interceptors.request.use(
  (config) => {
    if (config.showLoader !== false) {
      config._showLoader = true
      loaderBus.$emit('loader:start')
    }

    const token = authStore.getToken()

    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  (error) => {
    loaderBus.$emit('loader:stop')
    return Promise.reject(error)
  }
)

api.interceptors.response.use(
  (response) => {
    if (response.config && response.config._showLoader) {
      loaderBus.$emit('loader:stop')
    }

    return response
  },
  (error) => {
    if (error.config && error.config._showLoader) {
      loaderBus.$emit('loader:stop')
    }

    if (error.response && error.response.status === 401) {
      authStore.logout()
    }

    return Promise.reject(error)
  }
)

export default api