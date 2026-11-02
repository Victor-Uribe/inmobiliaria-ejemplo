<template>
  <div class="admin-modal-backdrop" role="presentation" @click.self="close">
    <section class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="testimonial-modal-title">
      <header class="admin-modal-header">
        <div>
          <span>Testimonio</span>
          <h2 id="testimonial-modal-title">{{ testimonial ? 'Editar testimonio' : 'Nuevo testimonio' }}</h2>
          <p>Contenido administrable visible en landing.</p>
        </div>
        <button type="button" class="admin-modal-close" aria-label="Cerrar" :disabled="saving" @click="close">&times;</button>
      </header>

      <form class="admin-modal-body" novalidate @submit.prevent="submit">
        <div class="admin-field">
          <input v-model="form.client_name" class="form-control" :class="{ 'is-invalid': fieldError('client_name') || fieldError('name') }" placeholder="Nombre del cliente" :disabled="saving" required>
          <FieldError :message="fieldError('client_name') || fieldError('name')" />
        </div>
        <div class="admin-field">
          <textarea v-model="form.comment" class="form-control" :class="{ 'is-invalid': fieldError('comment') }" rows="4" placeholder="Comentario" :disabled="saving" required></textarea>
          <FieldError :message="fieldError('comment')" />
        </div>
        <div class="admin-field">
          <input class="form-control" :class="{ 'is-invalid': fieldError('photo') || fieldError('image') }" type="file" accept="image/*" :disabled="saving" @change="changeFile">
          <FieldError :message="fieldError('photo') || fieldError('image')" />
        </div>
        <img v-if="preview" :src="preview" class="admin-preview" alt="Foto">
        <label class="admin-check"><input v-model="form.status" type="checkbox" :disabled="saving"> Activo</label>

        <footer class="admin-modal-footer">
          <button type="button" class="btn btn-outline-premium" :disabled="saving" @click="close">Cancelar</button>
          <button class="btn btn-premium" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
            {{ saving ? 'Guardando...' : 'Guardar testimonio' }}
          </button>
        </footer>
      </form>
    </section>
  </div>
</template>

<script>
import FieldError from '@/components/admin/FieldError.vue'

const empty = () => ({ client_name: '', comment: '', status: true })

export default {
  name: 'TestimonialModal',
  components: { FieldError },
  props: {
    testimonial: {
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
    if (!this.testimonial) return
    this.form = {
      ...empty(),
      ...this.testimonial,
      client_name: this.testimonial.client_name || this.testimonial.name,
      comment: this.testimonial.comment || this.testimonial.quote
    }
    this.preview = this.testimonial.photo_url || this.testimonial.photo || ''
  },
  methods: {
    changeFile (event) {
      if (this.saving) return
      this.file = event.target.files[0] || null
      this.preview = this.file ? URL.createObjectURL(this.file) : this.preview
    },
    submit () {
      if (this.saving) return
      if (!this.file) return this.$emit('save', { ...this.form })
      const formData = new FormData()
      Object.entries(this.form).forEach(([key, value]) => formData.append(key, value))
      formData.append('photo', this.file)
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
