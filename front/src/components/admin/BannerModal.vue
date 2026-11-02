<template>
  <div class="admin-modal-backdrop" role="presentation" @click.self="close">
    <section class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="banner-modal-title">
      <header class="admin-modal-header">
        <div>
          <span>Banner</span>
          <h2 id="banner-modal-title">{{ banner ? 'Editar banner' : 'Nuevo banner' }}</h2>
          <p>La imagen se sube como archivo local, no por URL.</p>
        </div>
        <button type="button" class="admin-modal-close" aria-label="Cerrar" :disabled="saving" @click="close">&times;</button>
      </header>

      <form class="admin-modal-body" novalidate @submit.prevent="submit">
        <div class="admin-field">
          <input v-model="form.title" class="form-control" :class="{ 'is-invalid': fieldError('title') }" placeholder="Titulo" :disabled="saving" required>
          <FieldError :message="fieldError('title')" />
        </div>
        <div class="admin-field">
          <textarea v-model="form.subtitle" class="form-control" :class="{ 'is-invalid': fieldError('subtitle') }" rows="3" placeholder="Subtitulo" :disabled="saving"></textarea>
          <FieldError :message="fieldError('subtitle')" />
        </div>
        <div class="admin-field">
          <input v-model="form.button_text" class="form-control" :class="{ 'is-invalid': fieldError('button_text') }" placeholder="Texto del botón" :disabled="saving">
          <FieldError :message="fieldError('button_text')" />
        </div>
        <div class="admin-field">
          <input v-model="form.button_url" class="form-control" :class="{ 'is-invalid': fieldError('button_url') }" placeholder="URL del botón" :disabled="saving">
          <FieldError :message="fieldError('button_url')" />
        </div>

        <div class="admin-field">
          <input class="form-control" :class="{ 'is-invalid': fieldError('image') }" type="file" accept="image/*" :required="!banner" :disabled="saving" @change="changeFile">
          <FieldError :message="fieldError('image')" />
        </div>
        <img v-if="preview" :src="preview" class="admin-preview" alt="Banner">
        <label class="admin-check"><input v-model="form.status" type="checkbox" :disabled="saving"> Activo</label>

        <footer class="admin-modal-footer">
          <button type="button" class="btn btn-outline-premium" :disabled="saving" @click="close">Cancelar</button>
          <button class="btn btn-premium" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
            {{ saving ? 'Guardando...' : 'Guardar banner' }}
          </button>
        </footer>
      </form>
    </section>
  </div>
</template>

<script>
import { assetUrl } from '@/utils/media'
import FieldError from '@/components/admin/FieldError.vue'

const empty = () => ({ title: '', subtitle: '', status: true })

export default {
  name: 'BannerModal',
  components: { FieldError },
  props: {
    banner: {
      type: Object,
      default: null
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
  data () {
    return {
      form: empty(),
      file: null,
      preview: ''
    }
  },
  created () {
    if (!this.banner) return
    this.form = { ...empty(), ...this.banner }
    this.preview = assetUrl(this.banner.image_url || this.banner.image, '')
  },
  methods: {
    changeFile (event) {
      if (this.saving) return
      this.file = event.target.files[0] || null
      this.preview = this.file ? URL.createObjectURL(this.file) : this.preview
    },
    submit () {
      if (this.saving) return
      const formData = new FormData()

      formData.append('title', this.form.title || '')
      formData.append('subtitle', this.form.subtitle || '')
      formData.append('button_text', this.form.button_text || '')
      formData.append('button_url', this.form.button_url || '')
      formData.append('status', this.form.status ? '1' : '0')

      if (this.file) {
        formData.append('image', this.file)
      }

      this.$emit('save', formData)
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
