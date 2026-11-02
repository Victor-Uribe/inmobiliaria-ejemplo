<template>
  <section>
    <div class="admin-page-title">
      <span>Hero</span>
      <h1>Banners</h1>
      <p>Imagenes locales para el carrusel principal.</p>
    </div>

    <div class="admin-toolbar">
      <button class="btn btn-premium" @click="openModal()">Nuevo banner</button>
      <div v-if="errorMessage" class="alert alert-danger mb-0">{{ errorMessage }}</div>
    </div>

    <div>
      <article v-for="item in items" :key="item.id" class="admin-card admin-row-card bt-b">
        <img class="admin-thumb large" :src="image(item)" alt="Banner">
        <div>
          <strong>{{ item.title }}</strong>
          <p>{{ item.subtitle }}</p>
        </div>
        <div class="admin-row-actions">
          <button class="btn btn-sm btn-outline-premium" @click="openModal(item)">Editar</button>
          <button class="btn btn-sm btn-danger" :disabled="saving" @click="remove(item)">Eliminar</button>
        </div>
      </article>
    </div>

    <BannerModal
      v-if="showModal"
      :banner="editing"
      :saving="saving"
      :errors="formErrors"
      @close="closeModal"
      @save="save"
    />
  </section>
</template>

<script>
import adminService from '@/services/adminService'
import BannerModal from '@/components/admin/BannerModal.vue'
import { assetUrl } from '@/utils/media'
import { confirmDelete, errorAlert, successAlert } from '@/utils/alerts'
import { errorMessage, validationErrors } from '@/utils/apiErrors'

export default {
  name: 'AdminBanners',
  components: { BannerModal },
  data: () => ({ items: [], editing: null, showModal: false, errorMessage: '', formErrors: {}, saving: false }),
  created () { this.load() },
  methods: {
    image (item) { return assetUrl(item.image_url || item.image, '') },
    async load () {
      try { const response = await adminService.banners(); this.items = response.data.data || response.data } catch (error) { this.items = [] }
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
        await adminService.saveBanner(payload, this.editing ? this.editing.id : null)
        await this.load()
        await successAlert('Banner guardado', 'El banner se guardo correctamente.')
      } catch (error) {
        console.error('No se pudo guardar el banner', error)
        this.formErrors = validationErrors(error)
        this.errorMessage = errorMessage(error, 'No se pudo guardar el banner. Revisa la API.')
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
        title: '¿Eliminar banner?',
        text: `Se eliminara "${item.title}".`
      })
      if (!confirmed) return
      this.errorMessage = ''
      this.saving = true
      try {
        await adminService.deleteBanner(item.id)
        await this.load()
        await successAlert('Banner eliminado', 'El banner se elimino correctamente.')
      } catch (error) {
        console.error('No se pudo eliminar el banner', error)
        this.errorMessage = errorMessage(error, 'No se pudo eliminar el banner. Revisa la API.')
        await errorAlert('No se pudo eliminar', this.errorMessage)
      } finally {
        this.saving = false
      }
    }
  }
}
</script>
