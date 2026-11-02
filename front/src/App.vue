<template>
  <div id="app">
    <PageLoader :show="pageLoading" :title="$env && $env.appName ? $env.appName : 'Inmobiliaria Real Estate'" />
    <router-view />
  </div>
</template>
<script>
import PageLoader from '@/components/common/PageLoader.vue'
import loaderBus from '@/utils/loaderBus'

export default {
  name: 'App',

  components: {
    PageLoader
  },

  data () {
    return {
      pageLoading: true,
      activeRequests: 0,
      hideTimer: null,
      loaderStartedAt: null,
      minLoaderTime: 700
    }
  },

  mounted () {
    loaderBus.$on('loader:start', this.startLoader)
    loaderBus.$on('loader:stop', this.stopLoader)

    // Cargador inicial de entrada
    setTimeout(() => {
      this.tryHideLoader()
    }, 900)
  },

  beforeDestroy () {
    loaderBus.$off('loader:start', this.startLoader)
    loaderBus.$off('loader:stop', this.stopLoader)

    if (this.hideTimer) {
      clearTimeout(this.hideTimer)
    }
  },

  methods: {
    startLoader () {
      this.activeRequests++

      if (this.hideTimer) {
        clearTimeout(this.hideTimer)
        this.hideTimer = null
      }

      if (!this.pageLoading) {
        this.loaderStartedAt = Date.now()
        this.pageLoading = true
      }

      if (!this.loaderStartedAt) {
        this.loaderStartedAt = Date.now()
      }
    },

    stopLoader () {
      this.activeRequests = Math.max(this.activeRequests - 1, 0)
      this.tryHideLoader()
    },

    tryHideLoader () {
      if (this.activeRequests > 0) return

      const elapsed = Date.now() - (this.loaderStartedAt || Date.now())
      const remaining = Math.max(this.minLoaderTime - elapsed, 250)

      if (this.hideTimer) {
        clearTimeout(this.hideTimer)
      }

      this.hideTimer = setTimeout(() => {
        if (this.activeRequests === 0) {
          this.pageLoading = false
          this.loaderStartedAt = null
        }
      }, remaining)
    }
  }
}
</script>