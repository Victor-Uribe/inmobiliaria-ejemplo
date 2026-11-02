<template>
  <div class="admin-shell">
    <button
      class="admin-sidebar-overlay"
      type="button"
      aria-label="Cerrar menu"
      v-if="sidebarOpen"
      @click="closeSidebar"
    ></button>

    <aside :class="['admin-sidebar', { 'is-open': sidebarOpen }]">
      <router-link class="admin-brand admin-brand-dashboard" :to="{ name: 'admin-dashboard' }" @click.native="closeSidebar">
        <span class="admin-logo">A</span>
        <span>
          <strong>{{ $env.appName }}</strong>
          <small>DASHBOARD</small>
        </span>
      </router-link>

      <nav class="admin-menu">
        <span class="admin-menu-label">Overview</span>
        <router-link :to="{ name: 'admin-dashboard' }" @click.native="closeSidebar">
          <span class="admin-menu-icon">▦</span>
          <span>Dashboard</span>
        </router-link>

        <span class="admin-menu-label">Commerce</span>
        <router-link :to="{ name: 'admin-properties' }" @click.native="closeSidebar">
          <span class="admin-menu-icon">⌂</span>
          <span>Propiedades</span>
        </router-link>
        <router-link v-if="canManage" :to="{ name: 'admin-leads' }" @click.native="closeSidebar">
          <span class="admin-menu-icon">◌</span>
          <span>Leads</span>
          <!-- <small class="admin-menu-badge">CRM</small> -->
        </router-link>

        <template v-if="canManage">
          <span class="admin-menu-label">Content</span>
          <router-link :to="{ name: 'admin-testimonials' }" @click.native="closeSidebar">
            <span class="admin-menu-icon">❝</span>
            <span>Testimonios</span>
          </router-link>
          <router-link :to="{ name: 'admin-banners' }" @click.native="closeSidebar">
            <span class="admin-menu-icon">▤</span>
            <span>Banners</span>
          </router-link>

          <span class="admin-menu-label">System</span>
          <router-link :to="{ name: 'admin-users' }" @click.native="closeSidebar">
            <span class="admin-menu-icon">◎</span>
            <span>Usuarios</span>
          </router-link>
        </template>

        <span class="admin-menu-label">Public</span>
        <router-link :to="{ name: 'home' }" @click.native="closeSidebar">
          <span class="admin-menu-icon">↗</span>
          <span>Ver landing</span>
        </router-link>
      </nav>
    </aside>

    <main class="admin-main">
      <header class="admin-topbar">
        <button class="admin-menu-toggle" type="button" aria-label="Abrir menu" @click="toggleSidebar">
          <span></span>
          <span></span>
          <span></span>
        </button>

        <div class="admin-search">
        
        </div>

        <div class="admin-topbar-actions">
          <router-link class="btn btn-premium btn-sm" :to="{ name: 'admin-properties' }">
            <i class="fa-solid fa-plus"></i>
            Nueva propiedad
          </router-link>
          <div class="admin-user-chip">
            <span>{{ initials }}</span>
            <div>
              <strong>{{ user.name || 'Usuario' }}</strong>
              <small>{{ roleLabel }}</small>
            </div>
          </div>
          <button class="btn btn-outline-premium btn-sm" @click="logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Salir
          </button>
        </div>
      </header>
      <section class="admin-content">
        <router-view />
      </section>
    </main>
  </div>
</template>

<script>
import authStore from '@/store/authStore'
import authService from '@/services/authService'
import { canManageSystem } from '@/utils/permissions'

export default {
  name: 'AdminLayout',
  data () {
    return {
      sidebarOpen: false
    }
  },
  computed: {
    user () {
      return authStore.getUser() || {}
    },
    roleLabel () {
      return this.user.role ? this.user.role.name || this.user.role.slug : 'Sin rol'
    },
    canManage () {
      return canManageSystem()
    },
    initials () {
      const name = this.user.name || 'A'
      return name.split(' ').map(part => part[0]).join('').slice(0, 2).toUpperCase()
    }
  },
  methods: {
    toggleSidebar () {
      this.sidebarOpen = !this.sidebarOpen
    },
    closeSidebar () {
      this.sidebarOpen = false
    },
    async logout () {
      this.closeSidebar()
      await authService.logout()
      this.$router.push({ name: 'admin-login' })
    }
  }
}
</script>
