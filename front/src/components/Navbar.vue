<template>
  <nav :class="['navbar navbar-expand-lg fixed-top premium-navbar', { 'is-scrolled': isScrolled }]">
    <div class="container">
      <router-link class="navbar-brand d-flex align-items-center" :to="{ name: 'home', hash: '#inicio' }" aria-label="Inicio">
        <span class="brand-mark">A</span>
        <span class="brand-copy">
          <strong>{{ $env.appName }}</strong>
          <small>Real Estate</small>
        </span>
      </router-link>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNavbar"
        aria-controls="mainNavbar"
        aria-expanded="false"
        aria-label="Abrir menú"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div id="mainNavbar" class="collapse navbar-collapse">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
          <li class="nav-item"><router-link class="nav-link" :to="{ name: 'home', hash: '#inicio' }">Inicio</router-link></li>
          <li class="nav-item"><router-link class="nav-link" :to="{ name: 'properties' }">Propiedades</router-link></li>
          <li class="nav-item"><router-link class="nav-link" :to="{ name: 'home', hash: '#beneficios' }">Beneficios</router-link></li>
          <li class="nav-item"><router-link class="nav-link" :to="{ name: 'home', hash: '#testimonios' }">Testimonios</router-link></li>
          <li class="nav-item"><router-link class="nav-link" :to="{ name: 'home', hash: '#ubicacion' }">Ubicación</router-link></li>
          <li class="nav-item"><router-link class="nav-link" :to="{ name: 'admin-login' }">Admin</router-link></li>
        </ul>

        <a class="btn btn-premium btn-sm" :href="whatsappLink" target="_blank" rel="noopener">
          Solicitar información
        </a>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'Navbar',
  props: {
    whatsapp: {
      type: String,
      required: true
    }
  },
  data () {
    return {
      isScrolled: false
    }
  },
  computed: {
    whatsappLink () {
      const text = encodeURIComponent('Hola, quiero solicitar información sobre propiedades en Ixtapaluca con {{ $env.appName }}. Quiero más información.')
      return `https://wa.me/${this.whatsapp}?text=${text}`
    }
  },
  mounted () {
    this.onScroll()
    window.addEventListener('scroll', this.onScroll, { passive: true })
  },
  beforeDestroy () {
    window.removeEventListener('scroll', this.onScroll)
  },
  methods: {
    onScroll () {
      this.isScrolled = window.scrollY > 24
    }
  }
}
</script>
