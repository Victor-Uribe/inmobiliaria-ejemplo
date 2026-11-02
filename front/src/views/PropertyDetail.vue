<template>
  <main>
    <Navbar :whatsapp="whatsapp" />

    <section v-if="property" class="detail-hero">
      <div class="container">
        <div class="row g-5 align-items-start">
          <div class="col-lg-7">
            <div class="detail-gallery">
              <img :src="selectedImage" :alt="property.title">
            </div>
            <div class="detail-thumbs" v-if="property.images.length > 1">
              <button
                v-for="image in property.images"
                :key="image.id"
                type="button"
                :class="{ active: selectedImage === image.url }"
                @click="selectedImage = image.url"
              >
                <img :src="image.url" :alt="property.title">
              </button>
            </div>
          </div>

          <div class="col-lg-5">
            <aside class="detail-panel">
              <span class="section-kicker">{{ property.type }}</span>
              <h1>{{ property.title }}</h1>
              <strong class="detail-price">{{ property.price }}</strong>
              <p class="property-location">{{ property.location }}</p>
              <p>{{ property.description }}</p>             

              <a class="btn btn-premium btn-lg w-100" :href="whatsappLink" target="_blank" rel="noopener">
                Contactar por WhatsApp
              </a>

              <form class="detail-lead-form" @submit.prevent="sendLead">
                <h2>Solicitar informacion</h2>
                <input v-model="lead.name" class="form-control" placeholder="Nombre" required>
                <input v-model="lead.phone" class="form-control" placeholder="Telefono">
                <input v-model="lead.email" type="email" class="form-control" placeholder="Correo">
                <textarea v-model="lead.message" class="form-control" rows="4" required></textarea>
                <button class="btn btn-outline-premium w-100" :disabled="sending">
                  {{ sending ? 'Enviando...' : 'Registrar lead' }}
                </button>
                <div v-if="success" class="alert alert-success mb-0">Lead registrado.</div>
              </form>
            </aside>
          </div>
        </div>
      </div>
    </section>

    <FooterSection :whatsapp="whatsapp" />
  </main>
</template>

<script>
import Navbar from '@/components/Navbar.vue'
import FooterSection from '@/components/FooterSection.vue'
import publicService from '@/services/publicService'
import fallbackProperties from '@/data/properties'
import { normalizeProperty, unwrap } from '@/utils/adapters'
import { propertyWhatsAppMessage, slugify } from '@/utils/formatters'

export default {
  name: 'PropertyDetail',
  components: { Navbar, FooterSection },
  data () {
    return {
      whatsapp: process.env.VUE_APP_WHATSAPP_PHONE || '525566266152',
      property: null,
      selectedImage: '',
      sending: false,
      success: false,
      lead: { name: '', phone: '', email: '', message: '' }
    }
  },
  computed: {
    whatsappLink () {
      const text = encodeURIComponent(propertyWhatsAppMessage(this.property))
      return `https://wa.me/${this.whatsapp}?text=${text}`
    }
  },
  created () {
    this.load()
  },
  methods: {
    async load () {
      try {
        const response = await publicService.property(this.$route.params.slug)
        this.property = normalizeProperty(unwrap(response))
      } catch (error) {
        const item = fallbackProperties.find(property => slugify(property.title) === this.$route.params.slug) || fallbackProperties[0]
        this.property = normalizeProperty(item)
      }
      this.selectedImage = this.property.image
      this.lead.message = propertyWhatsAppMessage(this.property)
      document.title = `${this.property.title} | ${this.$env.appName}`
    },
    async sendLead () {
      this.sending = true
      try {
        await publicService.createLead({
          property_id: this.property.id,
          name: this.lead.name,
          phone: this.lead.phone,
          email: this.lead.email,
          message: this.lead.message,
          source: 'property-detail'
        })
        this.success = true
      } catch (error) {
        this.success = false
      }
      this.sending = false
    }
  }
}
</script>
