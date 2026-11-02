<template>
  <main>
    <Navbar :whatsapp="whatsapp" />

    <section class="catalog-hero">
      <div class="container">
        <span class="section-kicker">Propiedades disponibles</span>
        <h1>Encuentra la opción ideal para vivir, invertir o hacer crecer tu patrimonio.</h1>
        <p>  Consulta propiedades disponibles con detalles clave, ubicación, precio y atención directa con un asesor.</p>
      </div>
    </section>

    <section class="section-block mb-4">
      <div class="container">
        <div class="row g-4 mt-4">
          <div v-for="property in properties" :key="property.id" class="col-md-6 col-xl-4">
            <PropertyCard :property="property" :whatsapp="whatsapp" />
          </div>
        </div>

        <div v-if="!properties.length" class="empty-state">
          <h2>No hay propiedades con esos filtros.</h2>
          <p>Prueba limpiando el rango de precio o contacta a {$env.AppName} por WhatsApp.</p>
        </div>
      </div>
    </section>

    <FooterSection :whatsapp="whatsapp" />
  </main>
</template>

<script>
import Navbar from '@/components/Navbar.vue'
import FooterSection from '@/components/FooterSection.vue'
import PropertyCard from '@/components/PropertyCard.vue'
import publicService from '@/services/publicService'
import fallbackProperties from '@/data/properties'
import { normalizeProperties, unwrap } from '@/utils/adapters'

export default {
  name: 'Properties',
  components: { Navbar, FooterSection, PropertyCard },
  data () {
    return {
      whatsapp: process.env.VUE_APP_WHATSAPP_PHONE || '525566266152',
      filters: {
        municipality: this.$route.query.municipality || '',
        state: this.$route.query.state || '',
        min_price: this.$route.query.min_price || '',
        max_price: this.$route.query.max_price || ''
      },
      properties: []
    }
  },
  created () {
    this.load()
  },
  methods: {
    async load () {
      try {
        const response = await publicService.properties(this.filters)
        this.properties = normalizeProperties(unwrap(response))
      } catch (error) {
        this.properties = normalizeProperties(fallbackProperties)
      }
    }
  }
}
</script>
<style scoped>
.section-block{
  padding: 0px 0;
}
.catalog-hero, .detail-hero{
      min-height: 35vh;
}
</style>