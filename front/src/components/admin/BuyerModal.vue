<template>
  <div class="admin-modal-backdrop" role="presentation" @click.self="close">
    <section class="admin-modal admin-modal-wide" role="dialog" aria-modal="true" aria-labelledby="buyer-modal-title">
      <header class="admin-modal-header">
        <div>
          <span>Comprador</span>
          <h2 id="buyer-modal-title">{{ property.title }}</h2>
          <p>Registra la venta, el enganche, descuento y mensualidad fija del comprador.</p>
        </div>
        <button type="button" class="admin-modal-close" aria-label="Cerrar" :disabled="saving" @click="close">&times;</button>
      </header>

      <form class="admin-modal-body" novalidate @submit.prevent="submit">
        <div class="admin-form-divider">
          <span>Datos del comprador</span>
        </div>

        <div class="row g-3">
          <!-- <div class="col-md-4 admin-field">
            <label class="form-label">Numero de comprador</label>
            <input v-model="form.buyer_number" class="form-control" :class="{ 'is-invalid': fieldError('buyer_number') }" placeholder="Ej. AND-001" :disabled="saving">
            <FieldError :message="fieldError('buyer_number')" />
          </div> -->
          <div class="col-md-12 admin-field">
            <label class="form-label">Nombre del comprador *</label>
            <input v-model="form.buyer_name" class="form-control" :class="{ 'is-invalid': fieldError('buyer_name') || fieldError('name') }" placeholder="Nombre completo" :disabled="saving" required>
            <FieldError :message="fieldError('buyer_name') || fieldError('name')" />
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-6 admin-field">
            <label class="form-label">WhatsApp / Telefono</label>
            <input v-model="form.buyer_phone" class="form-control" :class="{ 'is-invalid': fieldError('buyer_phone') || fieldError('phone') }" placeholder="5512345678" :disabled="saving">
            <FieldError :message="fieldError('buyer_phone') || fieldError('phone')" />
          </div>
          <div class="col-md-6 admin-field">
            <label class="form-label">Correo</label>
            <input v-model="form.buyer_email" type="email" class="form-control" :class="{ 'is-invalid': fieldError('buyer_email') || fieldError('email') }" placeholder="cliente@correo.com" :disabled="saving">
            <FieldError :message="fieldError('buyer_email') || fieldError('email')" />
          </div>
        </div>

        <div class="admin-form-divider">
          <span>Condiciones de venta</span>
        </div>

        <div class="row g-3">
          <div class="col-md-4 admin-field">
            <label class="form-label">Precio de venta *</label>
            <input v-model="form.sale_price" type="number" min="0" step="0.01" class="form-control" :class="{ 'is-invalid': fieldError('sale_price') }" placeholder="300000" :disabled="saving" required>
            <FieldError :message="fieldError('sale_price')" />
          </div>
          <div class="col-md-4 admin-field">
            <label class="form-label">Descuento</label>
            <input v-model="form.discount_amount" type="number" min="0" step="0.01" class="form-control" :class="{ 'is-invalid': fieldError('discount_amount') }" placeholder="0" :disabled="saving">
            <FieldError :message="fieldError('discount_amount')" />
          </div>
          <div class="col-md-4 admin-field">
            <label class="form-label">Enganche pactado</label>
            <input v-model="form.down_payment_amount" type="number" min="0" step="0.01" class="form-control" :class="{ 'is-invalid': fieldError('down_payment_amount') || fieldError('down_payment') }" placeholder="0" :disabled="saving">
            <FieldError :message="fieldError('down_payment_amount') || fieldError('down_payment')" />
          </div>
        </div>

        <div class="buyer-finance-preview">
          <span>Monto financiado</span>
          <strong>{{ money(financedAmount) }}</strong>
          <small v-if="estimatedMonths">Aproximadamente {{ estimatedMonths }} mensualidades.</small>
          <small v-else>Captura la mensualidad para calcular los meses aproximados.</small>
        </div>

        <div class="admin-form-divider">
          <span>Calendario de pagos</span>
        </div>

        <div class="row g-3">
          <div class="col-md-4 admin-field">
            <label class="form-label">Mensualidad fija *</label>
            <input v-model="form.monthly_payment_amount" type="number" min="1" step="0.01" class="form-control" :class="{ 'is-invalid': fieldError('monthly_payment_amount') }" placeholder="3000" :disabled="saving" required>
            <FieldError :message="fieldError('monthly_payment_amount')" />
          </div>
          <div class="col-md-4 admin-field">
            <label class="form-label">Dia limite de pago *</label>
            <input v-model="form.payment_due_day" type="number" min="1" max="31" class="form-control" :class="{ 'is-invalid': fieldError('payment_due_day') }" placeholder="5" :disabled="saving" required>
            <FieldError :message="fieldError('payment_due_day')" />
          </div>
          <div class="col-md-4 admin-field">
            <label class="form-label">Primer mes a pagar *</label>
            <input v-model="form.first_payment_month" type="month" class="form-control" :class="{ 'is-invalid': fieldError('first_payment_period') || fieldError('first_payment_date') }" :disabled="saving" required>
            <FieldError :message="fieldError('first_payment_period') || fieldError('first_payment_date')" />
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-4 admin-field">
            <label class="form-label">Fecha de venta</label>
            <input v-model="form.sale_date" type="date" class="form-control" :class="{ 'is-invalid': fieldError('sale_date') }" :disabled="saving">
            <FieldError :message="fieldError('sale_date')" />
          </div>
          <div class="col-md-8 admin-field">
            <label class="form-label">Notas</label>
            <textarea v-model="form.notes" rows="2" class="form-control" :class="{ 'is-invalid': fieldError('notes') }" placeholder="Observaciones del acuerdo, descuento o enganche" :disabled="saving"></textarea>
            <FieldError :message="fieldError('notes')" />
          </div>
        </div>

        <footer class="admin-modal-footer">
          <button type="button" class="btn btn-outline-premium" :disabled="saving" @click="close">Cancelar</button>
          <button class="btn btn-premium" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
            {{ saving ? 'Guardando...' : 'Guardar comprador' }}
          </button>
        </footer>
      </form>
    </section>
  </div>
