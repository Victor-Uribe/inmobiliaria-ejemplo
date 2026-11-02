<template>
  <div class="admin-modal-backdrop" role="presentation" @click.self="close">
    <section class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
      <header class="admin-modal-header">
        <div>
          <span>Usuario</span>
          <h2 id="user-modal-title">{{ user ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
          <p>Alta de administradores y asesores.</p>
        </div>
        <button type="button" class="admin-modal-close" aria-label="Cerrar" :disabled="saving" @click="close">&times;</button>
      </header>

      <form class="admin-modal-body" novalidate @submit.prevent="submit">
        <div class="admin-field">
          <input v-model="form.name" class="form-control" :class="{ 'is-invalid': fieldError('name') }" placeholder="Nombre" :disabled="saving" required>
          <FieldError :message="fieldError('name')" />
        </div>
        <div class="admin-field">
          <input v-model="form.email" type="email" class="form-control" :class="{ 'is-invalid': fieldError('email') }" placeholder="Correo" :disabled="saving" required>
          <FieldError :message="fieldError('email')" />
        </div>
        <div class="admin-field">
          <input v-model="form.phone" class="form-control" :class="{ 'is-invalid': fieldError('phone') }" placeholder="Telefono" :disabled="saving">
          <FieldError :message="fieldError('phone')" />
        </div>
        <div class="admin-field">
          <select v-model="form.role_id" class="form-select" :class="{ 'is-invalid': fieldError('role_id') }" :disabled="saving">
            <option :value="1">Super Admin</option>
            <option :value="2">Admin</option>
            <option :value="3">Asesor</option>
          </select>
          <FieldError :message="fieldError('role_id')" />
        </div>
        <div v-if="!user" class="admin-field">
          <input v-model="form.password" type="password" class="form-control" :class="{ 'is-invalid': fieldError('password') }" placeholder="Password inicial" :disabled="saving" required>
          <FieldError :message="fieldError('password')" />
        </div>
        <label class="admin-check"><input v-model="form.status" type="checkbox" :disabled="saving"> Activo</label>

        <footer class="admin-modal-footer">
          <button type="button" class="btn btn-outline-premium" :disabled="saving" @click="close">Cancelar</button>
          <button class="btn btn-premium" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
            {{ saving ? 'Guardando...' : 'Guardar usuario' }}
          </button>
        </footer>
      </form>
    </section>
  </div>
</template>

<script>
import FieldError from '@/components/admin/FieldError.vue'

const empty = () => ({ name: '', email: '', phone: '', role_id: 3, password: '', status: true })

export default {
  name: 'UserModal',
  components: { FieldError },
  props: {
    user: {
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
      form: empty()
    }
  },
  created () {
    if (!this.user) return
    this.form = { ...empty(), ...this.user, role_id: this.user.role_id || (this.user.role && this.user.role.id) || 3, password: '' }
  },
  methods: {
    submit () {
      if (this.saving) return
      const payload = { ...this.form }
      if (this.user && !payload.password) delete payload.password
      this.$emit('save', payload)
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
