<template>
  <div class="admin-modal-backdrop" role="presentation" @click.self="close">
    <section class="admin-modal admin-modal-wide" role="dialog" aria-modal="true" aria-labelledby="payment-modal-title">
      <header class="admin-modal-header">
        <div>
          <span>Pagos</span>
          <h2 id="payment-modal-title">{{ property.title }}</h2>
          <p>{{ property.buyer_name || 'Comprador sin registrar' }} · {{ property.payment_status.label }}</p>
        </div>
        <button type="button" class="admin-modal-close" aria-label="Cerrar" :disabled="busy" @click="close">&times;</button>
      </header>

      <div class="admin-modal-body">
        <div v-if="loading" class="alert alert-info mb-3">Cargando pagos y periodos...</div>

        <div class="payment-summary-grid">
          <div class="payment-summary-card highlight">
            <span :class="['payment-indicator', `is-${property.payment_status.color}`]">
              {{ property.payment_status.label }}
            </span>
            <small>Estado del comprador</small>
          </div>
          <div class="payment-summary-card">
            <span>Mensualidad</span>
            <strong>{{ monthlyAmount }}</strong>
          </div>
          <div class="payment-summary-card">
            <span>Saldo financiado</span>
            <strong>{{ money(summary.financed_balance || summary.balance || property.financed_amount || 0) }}</strong>
          </div>
          <div class="payment-summary-card">
            <span>Total pagado</span>
            <strong>{{ money(summary.total_paid || 0) }}</strong>
          </div>
        </div>

        <form class="payment-modal-form" novalidate @submit.prevent="submit">
          <!-- <div class="admin-field">
            <label class="form-label">Tipo de pago</label>
            <select v-model="form.payment_type" class="form-select" :class="{ 'is-invalid': fieldError('payment_type') }" :disabled="busy" required>
              <option value="monthly">Mensualidad</option>
              <option value="down_payment">Enganche</option>
              <option value="adjustment">Ajuste</option>
            </select>
            <FieldError :message="fieldError('payment_type')" />
          </div> -->
          <div class="admin-field">
            <label class="form-label">Fecha real de pago *</label>
            <input v-model="form.paid_at" type="date" class="form-control" :class="{ 'is-invalid': fieldError('paid_at') }" :disabled="busy" required>
            <FieldError :message="fieldError('paid_at')" />
          </div>
          <div class="admin-field">
            <label class="form-label">Mes al que se aplica</label>
            <input v-model="form.applied_month" type="month" class="form-control" :class="{ 'is-invalid': fieldError('applied_period') || fieldError('month') }" :disabled="busy || form.payment_type !== 'monthly'">
            <FieldError :message="fieldError('applied_period') || fieldError('month')" />
          </div>
          <div class="admin-field">
            <label class="form-label">Monto pagado *</label>
            <input v-model="form.amount" type="number" min="1" step="0.01" class="form-control" :class="{ 'is-invalid': fieldError('amount') }" placeholder="Monto pagado" :disabled="busy" required>
            <FieldError :message="fieldError('amount')" />
          </div>
          <div class="admin-field">
            <label class="form-label">Metodo</label>
            <input v-model="form.payment_method" class="form-control" :class="{ 'is-invalid': fieldError('payment_method') }" placeholder="Efectivo, transferencia..." :disabled="busy">
            <FieldError :message="fieldError('payment_method')" />
          </div>
          <div class="admin-field">
            <label class="form-label">Referencia</label>
            <input v-model="form.reference" class="form-control" :class="{ 'is-invalid': fieldError('reference') }" placeholder="Referencia bancaria" :disabled="busy">
            <FieldError :message="fieldError('reference')" />
          </div>
          <div class="admin-field">
            <label class="form-label">Folio</label>
            <input v-model="form.folio" class="form-control" :class="{ 'is-invalid': fieldError('folio') }" placeholder="Folio interno" :disabled="busy">
            <FieldError :message="fieldError('folio')" />
          </div>
        

        <div class="admin-field col_full">
          <label class="form-label">Notas del pago</label>
          <textarea v-model="form.notes" rows="2" class="form-control" :class="{ 'is-invalid': fieldError('notes') }" placeholder="Observaciones del pago" :disabled="busy"></textarea>
          <FieldError :message="fieldError('notes')" />
        </div>
          <button class="btn btn-premium mt-3" :disabled="busy">
            <span v-if="saving" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
            {{ saving ? 'Guardando...' : 'Registrar pago' }}
          </button>

        </form>


        <div class="admin-form-divider">
          <span>Periodos calculados</span>
        </div>

        <div class="payment-periods">
          <article v-for="period in periods" :key="period.period" :class="['payment-period-row', period.status, { overdue: period.is_overdue }]">
            <div>
              <strong>{{ period.period_label || formatPeriod(period.period) }}</strong>
              <small>Limite: {{ period.due_date || 'N/A' }}</small>
            </div>
            <div>
              <span>{{ money(period.amount_paid || 0) }} / {{ money(period.amount_due || property.monthly_payment_amount || 0) }}</span>
              <small>{{ periodStatusLabel(period) }}</small>
            </div>
          </article>
          <div v-if="!periods.length" class="empty-state compact">Los periodos apareceran cuando el backend regrese el resumen de pagos.</div>
        </div>

        <div class="admin-form-divider">
          <span>Historial de pagos reales</span>
        </div>

        <div class="payment-history">
          <article v-for="payment in property.payments" :key="payment.id" class="payment-history-row">
            <div>
              <strong>{{ money(payment.amount) }}</strong>
              <small>Pago real: {{ payment.paid_at || 'Sin fecha' }}</small>
              <small v-if="payment.applied_period">Aplicado a: {{ formatPeriod(payment.applied_period) }} · Limite: {{ payment.applied_due_date || 'N/A' }}</small>
              <small v-else>{{ paymentTypeLabel(payment.payment_type) }}</small>
              <small v-if="payment.reference || payment.folio">{{ payment.reference || payment.folio }}</small>
            </div>
            <div class="payment-history-actions">
              <span :class="['admin-status', payment.status === 'cancelled' ? 'is-danger' : '']">{{ payment.status === 'cancelled' ? 'Cancelado' : paymentTypeLabel(payment.payment_type) }}</span>
              <button v-if="payment.status !== 'cancelled'" type="button" class="btn btn-sm btn-danger" :disabled="busy" @click="$emit('delete-payment', payment)">
                <i class="fa-solid fa-ban"></i>
                Cancelar
              </button>
            </div>
          </article>
          <div v-if="!property.payments.length" class="empty-state compact">Aun no hay pagos registrados.</div>
        </div>
      </div>

      <footer class="admin-modal-footer">
        <button type="button" class="btn btn-outline-premium" :disabled="busy" @click="close">Cerrar</button>
      </footer>
    </section>
  </div>
