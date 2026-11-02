<template>
  <section>
    <div class="admin-page-title">
      <span>Equipo</span>
      <h1>Usuarios</h1>
      <p>Alta de administradores y asesores.</p>
    </div>

    <div class="admin-toolbar">
      <button class="btn btn-premium" @click="openModal()">Nuevo usuario</button>
      <div v-if="errorMessage" class="alert alert-danger mb-0">{{ errorMessage }}</div>
    </div>

    <div class="admin-card table-responsive bt-b">
      <table class="table align-middle">
        <tbody>
          <tr v-for="user in items" :key="user.id">
            <td><strong>{{ user.name }}</strong><small>{{ user.email }} · {{ user.phone || 'Sin telefono' }}</small></td>
            <td><span class="admin-status">{{ roleName(user) }}</span></td>
            <td class="text-end admin-table-actions">
              <button class="btn btn-sm btn-outline-premium" @click="openModal(user)">Editar</button>
              <button class="btn btn-sm btn-danger" :disabled="saving" @click="remove(user)">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
      </div>

    <UserModal
      v-if="showModal"
      :user="editing"
      :saving="saving"
      :errors="formErrors"
      @close="closeModal"
      @save="save"
    />
  </section>
</template>

<script>
import adminService from '@/services/adminService'
import UserModal from '@/components/admin/UserModal.vue'
import { confirmDelete, errorAlert, successAlert } from '@/utils/alerts'
import { errorMessage, validationErrors } from '@/utils/apiErrors'

export default {
  name: 'AdminUsers',
  components: { UserModal },
  data: () => ({ items: [], editing: null, showModal: false, errorMessage: '', formErrors: {}, saving: false }),
  created () { this.load() },
  methods: {
    roleName (user) {
      if (user.role && user.role.name) return user.role.name
      return { 1: 'Super Admin', 2: 'Admin', 3: 'Asesor' }[Number(user.role_id)] || 'Sin rol'
    },
    async load () {
      try { const response = await adminService.users(); this.items = response.data.data || response.data } catch (error) { this.items = [] }
    },
    openModal (user = null) {
      this.editing = user
      this.formErrors = {}
      this.showModal = true
    },
    closeModal () {
      if (this.saving) return
      this.editing = null
      this.showModal = false
      this.formErrors = {}
    },
    async save (payload) {
      if (this.saving) return
      this.errorMessage = ''
      this.formErrors = {}
      this.saving = true
      try {
        await adminService.saveUser(payload, this.editing ? this.editing.id : null)
        await this.load()
        await successAlert('Usuario guardado', 'El usuario se guardo correctamente.')
      } catch (error) {
        console.error('No se pudo guardar el usuario', error)
        this.formErrors = validationErrors(error)
        this.errorMessage = errorMessage(error, 'No se pudo guardar el usuario. Revisa la API.')
        await errorAlert('No se pudo guardar', this.errorMessage)
        return
      } finally {
        this.saving = false
      }
      this.closeModal()
    },
    async remove (user) {
      if (this.saving) return
      const confirmed = await confirmDelete({
        title: '¿Eliminar usuario?',
        text: `Se eliminara "${user.name}".`
      })
      if (!confirmed) return
      this.errorMessage = ''
      this.saving = true
      try {
        await adminService.deleteUser(user.id)
        await this.load()
        await successAlert('Usuario eliminado', 'El usuario se elimino correctamente.')
      } catch (error) {
        console.error('No se pudo eliminar el usuario', error)
        this.errorMessage = errorMessage(error, 'No se pudo eliminar el usuario. Revisa la API.')
        await errorAlert('No se pudo eliminar', this.errorMessage)
      } finally {
        this.saving = false
      }
    }
  }
}
</script>
