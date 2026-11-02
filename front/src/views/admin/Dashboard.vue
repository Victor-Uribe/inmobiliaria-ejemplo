<template>
  <section>
    <div class="admin-page-title">
      <span>Panel</span>
      <h1>{{ isAdvisor ? 'Vista asesor' : 'Dashboard general' }}</h1>
      <p>{{ isAdvisor ? 'Solo metricas y propiedades asignadas a tu usuario.' : 'Resumen comercial del sistema.' }}</p>
    </div>

    <div class="row g-4 admin-metric-row">
      <div v-for="card in visibleCards" :key="card.label" class="col-md-6 col-xl-3">
        <article :class="['admin-metric', `tone-${card.tone}`]" class="bt-b">
          <div class="admin-metric-head ">
            <span>{{ card.label }}</span>
            <i>{{ card.icon }}</i>
          </div>
          <strong>{{ card.value || 0 }}</strong>
          <small>{{ card.caption }}</small>
        </article>
      </div>
    </div>
  </section>
</template>

<script>
import adminService from '@/services/adminService'
import { isAdvisor } from '@/utils/permissions'

export default {
  name: 'AdminDashboard',
  data () {
    return {
      stats: {
        total_properties: 0,
        featured_properties: 0,
        active_properties: 0,
        sold_properties: 0,
        total_users: 0,
        total_testimonials: 0,
        total_leads: 0
      }
    }
  },
  computed: {
    isAdvisor,
    cards () {
      return [
        { label: 'Propiedades', value: this.stats.total_properties, manager: false, icon: '⌂', tone: 'green', caption: 'Catalogo total' },
        { label: 'Destacadas', value: this.stats.featured_properties, manager: false, icon: '★', tone: 'cyan', caption: 'Visibles en landing' },
        { label: 'Activas', value: this.stats.active_properties, manager: false, icon: '✓', tone: 'blue', caption: 'Disponibles para venta' },
        { label: 'Vendidas', value: this.stats.sold_properties, manager: false, icon: '◆', tone: 'gold', caption: 'Cerradas o inactivas' },
        { label: 'Usuarios', value: this.stats.total_users, manager: true, icon: '◎', tone: 'green', caption: 'Equipo registrado' },
        { label: 'Testimonios', value: this.stats.total_testimonials, manager: true, icon: '❝', tone: 'cyan', caption: 'Prueba social' },
        { label: 'Leads', value: this.stats.total_leads, manager: true, icon: '◌', tone: 'blue', caption: 'Prospectos recibidos' }
      ]
    },
    visibleCards () {
      return this.isAdvisor ? this.cards.filter(card => !card.manager) : this.cards
    }
  },
  created () {
    this.load()
  },
  methods: {
    async load () {
      try {
        const response = await adminService.dashboard()
        this.stats = response.data.data || response.data
      } catch (error) {
        console.error('No se pudo cargar el dashboard', error)
      }
    }
  }
}
</script>
