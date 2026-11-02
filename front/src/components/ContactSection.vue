<template>
  <section id="contacto" class="section-block contact-section">
    <div class="container">
      <div class="contact-shell reveal-up">
        <div class="row g-4 align-items-center">
          <div class="col-lg-5">
            <span class="section-kicker">Agenda una visita</span>
            <h2>El siguiente paso hacia tu próxima propiedad.</h2>
            <p>  Coordina una visita con un asesor, conoce los detalles de la propiedad y resuelve tus dudas antes de tomar una decisión.</p>
          </div>

          <div class="col-lg-7">
            <form class="contact-form" @submit.prevent="sendWhatsApp">
              <div class="row g-3">
                <div class="col-md-6">
                  <input v-model="form.name" class="form-control" type="text" placeholder="Nombre" required>
                </div>
                <div class="col-md-6">
                  <input v-model="form.phone" class="form-control" type="tel" placeholder="Teléfono" required>
                </div>
                <div class="col-md-6">
                  <select v-model="form.propertyType" class="form-select">
                    <option>Casa</option>
                    <option>Departamento</option>
                    <option>Terreno</option>
                    <option>Local</option>
                    <option>Renta</option>
                    <option>Venta</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <input v-model="form.zone" class="form-control" type="text" placeholder="Zona de interés">
                </div>
                <div class="col-12">
                  <textarea v-model="form.message" class="form-control" rows="4" placeholder="Mensaje o presupuesto aproximado"></textarea>
                </div>
                <div class="col-12 d-flex flex-column flex-md-row gap-3">
                  
                  <button class="btn btn-premium btn-lg" type="submit" :disabled="sending">
                    {{ sending ? 'Enviando...' : 'Enviar' }}
                  </button>
                </div>
                <div v-if="success" class="col-12">
                  <div class="alert alert-success mb-0">Lead registrado correctamente.</div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import publicService from '@/services/publicService'

export default {
  name: 'ContactSection',
  props: {
    whatsapp: {
      type: String,
      required: true
    }
  },
  data () {
    return {
      form: {
        name: '',
        phone: '',
        propertyType: 'Casa',
        zone: '',
        message: ''
      },
      sending: false,
      success: false
    }
  },
  methods: {
    async sendWhatsApp () {
      const message = [
        'Hola, quiero solicitar información de una propiedad.',
        `Nombre: ${this.form.name}`,
        `Teléfono: ${this.form.phone}`,
        `Tipo: ${this.form.propertyType}`,
        `Zona: ${this.form.zone || 'No especificada'}`,
        `Mensaje: ${this.form.message || 'Sin mensaje adicional'}`
      ].join('\n')

      this.sending = true
      try {
        await publicService.createLead({
          name: this.form.name,
          phone: this.form.phone,
          email: '',
          source: 'landing-contact',
          message,
          property_type: this.form.propertyType,
          zone: this.form.zone
        })
        this.success = true
      } catch (error) {
        this.success = false
      }
      this.sending = false
      window.open(`https://wa.me/${this.whatsapp}?text=${encodeURIComponent(message)}`, '_blank', 'noopener,noreferrer')
    }
  }
}
</script>
