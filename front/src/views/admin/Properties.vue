<template>
  <section>
    <div class="admin-page-title">
      <span>Catalogo</span>
      <h1>Propiedades</h1>
      <p>
        {{ isAdvisor ? 'Solo puedes ver y administrar tus propiedades.' : 'Administracion completa del catalogo.' }}
      </p>
    </div>

    <div class="admin-toolbar">
      <button class="btn btn-premium" :disabled="saving || loading" @click="openPropertyModal()">
        <i class="fa-solid fa-plus"></i>
        Nueva propiedad
      </button>

      <div v-if="errorMessage" class="alert alert-danger mb-0">
        {{ errorMessage }}
      </div>
    </div>

    <!-- FILTROS SERVER SIDE -->
    <div class="admin-card property-filters">
      <div class="property-filters-header">
        <div>
          <span>Filtros</span>
          <strong>Buscar propiedades</strong>
        </div>

        <button
          v-if="hasActiveFilters"
          type="button"
          class="btn btn-sm btn-outline-secondary"
          :disabled="loading"
          @click="resetFilters"
        >
          <i class="fa-solid fa-rotate-left"></i>
          Limpiar
        </button>
      </div>

      <div class="property-filters-grid">
        <div class="admin-field filter-search">
          <label class="form-label">Buscar</label>
          <div class="filter-input-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input
              v-model.trim="filter.search"
              type="text"
              class="form-control"
              placeholder="Nombre, ubicacion, comprador, telefono..."
              :disabled="loading"
              @keyup="debouncedFilter"
            >
          </div>
        </div>

        <div class="admin-field">
          <label class="form-label">Estatus</label>
          <select
            v-model="filter.status"
            class="form-select"
            :disabled="loading"
            @change="applyFilters"
          >
            <option value="">Todos</option>
            <option value="active">Activos</option>
            <option value="inactive">Inactivos</option>
            <option value="sold">Vendidos</option>
          </select>
        </div>

        <div class="admin-field">
          <label class="form-label">Pagos</label>
          <select
            v-model="filter.payments"
            class="form-select"
            :disabled="loading"
            @change="applyFilters"
          >
            <option value="">Todos</option>
            <option value="current">Al corriente</option>
            <option value="pending">Con atraso</option>
            <option value="yellow">Amarillo</option>
            <option value="red">Rojo</option>
            <option value="without_buyer">Sin comprador</option>
          </select>
        </div>

        <div class="admin-field">
          <label class="form-label">Mostrar</label>
          <select
            v-model.number="pagination.per_page"
            class="form-select"
            :disabled="loading"
            @change="changePerPage"
          >
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
        </div>
      </div>

      <small class="filter-results">
        Mostrando {{ pagination.from || 0 }} - {{ pagination.to || 0 }} de {{ pagination.total || 0 }} propiedades
      </small>
    </div>

    <div class="admin-card table-responsive bt-b">
      <div v-if="loading" class="admin-loading-inline">
        <span class="spinner-border spinner-border-sm me-2"></span>
        Cargando propiedades...
      </div>

      <table v-else class="table align-middle">
        <tbody>
          <tr v-for="property in visibleProperties" :key="property.id">
            <td>
              <img class="admin-thumb" :src="property.image" :alt="property.title">
            </td>

            <td>
              <strong>{{ property.title }}</strong>
              <small>{{ property.location }} · Asesor {{ property.user_id || 'N/A' }}</small>
            </td>

            <td>
              <template v-if="hasBuyer(property)">
                <strong>{{ property.buyer_name || 'Sin comprador' }}</strong>
                <small>{{ property.buyer_number || 'Sin numero' }}</small>
              </template>

              <span v-else class="admin-status">
                No vendida
              </span>
            </td>

            <td>
              <template v-if="hasBuyer(property)">
                <span :class="['payment-indicator', `is-${paymentColor(property)}`]">
                  {{ paymentLabel(property) }}
                </span>
                <small>{{ paymentAmount(property) }}</small>
              </template>

              <small v-else>
                Disponible o inactiva
              </small>
            </td>

            <td class="text-end admin-table-actions">
              <a
                v-if="hasBuyer(property) && shouldShowReminder(property)"
                class="btn btn-sm btn-success"
                :href="reminderUrl(property)"
                target="_blank"
                rel="noopener"
              >
                <i class="fa-brands fa-whatsapp"></i>
                WhatsApp
              </a>

              <button
                class="btn btn-sm btn-outline-premium"
                :disabled="saving || loading"
                @click="openPropertyModal(property)"
              >
                <i class="fa-solid fa-pencil"></i>
                Editar
              </button>

              <button
                class="btn btn-sm btn-outline-premium"
                :disabled="saving || loading"
                @click="openGalleryModal(property)"
              >
                <i class="fa-solid fa-file-image"></i>
                Imagenes
              </button>

              <button
                class="btn btn-sm btn-outline-premium"
                :disabled="saving || loading"
                @click="openBuyerModal(property)"
              >
                <i class="fa-solid fa-user-gear"></i>
                Venta / Comprador
              </button>

              <button
                v-if="hasBuyer(property)"
                class="btn btn-sm btn-outline-premium"
                :disabled="saving || loading"
                @click="openPaymentModal(property)"
              >
                <i class="fa-solid fa-hand-holding-dollar"></i>
                Pagos
              </button>

              <button
                class="btn btn-sm btn-danger"
                :disabled="saving || loading"
                @click="removeProperty(property)"
              >
                <i class="fa-solid fa-trash"></i>
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="!loading && !visibleProperties.length" class="empty-state compact">
        No hay propiedades registradas con esos filtros.
      </div>

      <!-- PAGINACION -->
      <div v-if="!loading && pagination.last_page > 1" class="admin-pagination">
        <button
          type="button"
          class="btn btn-sm btn-outline-premium"
          :disabled="pagination.current_page <= 1 || loading"
          @click="changePage(pagination.current_page - 1)"
        >
          <i class="fa-solid fa-chevron-left"></i>
          Anterior
        </button>

        <button
          v-if="paginationPages[0] > 1"
          type="button"
          class="btn btn-sm btn-outline-premium"
          :disabled="loading"
          @click="changePage(1)"
        >
          1
        </button>

        <span v-if="paginationPages[0] > 2" class="pagination-dots">
          ...
        </span>

        <button
          v-for="page in paginationPages"
          :key="page"
          type="button"
          class="btn btn-sm"
          :class="page === pagination.current_page ? 'btn-premium' : 'btn-outline-premium'"
          :disabled="loading"
          @click="changePage(page)"
        >
          {{ page }}
        </button>

        <span v-if="paginationPages[paginationPages.length - 1] < pagination.last_page - 1" class="pagination-dots">
          ...
        </span>

        <button
          v-if="paginationPages[paginationPages.length - 1] < pagination.last_page"
          type="button"
          class="btn btn-sm btn-outline-premium"
          :disabled="loading"
          @click="changePage(pagination.last_page)"
        >
          {{ pagination.last_page }}
        </button>

        <button
          type="button"
          class="btn btn-sm btn-outline-premium"
          :disabled="pagination.current_page >= pagination.last_page || loading"
          @click="changePage(pagination.current_page + 1)"
        >
          Siguiente
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <BuyerModal
      v-if="activeModal === 'buyer' && modalProperty"
      :property="modalProperty"
      :saving="saving"
      :errors="formErrors"
      @close="closeModals"
      @save="saveBuyer"
    />

    <PaymentModal
      v-if="activeModal === 'payments' && modalProperty"
      :property="modalProperty"
      :saving="saving"
      :loading="paymentLoading"
      :errors="formErrors"
      @close="closeModals"
      @save="savePayment"
      @delete-payment="deletePayment"
    />

    <PropertyModal
      v-if="activeModal === 'property'"
      :property="modalProperty"
      :is-advisor="isAdvisor"
      :user="user"
      :saving="saving"
      :errors="formErrors"
      @close="closeModals"
      @save="saveProperty"
    />

    <GalleryModal
      v-if="activeModal === 'gallery' && modalProperty"
      :property="modalProperty"
      :saving="saving"
      :errors="formErrors"
      @close="closeModals"
      @upload="uploadGallery"
      @set-main="setMain"
      @delete-image="deleteImage"
    />
  </section>