</template>

<script>
import { money } from '@/utils/formatters'
import FieldError from '@/components/admin/FieldError.vue'

const todayDate = () => new Date().toISOString().slice(0, 10)
const toMonth = (value) => {
  if (!value) return ''
  return String(value).slice(0, 7)
}
const currentMonth = () => {
  const today = new Date()
  return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`
}

const emptyPayment = (property = {}) => ({
  payment_type: 'monthly',
  applied_month: toMonth(property.next_pending_period) || nextPendingMonth(property) || currentMonth(),
  amount: property.monthly_payment_amount || '',
  paid_at: todayDate(),
  payment_method: 'efectivo',
  reference: '',
  folio: '',
  notes: ''
})

function nextPendingMonth (property = {}) {
  const period = (property.periods || []).find(item => item.status !== 'paid')
  return period ? toMonth(period.period) : ''
}

export default {
  name: 'PaymentModal',
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
    loading: {
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
      form: emptyPayment(this.property)
    }
  },
  computed: {
    busy () {
      return this.saving || this.loading
    },
    summary () {
      return this.property.payment_status || {}
    },
    periods () {
      return this.property.periods || []
    },
    monthlyAmount () {
      return this.property.monthly_payment_amount ? `${money(this.property.monthly_payment_amount)} mensual` : 'Sin mensualidad'
    }
  },
  watch: {
    property: {
      deep: true,
      handler () {
        if (!this.saving) this.reset()
      }
    },
    'form.payment_type' (value) {
      if (value === 'monthly') {
        this.form.amount = this.property.monthly_payment_amount || this.form.amount
        this.form.applied_month = nextPendingMonth(this.property) || this.form.applied_month || currentMonth()
      }
    }
  },
  methods: {
    money,
    submit () {
      if (this.busy) return
      const payload = {
        payment_type: this.form.payment_type,
        paid_at: this.form.paid_at,
        amount: Number(this.form.amount || 0),
        payment_method: this.form.payment_method,
        reference: this.form.reference,
        folio: this.form.folio,
        notes: this.form.notes
      }

      if (this.form.payment_type === 'monthly') {
        payload.month = this.form.applied_month
        payload.applied_period = this.form.applied_month ? `${this.form.applied_month}-01` : null
      }

      this.$emit('save', payload)
    },
    reset () {
      this.form = emptyPayment(this.property)
    },
    fieldError (field) {
      return this.errors[field] || ''
    },
    close () {
      if (!this.busy) this.$emit('close')
    },
    formatPeriod (value) {
      if (!value) return 'Sin periodo'
      const date = new Date(`${String(value).slice(0, 7)}-01T00:00:00`)
      return date.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' })
    },
    paymentTypeLabel (type) {
      const labels = {
        monthly: 'Mensualidad',
        down_payment: 'Enganche',
        adjustment: 'Ajuste'
      }
      return labels[type] || 'Pago'
    },
    periodStatusLabel (period) {
      if (period.status === 'paid') return 'Pagado'
      if (period.status === 'partial') return period.is_overdue ? 'Parcial vencido' : 'Parcial'
      return period.is_overdue ? 'Vencido' : 'Pendiente'
    }
  }
}
</script>
<style scoped>
.col_full {
  grid-column: span 3;
}
</style>