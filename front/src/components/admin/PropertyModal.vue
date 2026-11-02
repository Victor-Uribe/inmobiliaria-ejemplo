<template>
  <div class="admin-modal-backdrop" role="presentation" @click.self="close">
    <section class="admin-modal admin-modal-wide" role="dialog" aria-modal="true"
      aria-labelledby="property-modal-title">
      <header class="admin-modal-header">
        <div>
          <span>Propiedad</span>
          <h2 id="property-modal-title">{{ property ? 'Editar propiedad' : 'Nueva propiedad' }}</h2>
          <p>Los datos de comprador y pagos se capturan en modales separados cuando la propiedad esta vendida.</p>
        </div>
        <button type="button" class="admin-modal-close" aria-label="Cerrar" :disabled="saving" @click="close">&times;</button>
      </header>

      <form class="admin-modal-body" novalidate @submit.prevent="submit">
        <div class="admin-field">
          <input v-model="form.title" class="form-control" :class="{ 'is-invalid': fieldError('title') }" placeholder="Titulo" :disabled="saving" required>
          <FieldError :message="fieldError('title')" />
        </div>
        <div class="admin-field">
          <input v-model="form.price" type="number" class="form-control" :class="{ 'is-invalid': fieldError('price') }" placeholder="Precio publico" :disabled="saving" required>
          <FieldError :message="fieldError('price')" />
        </div>
        <div class="row g-3">
          <div class="col-md-6 admin-field">
            <input v-model="form.state" class="form-control" :class="{ 'is-invalid': fieldError('state') }" placeholder="Estado" :disabled="saving">
            <FieldError :message="fieldError('state')" />
          </div>
          <div class="col-md-6 admin-field">
            <input v-model="form.municipality" class="form-control" :class="{ 'is-invalid': fieldError('municipality') }" placeholder="Municipio" :disabled="saving">
            <FieldError :message="fieldError('municipality')" />
          </div>
        </div>
        <!-- <div class="admin-field">
          <input v-model="form.contact" class="form-control" :class="{ 'is-invalid': fieldError('contact') }" placeholder="Contacto" :disabled="saving">
          <FieldError :message="fieldError('contact')" />
        </div> -->
        <!-- <div v-if="!isAdvisor" class="admin-field">
          <input v-model="form.user_id" type="number" class="form-control" :class="{ 'is-invalid': fieldError('user_id') }" placeholder="ID asesor" :disabled="saving">
          <FieldError :message="fieldError('user_id')" />
        </div> -->
        <div class="admin-field">
          <select v-model="form.status" class="form-select" :class="{ 'is-invalid': fieldError('status') }" :disabled="saving">
            <option value="active">Activa</option>
            <option value="inactive">Inactiva</option>
            <option value="sold">Vendida</option>
          </select>
          <FieldError :message="fieldError('status')" />
        </div>
        <div class="admin-field">
          <input class="form-control" :class="{ 'is-invalid': fieldError('main_image') || fieldError('image') }" type="file" accept="image/*" :disabled="saving" @change="changeMainImage">
          <FieldError :message="fieldError('main_image') || fieldError('image')" />
        </div>
        <img v-if="preview" :src="preview" class="admin-preview" alt="Preview">
        <div class="admin-field">
          <textarea v-model="form.description" class="form-control" :class="{ 'is-invalid': fieldError('description') }" rows="4" placeholder="Descripcion" :disabled="saving"></textarea>
          <FieldError :message="fieldError('description')" />
        </div>
        <label class="admin-check">
          <input v-model="form.is_featured" type="checkbox" :disabled="saving">
          Destacada
        </label>

        <footer class="admin-modal-footer">
          <button type="button" class="btn btn-outline-premium" :disabled="saving" @click="close">
            <i class="fa-solid fa-arrow-rotate-left"></i>
            Cancelar
          </button>
          <button class="btn btn-premium" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
            <i v-else class="fa-solid fa-floppy-disk"></i>
            {{ saving ? 'Guardando...' : 'Guardar propiedad' }}
          </button>
        </footer>
      </form>
    </section>
  </div>
</template>

<script>
import { slugify } from '@/utils/formatters'
import FieldError from '@/components/admin/FieldError.vue'

const empty = () => ({
  title: '',
  price: '',
  state: 'Estado de Mexico',
  municipality: 'Ixtapaluca',
  contact: '',
  user_id: 1,
  status: 'active',
  description: '',
  is_featured: false
})

export default {
  name: 'PropertyModal',
  components: { FieldError },
  props: {
    property: {
      type: Object,
      default: null
    },
    isAdvisor: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object,
      default: () => ({})
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
  data() {
    return {
      form: empty(),
      file: null,
      preview: ''
    }
  },
  created() {
    this.fill()
  },
  methods: {
    fill() {
      if (!this.property) return
      this.form = {
        ...empty(),
        ...this.property.raw,
        title: this.property.title,
        price: this.property.rawPrice,
        state: this.property.state,
        municipality: this.property.municipality,
        status: this.property.status,
        description: this.property.description,
        user_id: this.property.user_id,
        is_featured: this.property.featured ? 0 : 1
      }
      this.preview = this.property.image
    },
    changeMainImage(event) {
      if (this.saving) return
      this.file = event.target.files[0] || null
      this.preview = this.file ? URL.createObjectURL(this.file) : this.preview
    },
    payload() {
      const payload = { ...this.form }
      if (this.isAdvisor) payload.user_id = this.user.id
      if (!payload.slug) payload.slug = slugify(payload.title)
      if (!this.file) return payload

      const formData = new FormData()
      Object.entries(payload).forEach(([key, value]) => formData.append(key, value))
      formData.append('main_image', this.file)
      return formData
    },
    submit() {
      if (this.saving) return
      this.$emit('save', this.payload())
    },
    fieldError(field) {
      return this.errors[field] || ''
    },
    close() {
      if (!this.saving) this.$emit('close')
    }
  }
}
</script>
