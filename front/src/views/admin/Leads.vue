<template>
  <section>
    <div class="admin-page-title">
      <span>Prospectos</span>
      <h1>Leads</h1>
      <p>Solicitudes recibidas desde landing y fichas de propiedad.</p>
    </div>

    <div class="admin-card table-responsive bt-b">
      <table class="table align-middle">
        <tbody>
          <tr v-for="lead in items" :key="lead.id">
            <td>
              <strong>{{ lead.name }}</strong>
              <small>{{ lead.phone || 'Sin telefono' }} · {{ lead.email || 'Sin correo' }}</small>
            </td>
            <td>{{ lead.message }}</td>
            <td><span class="admin-status">{{ lead.source || 'landing' }}</span></td>
            <td>
              <select v-model="lead.status" class="form-select" @change="update(lead)">
                <option value="new">Nuevo</option>
                <option value="contacted">Contactado</option>
                <option value="closed">Cerrado</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="!items.length" class="empty-state compact">Sin leads registrados.</div>
    </div>
  </section>
</template>

<script>
import adminService from '@/services/adminService'

export default {
  name: 'AdminLeads',
  data: () => ({ items: [] }),
  created () {
    this.load()
  },
  methods: {
    async load () {
      try {
        const response = await adminService.leads()
        this.items = response.data.data || response.data
      } catch (error) {
        this.items = []
      }
    },
    async update (lead) {
      try {
        await adminService.updateLead(lead.id, { status: lead.status })
      } catch (error) {
        console.error('No se pudo actualizar el lead', error)
      }
    }
  }
}
</script>
