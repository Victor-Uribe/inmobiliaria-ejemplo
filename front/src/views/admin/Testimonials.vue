<template>
  <section>
    <div class="admin-page-title">
      <span>Contenido</span>
      <h1>Testimonios</h1>
      <p>Administrables desde API y visibles en landing.</p>
    </div>

    <div class="admin-toolbar">
      <button class="btn btn-premium" @click="openModal()">Nuevo testimonio</button>
      <div v-if="errorMessage" class="alert alert-danger mb-0">{{ errorMessage }}</div>
    </div>

    <article v-for="item in items" :key="item.id" class="admin-card admin-row-card bt-b">
      <div>
        <strong>{{ item.client_name || item.name }}</strong>
        <p>{{ item.comment || item.quote }}</p>
      </div>
      <div class="text-end">
        <button class="btn btn-sm btn-outline-premium" @click="openModal(item)">Editar</button>
        <button class="btn btn-sm btn-danger ms-2" :disabled="saving" @click="remove(item)">Eliminar</button>
      </div>
    </article>

    <TestimonialModal
      v-if="showModal"
      :testimonial="editing"
      :saving="saving"
      :errors="formErrors"
      @close="closeModal"
      @save="save"
    />
  </section>
</template>

<script>
import adminService from '@/services/adminService'
import TestimonialModal from '@/components/admin/TestimonialModal.vue'
import { confirmDelete, errorAlert, successAlert } from '@/utils/alerts'
import { errorMessage, validationErrors } from '@/utils/apiErrors'

export default {
  name: 'AdminTestimonials',
  components: { TestimonialModal },
  data: () => ({ items: [], editing: null, showModal: false, errorMessage: '', formErrors: {}, saving: false }),
  created () { this.load() },
  methods: {
    async load () {
      try { const response = await adminService.testimonials(); this.items = response.data.data || response.data } catch (error) { this.items = [] }
    },
    openModal (item = null) {
      this.editing = item
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
        await adminService.saveTestimonial(payload, this.editing ? this.editing.id : null)
        await this.load()
        await successAlert('Testimonio guardado', 'El testimonio se guardo correctamente.')
      } catch (error) {
        console.error('No se pudo guardar el testimonio', error)
        this.formErrors = validationErrors(error)
        this.errorMessage = errorMessage(error, 'No se pudo guardar el testimonio. Revisa la API.')
        await errorAlert('No se pudo guardar', this.errorMessage)
        return
      } finally {
        this.saving = false
      }
      this.closeModal()
    },
    async remove (item) {
      if (this.saving) return
      const confirmed = await confirmDelete({
        title: '¿Eliminar testimonio?',
        text: `Se eliminara el testimonio de "${item.client_name || item.name}".`
      })
      if (!confirmed) return
      this.errorMessage = ''
      this.saving = true
      try {
        await adminService.deleteTestimonial(item.id)
        await this.load()
        await successAlert('Testimonio eliminado', 'El testimonio se elimino correctamente.')
      } catch (error) {
        console.error('No se pudo eliminar el testimonio', error)
        this.errorMessage = errorMessage(error, 'No se pudo eliminar el testimonio. Revisa la API.')
        await errorAlert('No se pudo eliminar', this.errorMessage)
      } finally {
        this.saving = false
      }
    }
  }
}
</script>