</template>

<script>
import adminService from '@/services/adminService'
import fallbackProperties from '@/data/properties'
import authStore from '@/store/authStore'
import BuyerModal from '@/components/admin/BuyerModal.vue'
import GalleryModal from '@/components/admin/GalleryModal.vue'
import PaymentModal from '@/components/admin/PaymentModal.vue'
import PropertyModal from '@/components/admin/PropertyModal.vue'
import { normalizeProperties, normalizePropertyPaymentData } from '@/utils/adapters'
import { isAdvisor } from '@/utils/permissions'
import { money, paymentReminderMessage } from '@/utils/formatters'
import { confirmAction, confirmDelete, errorAlert, successAlert } from '@/utils/alerts'
import { errorMessage, validationErrors } from '@/utils/apiErrors'

export default {
  name: 'AdminProperties',

  components: {
    BuyerModal,
    GalleryModal,
    PaymentModal,
    PropertyModal
  },

  data () {
    return {
      items: [],
      errorMessage: '',
      formErrors: {},
      saving: false,
      loading: false,
      paymentLoading: false,
      activeModal: '',
      modalProperty: null,
      searchTimer: null,
      filter: {
        search: '',
        status: '',
        payments: ''
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0
      }
    }
  },

  computed: {
    isAdvisor,

    user () {
      return authStore.getUser() || {}
    },

    visibleProperties () {
      return this.items
    },

    hasActiveFilters () {
      return Boolean(
        this.filter.search ||
        this.filter.status ||
        this.filter.payments
      )
    },

    paginationPages () {
      const current = Number(this.pagination.current_page || 1)
      const last = Number(this.pagination.last_page || 1)
      const pages = []

      let start = Math.max(1, current - 2)
      let end = Math.min(last, current + 2)

      if (current <= 2) {
        end = Math.min(last, 5)
      }

      if (current >= last - 1) {
        start = Math.max(1, last - 4)
      }

      for (let page = start; page <= end; page++) {
        pages.push(page)
      }

      return pages
    },

    selected () {
      return this.modalProperty
    }
  },

  created () {
    this.load()
  },

  beforeDestroy () {
    if (this.searchTimer) {
      clearTimeout(this.searchTimer)
    }
  },

  methods: {
    money,

    async load (page = 1) {
      this.loading = true
      this.errorMessage = ''

      try {
        const response = await adminService.properties(this.buildParams(page))
        this.setPaginatedResponse(response.data, page)
      } catch (error) {
        console.error('No se pudieron cargar las propiedades', error)

        const properties = this.applyLocalFilters(normalizeProperties(fallbackProperties))
        const paginated = this.paginateLocal(properties, page)

        this.items = paginated.data
        this.pagination = paginated.pagination
      } finally {
        this.loading = false
      }
    },

    buildParams (page = 1) {
      const params = {
        page,
        per_page: this.pagination.per_page
      }

      if (this.filter.search) {
        params.search = this.filter.search
      }

      if (this.filter.status) {
        params.status = this.filter.status
      }

      if (this.filter.payments) {
        params.payments = this.filter.payments
      }

      if (this.isAdvisor && this.user && this.user.id) {
        params.user_id = this.user.id
      }

      return params
    },

    setPaginatedResponse (payload, fallbackPage = 1) {
      const response = payload || {}
      const rows = Array.isArray(response.data)
        ? response.data
        : Array.isArray(response)
          ? response
          : []

      const meta = response.meta || response

      const currentPage = Number(meta.current_page || fallbackPage || 1)
      const perPage = Number(meta.per_page || this.pagination.per_page || 10)
      const total = Number(meta.total || rows.length)
      const lastPage = Number(meta.last_page || Math.max(1, Math.ceil(total / perPage)))

      const from = meta.from !== undefined && meta.from !== null
        ? Number(meta.from)
        : rows.length
          ? ((currentPage - 1) * perPage) + 1
          : 0

      const to = meta.to !== undefined && meta.to !== null
        ? Number(meta.to)
        : rows.length
          ? ((currentPage - 1) * perPage) + rows.length
          : 0

      this.items = normalizeProperties(rows)

      this.pagination = {
        current_page: currentPage,
        last_page: lastPage,
        per_page: perPage,
        total,
        from,
        to
      }
    },

    debouncedFilter () {
      if (this.searchTimer) {
        clearTimeout(this.searchTimer)
      }

      this.searchTimer = setTimeout(() => {
        this.applyFilters()
      }, 400)
    },

    applyFilters () {
      this.load(1)
    },

    resetFilters () {
      this.filter = {
        search: '',
        status: '',
        payments: ''
      }

      this.load(1)
    },

    changePage (page) {
      const nextPage = Number(page)

      if (
        nextPage < 1 ||
        nextPage > this.pagination.last_page ||
        nextPage === this.pagination.current_page ||
        this.loading
      ) {
        return
      }

      this.load(nextPage)
    },

    changePerPage () {
      this.load(1)
    },

    normalizeText (value) {
      return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
    },

    applyLocalFilters (properties) {
      let rows = [...properties]

      if (this.isAdvisor && this.user && this.user.id) {
        rows = rows.filter(item => Number(item.user_id) === Number(this.user.id))
      }

      const search = this.normalizeText(this.filter.search)
      const status = String(this.filter.status || '').toLowerCase()
      const payments = String(this.filter.payments || '').toLowerCase()

      if (search) {
        rows = rows.filter(property => {
          const searchableText = this.normalizeText([
            property.title,
            property.location,
            property.address,
            property.state,
            property.municipality,
            property.city,
            property.buyer_name,
            property.buyer_number,
            property.buyer_phone,
            property.buyer_email,
            property.price,
            property.sale_price
          ].filter(Boolean).join(' '))

          return searchableText.includes(search)
        })
      }

      if (status) {
        rows = rows.filter(property => {
          if (status === 'sold') {
            return this.isSold(property)
          }

          return String(property.status || '').toLowerCase() === status
        })
      }

      if (payments) {
        rows = rows.filter(property => {
          const hasBuyer = this.hasBuyer(property)
          const color = this.paymentColor(property)

          if (payments === 'without_buyer') {
            return !hasBuyer
          }

          if (!hasBuyer) {
            return false
          }

          if (payments === 'current') {
            return color === 'green'
          }

          if (payments === 'pending') {
            return ['yellow', 'red'].includes(color)
          }

          return color === payments
        })
      }

      return rows
    },

    paginateLocal (properties, page = 1) {
      const perPage = Number(this.pagination.per_page || 10)
      const total = properties.length
      const lastPage = Math.max(1, Math.ceil(total / perPage))
      const currentPage = Math.min(Math.max(Number(page || 1), 1), lastPage)
      const start = (currentPage - 1) * perPage
      const data = properties.slice(start, start + perPage)

      return {
        data,
        pagination: {
          current_page: currentPage,
          last_page: lastPage,
          per_page: perPage,
          total,
          from: data.length ? start + 1 : 0,
          to: data.length ? start + data.length : 0
        }
      }
    },

    paymentColor (property) {
      const paymentStatus = property.payment_status || {}
      return String(paymentStatus.color || 'green').toLowerCase()
    },

    paymentLabel (property) {
      const paymentStatus = property.payment_status || {}

      if (paymentStatus.label) {
        return paymentStatus.label
      }

      const color = this.paymentColor(property)

      if (color === 'red') {
        return 'Atraso critico'
      }

      if (color === 'yellow') {
        return 'Con atraso'
      }

      return 'Al corriente'
    },

    paymentAmount (property) {
      const monthly = property.monthly_payment_amount
        ? `${money(property.monthly_payment_amount)} mensual`
        : 'Sin mensualidad'

      const summary = property.payment_status || {}
      const balance = summary.financed_balance !== undefined && summary.financed_balance !== null
        ? summary.financed_balance
        : summary.balance

      return balance !== undefined && balance !== null
        ? `${monthly} · saldo ${money(balance)}`
        : monthly
    },

    isSold (property) {
      return ['sold', 'vendida'].includes(String(property.status || '').toLowerCase())
    },

    hasBuyer (property) {
      return this.isSold(property) || property.buyer_id || property.buyer_name
    },

    shouldShowReminder (property) {
      return ['yellow', 'red'].includes(this.paymentColor(property)) && property.buyer_phone
    },

    reminderUrl (property) {
      const phone = String(property.buyer_phone || '').replace(/\D/g, '')
      return `https://wa.me/${phone}?text=${encodeURIComponent(paymentReminderMessage(property))}`
    },

    openBuyerModal (property) {
      this.formErrors = {}
      this.errorMessage = ''
      this.modalProperty = property
      this.activeModal = 'buyer'
    },

    async openPaymentModal (property) {
      this.formErrors = {}
      this.errorMessage = ''
      this.modalProperty = property
      this.activeModal = 'payments'
      await this.loadPropertyPayments(property)
    },

    openPropertyModal (property = null) {
      this.formErrors = {}
      this.errorMessage = ''
      this.modalProperty = property
      this.activeModal = 'property'
    },

    openGalleryModal (property) {
      this.formErrors = {}
      this.errorMessage = ''
      this.modalProperty = property
      this.activeModal = 'gallery'
    },

    closeModals () {
      if (this.saving || this.paymentLoading) return

      this.activeModal = ''
      this.modalProperty = null
      this.formErrors = {}
    },

    mergeModalProperty (updated) {
      this.items = this.items.map(item => Number(item.id) === Number(updated.id) ? updated : item)
      this.modalProperty = updated
    },

    async loadPropertyPayments (property = this.modalProperty) {
      if (!property || this.paymentLoading) return

      this.paymentLoading = true

      try {
        const response = await adminService.propertyPayments(property.id)
        const updated = normalizePropertyPaymentData(property, response.data)
        this.mergeModalProperty(updated)
      } catch (error) {
        console.error('No se pudieron cargar los pagos', error)
        this.errorMessage = errorMessage(error, 'No se pudieron cargar los pagos de la propiedad.')
      } finally {
        this.paymentLoading = false
      }
    },

    async saveProperty (payload) {
      if (this.saving) return

      const propertyId = this.modalProperty ? this.modalProperty.id : null

      this.errorMessage = ''
      this.formErrors = {}
      this.saving = true

      try {
        await adminService.saveProperty(payload, propertyId)
        await this.load(propertyId ? this.pagination.current_page : 1)
        await successAlert('Propiedad guardada', 'Los datos se guardaron correctamente.')
      } catch (error) {
        console.error('No se pudo guardar la propiedad', error)
        this.formErrors = validationErrors(error)
        this.errorMessage = errorMessage(error, 'No se pudo guardar la propiedad. Revisa la API.')
        await errorAlert('No se pudo guardar', this.errorMessage)
        return
      } finally {
        this.saving = false
      }

      this.closeModals()
    },

    async uploadGallery (files) {
      if (!this.modalProperty || this.saving) return

      this.errorMessage = ''
      this.formErrors = {}
      this.saving = true

      for (const file of files) {
        const formData = new FormData()
        formData.append('image', file)

        try {
          await adminService.uploadPropertyImage(this.modalProperty.id, formData)
        } catch (error) {
          console.error('No se pudo subir la imagen de galeria', error)
          this.formErrors = validationErrors(error)
          this.errorMessage = errorMessage(error, 'No se pudo subir una imagen de la galeria.')
          await errorAlert('No se pudo subir', this.errorMessage)
          this.saving = false
          return
        }
      }

      await this.load(this.pagination.current_page)
      this.modalProperty = this.items.find(item => item.id === this.modalProperty.id) || this.modalProperty
      this.saving = false
      await successAlert('Imagenes cargadas', 'La galeria se actualizo correctamente.')
    },

    async saveBuyer (payload) {
      if (!this.modalProperty || this.saving) return

      this.errorMessage = ''
      this.formErrors = {}
      this.saving = true

      try {
        await adminService.savePropertyBuyer(this.modalProperty.id, payload)
        await this.load(this.pagination.current_page)
        await successAlert('Comprador guardado', 'La informacion de venta y comprador se actualizo correctamente.')
      } catch (error) {
        console.error('No se pudo guardar el comprador', error)
        this.formErrors = validationErrors(error)

        if (Object.keys(this.formErrors).length) {
          this.errorMessage = errorMessage(error, 'Revisa los campos marcados.')
          await errorAlert('Datos incompletos', this.errorMessage)
          return
        }

        if (error.response) {
          this.errorMessage = errorMessage(error, 'No se pudo guardar el comprador.')
          await errorAlert('No se pudo guardar', this.errorMessage)
          return
        }

        const raw = {
          ...this.modalProperty.raw,
          buyer: {
            ...(this.modalProperty.raw && this.modalProperty.raw.buyer ? this.modalProperty.raw.buyer : {}),
            ...payload,
            id: this.modalProperty.buyer_id || Date.now()
          },
          status: 'sold'
        }

        const updated = normalizeProperties([raw])[0]
        this.items = this.items.map(item => item.id === this.modalProperty.id ? updated : item)
        await successAlert('Comprador guardado', 'Se guardo localmente para mantener el demo visible.')
      } finally {
        this.saving = false
      }

      this.closeModals()
    },

    async savePayment (paymentPayload) {
      if (!this.modalProperty || this.saving) return

      this.errorMessage = ''
      this.formErrors = {}
      this.saving = true

      const payload = {
        ...paymentPayload,
        amount: Number(paymentPayload.amount)
      }

      try {
        const response = await adminService.savePropertyPayment(this.modalProperty.id, payload)

        const updated = normalizePropertyPaymentData(this.modalProperty, {
          ...response.data,
          payments: response.data.payments || [...(this.modalProperty.payments || []), response.data.payment].filter(Boolean)
        })

        this.mergeModalProperty(updated)
        await this.loadPropertyPayments(updated)
        await successAlert('Pago registrado', 'El pago se agrego correctamente.')
      } catch (error) {
        console.error('No se pudo registrar el pago', error)
        this.formErrors = validationErrors(error)

        if (Object.keys(this.formErrors).length) {
          this.errorMessage = errorMessage(error, 'Revisa los campos marcados.')
          await errorAlert('Datos incompletos', this.errorMessage)
          return
        }

        if (error.response) {
          this.errorMessage = errorMessage(error, 'No se pudo registrar el pago.')
          await errorAlert('No se pudo registrar', this.errorMessage)
          return
        }

        const raw = {
          ...this.modalProperty.raw,
          payments: [...(this.modalProperty.payments || []), { ...payload, id: Date.now(), status: 'applied' }]
        }

        const updated = normalizeProperties([raw])[0]
        this.items = this.items.map(item => item.id === this.modalProperty.id ? updated : item)
        this.modalProperty = updated
        await successAlert('Pago registrado', 'Se guardo localmente para mantener el demo visible.')
      } finally {
        this.saving = false
      }
    },

    async deletePayment (payment) {
      if (!payment || this.saving) return

      const confirmed = await confirmDelete({
        title: '¿Cancelar pago?',
        text: 'El pago quedara cancelado para auditoria y ya no contara en los saldos.',
        confirmButtonText: 'Si, cancelar'
      })

      if (!confirmed) return

      this.errorMessage = ''
      this.formErrors = {}
      this.saving = true

      try {
        await adminService.deletePropertyPayment(payment.id)
        await this.loadPropertyPayments(this.modalProperty)
        await successAlert('Pago cancelado', 'El pago se cancelo correctamente.')
      } catch (error) {
        console.error('No se pudo cancelar el pago', error)
        this.errorMessage = errorMessage(error, 'No se pudo cancelar el pago.')
        await errorAlert('No se pudo cancelar', this.errorMessage)
      } finally {
        this.saving = false
      }
    },

    async setMain (image) {
      if (this.saving) return

      const confirmed = await confirmAction({
        title: '¿Marcar como principal?',
        text: 'Esta imagen se mostrara como portada de la propiedad.',
        confirmButtonText: 'Si, marcar'
      })

      if (!confirmed) return

      this.errorMessage = ''
      this.formErrors = {}
      this.saving = true

      try {
        await adminService.setMainImage(image.id)
        await this.load(this.pagination.current_page)
        this.modalProperty = this.items.find(item => item.id === this.modalProperty.id) || this.modalProperty
        await successAlert('Imagen principal', 'La imagen principal se actualizo correctamente.')
      } catch (error) {
        console.error('No se pudo marcar la imagen principal', error)
        this.errorMessage = errorMessage(error, 'No se pudo marcar la imagen principal.')
        await errorAlert('No se pudo actualizar', this.errorMessage)
      } finally {
        this.saving = false
      }
    },

    async deleteImage (image) {
      if (this.saving) return

      const confirmed = await confirmDelete({
        title: '¿Eliminar imagen?',
        text: 'La imagen se eliminara de la galeria de esta propiedad.'
      })

      if (!confirmed) return

      this.errorMessage = ''
      this.formErrors = {}
      this.saving = true

      try {
        await adminService.deletePropertyImage(image.id)
        await this.load(this.pagination.current_page)
        this.modalProperty = this.items.find(item => item.id === this.modalProperty.id) || this.modalProperty
        await successAlert('Imagen eliminada', 'La imagen se elimino correctamente.')
      } catch (error) {
        console.error('No se pudo eliminar la imagen', error)
        this.errorMessage = errorMessage(error, 'No se pudo eliminar la imagen.')
        await errorAlert('No se pudo eliminar', this.errorMessage)
      } finally {
        this.saving = false
      }
    },

    async removeProperty (property) {
      if (this.saving) return

      const confirmed = await confirmDelete({
        title: '¿Eliminar propiedad?',
        text: `Se eliminara "${property.title}".`
      })

      if (!confirmed) return

      this.errorMessage = ''
      this.saving = true

      try {
        await adminService.deleteProperty(property.id)

        const nextPage = this.items.length === 1 && this.pagination.current_page > 1
          ? this.pagination.current_page - 1
          : this.pagination.current_page

        await this.load(nextPage)
        await successAlert('Propiedad eliminada', 'La propiedad se elimino correctamente.')
      } catch (error) {
        console.error('No se pudo eliminar la propiedad', error)
        this.errorMessage = errorMessage(error, 'No se pudo eliminar la propiedad.')
        await errorAlert('No se pudo eliminar', this.errorMessage)
      } finally {
        this.saving = false
      }
    }
  }
}
</script>

<style scoped>
.property-filters {
  margin-bottom: 1.25rem;
  padding: 1rem;
}

.property-filters-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.property-filters-header span {
  display: block;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  opacity: 0.65;
}

.property-filters-header strong {
  display: block;
  font-size: 1rem;
}

.property-filters-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 0.7fr;
  gap: 1rem;
  align-items: end;
}

.filter-input-icon {
  position: relative;
}

.filter-input-icon i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: 0.55;
  pointer-events: none;
}

.filter-input-icon .form-control {
  padding-left: 2.5rem;
}

.filter-results {
  display: block;
  margin-top: 0.85rem;
  opacity: 0.7;
}

.admin-loading-inline {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 160px;
  font-weight: 600;
  opacity: 0.75;
}

.admin-pagination {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 0.45rem;
  flex-wrap: wrap;
  padding: 1rem 0 0;
}

.pagination-dots {
  padding: 0 0.35rem;
  opacity: 0.65;
}

@media (max-width: 991px) {
  .property-filters-grid {
    grid-template-columns: 1fr;
  }

  .admin-pagination {
    justify-content: center;
  }
}
</style>