<template>
  <article class="property-card reveal-up">
    <div class="property-image-wrap">
      <img :src="property.image" :alt="property.title" class="property-image">
      <span class="property-badge">{{ property.type }}</span>
    </div>

    <div class="property-body">
      <div class="d-flex justify-content-between align-items-start gap-3">
        <div>
          <p class="property-location">{{ property.location }}</p>
          <h3>{{ property.title }}</h3>
        </div>

      </div>

      <p class="property-description">{{ property.description }}</p>

      <!-- <div class="property-features">
        <span v-if="property.bedrooms > 0">{{ property.bedrooms }} rec.</span>
        <span v-if="property.bathrooms > 0">{{ property.bathrooms }} baños</span>
        <span>{{ property.area }} m²</span>
      </div> -->

      <div class="property-footer">
        <strong>{{ property.price }}</strong>
        <div class="property-actions">
          <button class="btn btn-outline-premium" type="button" @click="shareOnWhatsApp(property)"><i
              class="fa-brands fa-whatsapp"></i></button>
          <button class="share-button" type="button" @click="shareOnFacebook(property)"
            aria-label="Compartir en Facebook">
            <i class="fa-brands fa-facebook-f"></i>
          </button>
          <router-link :to="{ name: 'property-detail', params: { slug: property.slug } }">Detalle</router-link>
        </div>
      </div>
    </div>
  </article>
</template>

<script>
import { propertyWhatsAppMessage } from '@/utils/formatters'

export default {
  name: 'PropertyCard',
  props: {
    property: {
      type: Object,
      required: true
    },
    whatsapp: {
      type: String,
      required: true
    }
  },
  computed: {
    whatsappLink() {
      const text = encodeURIComponent(propertyWhatsAppMessage(this.property))
      return `https://wa.me/${this.whatsapp}?text=${text}`
    }
  },
  methods: {
    shareOnFacebook(property) {
      const shareUrl = `${process.env.VUE_APP_BACKEND_URL}/share/property/${property.id}`
      const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`
      window.open(facebookUrl, '_blank', 'width=700,height=500')
    },
    shareOnWhatsApp(property) {
      const shareUrl = `${process.env.VUE_APP_BACKEND_URL}/share/property/${property.id}`
      const title = property.title || 'Propiedad en venta'

      const text = `${title}, conoce los detalles, fotos y disponibilidad de esta propiedad.\n\nVer propiedad:\n${shareUrl}`
      const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text)}`
      window.open(whatsappUrl, '_blank')
    }

  }
}
</script>