</template>

<script>
import FieldError from '@/components/admin/FieldError.vue'
import { money } from '@/utils/formatters'

const todayMonth = () => {
  const today = new Date()
  return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`
}

const toMonth = (value) => {
  if (!value) return ''
  return String(value).slice(0, 7)
}

const empty = () => ({
  buyer_number: '',
  buyer_name: '',
  buyer_phone: '',
  buyer_email: '',
  sale_price: '',
  discount_amount: 0,
  down_payment_amount: 0,
  monthly_payment_amount: '',
  payment_due_day: 5,
  sale_date: '',
  first_payment_month: todayMonth(),
  notes: ''
})

export default {
  name: 'BuyerModal',
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
  data () {
    return {
      form: empty()
    }
  },
  computed: {
    financedAmount () {
      return Math.max(
        0,
        Number(this.form.sale_price || 0) -
        Number(this.form.discount_amount || 0) -
        Number(this.form.down_payment_amount || 0)
      )
    },
    estimatedMonths () {
      const monthly = Number(this.form.monthly_payment_amount || 0)
      if (!monthly || !this.financedAmount) return 0
      return Math.ceil(this.financedAmount / monthly)
    }
  },
  created () {
    this.fill()
  },
  methods: {
    money,
    fill () {
      console.log(this.property);
      this.form = {
        buyer_number: this.property.buyer_number || '',
        buyer_name: this.property.buyer_name || '',
        buyer_phone: this.property.buyer_phone || '',
        buyer_email: this.property.buyer_email || '',
        sale_price: this.property.sale_price || this.property.rawPrice || '',
        discount_amount: this.property.discount_amount || 0,
        down_payment_amount: this.property.down_payment_amount || 0,
        monthly_payment_amount: this.property.monthly_payment_amount || '',
        payment_due_day: this.property.payment_due_day || 5,
        sale_date: this.property.sale_date || '',
        first_payment_month: toMonth(this.property.first_payment_period) || todayMonth(),
        notes: this.property.buyer_notes || this.property.notes || ''
      }
    },
    submit () {
      if (this.saving) return
      this.$emit('save', {
        buyer_number: this.form.buyer_number,
        buyer_name: this.form.buyer_name,
        buyer_phone: this.form.buyer_phone,
        buyer_email: this.form.buyer_email,
        sale_price: Number(this.form.sale_price || 0),
        discount_amount: Number(this.form.discount_amount || 0),
        down_payment_amount: Number(this.form.down_payment_amount || 0),
        monthly_payment_amount: Number(this.form.monthly_payment_amount || 0),
        payment_due_day: Number(this.form.payment_due_day || 5),
        sale_date: this.form.sale_date || null,
        first_payment_period: this.form.first_payment_month ? `${this.form.first_payment_month}-01` : null,
        notes: this.form.notes
      })
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
