<template>
  <main class="admin-login-screen">
    <section class="admin-login-card">
      <span class="brand-mark">A</span>
      <h1>{{ $env.appName }}</h1>
      <p>Acceso JWT para administrar propiedades, leads y contenido.</p>

      <form @submit.prevent="login">
        <input v-model="form.email" type="email" class="form-control" placeholder="Correo" required>
        <input v-model="form.password" type="password" class="form-control" placeholder="Password" required>
        <button class="btn btn-premium w-100" :disabled="loading">{{ loading ? 'Entrando...' : 'Entrar' }}</button>
      </form>

      <div class="demo-login-actions">
        <button class="btn btn-outline-premium btn-sm" @click="demoAdmin">Demo admin</button>
        <button class="btn btn-outline-premium btn-sm" @click="demoAdvisor">Demo asesor</button>
      </div>

      <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>
    </section>
  </main>
</template>

<script>
import authService from '@/services/authService'
import authStore from '@/store/authStore'

export default {
  name: 'AdminLogin',
  data () {
    return {
      loading: false,
      error: '',
      form: { email: 'vic.hug.urib@gmail.com', password: 'password123' }
    }
  },
  methods: {
    async login () {
      this.loading = true
      this.error = ''
      try {
        await authService.login(this.form)
        this.$router.push({ name: 'admin-dashboard' })
      } catch (error) {
        this.error = 'No se pudo iniciar sesion con API. Puedes usar los accesos demo.'
      }
      this.loading = false
    },
    demoAdmin () {
      authStore.setSession('demo-admin', { id: 1, name: 'Administrador', role_id: 1, role: { name: 'Super Admin', slug: 'super-admin' } })
      this.$router.push({ name: 'admin-dashboard' })
    },
    demoAdvisor () {
      authStore.setSession('demo-asesor', { id: 3, name: 'Asesor Ixtapaluca', role_id: 3, role: { name: 'Asesor', slug: 'asesor' } })
      this.$router.push({ name: 'admin-dashboard' })
    }
  }
}
</script>
