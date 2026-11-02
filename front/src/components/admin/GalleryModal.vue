<template>
  <div class="admin-modal-backdrop" role="presentation" @click.self="close">
    <section class="admin-modal admin-modal-wide" role="dialog" aria-modal="true" aria-labelledby="gallery-modal-title">
      <header class="admin-modal-header">
        <div>
          <span>Galeria</span>
          <h2 id="gallery-modal-title">{{ property.title }}</h2>
          <p>Sube imagenes locales, marca una como principal o elimina imagenes existentes.</p>
        </div>
        <button type="button" class="admin-modal-close" aria-label="Cerrar" :disabled="saving" @click="close">&times;</button>
      </header>

      <div class="admin-modal-body">
        <form class="payment-modal-form" novalidate @submit.prevent="upload">
          <div class="admin-field gallery-file-field">
            <input class="form-control" :class="{ 'is-invalid': fieldError('image') || fieldError('images') }" type="file" accept="image/*" multiple :disabled="saving" @change="changeFiles">
            <FieldError :message="fieldError('image') || fieldError('images')" />
          </div>
          <button class="btn btn-premium" :disabled="saving || !files.length">
            <span v-if="saving" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
            {{ saving ? 'Subiendo...' : 'Subir imagenes' }}
          </button>
        </form>

        <div class="admin-image-grid">
          <article v-for="image in property.images" :key="image.id" class="admin-image-tile">
            <img :src="image.url" alt="Imagen propiedad">
            <div>
              <button class="btn btn-sm btn-light" :disabled="saving" @click="$emit('set-main', image)">Principal</button>
              <button class="btn btn-sm btn-danger" :disabled="saving" @click="$emit('delete-image', image)">Eliminar</button>
            </div>
          </article>
        </div>
      </div>

      <footer class="admin-modal-footer">
        <button type="button" class="btn btn-outline-premium" :disabled="saving" @click="close">Cerrar</button>
      </footer>
    </section>
  </div>
</template>

<script>
import FieldError from '@/components/admin/FieldError.vue'

export default {
  name: 'GalleryModal',
  components: { FieldError },
  props: {
    property: {
      type: Object,
      required: true
    },
    saving: {
      type: Boolean,
      default: false
    },
    errors: {
      type: Object,
      default: () => ({})
    }
  },
  data: () => ({ files: [] }),
  methods: {
    changeFiles (event) {
      if (this.saving) return
      this.files = Array.from(event.target.files || [])
    },
    upload () {
      if (this.saving || !this.files.length) return
      this.$emit('upload', this.files)
      this.files = []
    },
    fieldError (field) {
      return this.errors[field] || ''
    },
    close () {
      if (!this.saving) this.$emit('close')
    }
  }
}
</script>
