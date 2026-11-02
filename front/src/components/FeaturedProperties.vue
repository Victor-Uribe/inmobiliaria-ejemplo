<template>
  <section id="propiedades" class="section-block featured-section">
    <div class="container">
      <div class="section-heading reveal-up">
        <span class="section-kicker">Propiedades destacadas</span>
        <h2>Encuentra la propiedad ideal para vivir, invertir o hacer crecer tu patrimonio.</h2>
        <p>
              Revisa opciones disponibles con detalles clave, asesoría profesional y atención personalizada para avanzar con seguridad.
        </p>
      </div>

      <div class="row g-4">
        <div v-for="property in properties" :key="property.id" class="col-md-6 col-xl-4">
          <PropertyCard :property="property" :whatsapp="whatsapp" />
        </div>
      </div>

      <div class="text-center mt-5">
        <router-link class="btn btn-outline-premium btn-lg" :to="{ name: 'properties' }">Ver catalogo completo</router-link>
      </div>
    </div>
  </section>
</template>

<script>
import PropertyCard from '@/components/PropertyCard.vue'

export default {
  name: 'FeaturedProperties',
  components: {
    PropertyCard
  },
  props: {
    properties: {
      type: Array,
      required: true
    },
    whatsapp: {
      type: String,
      required: true
    }
  }
}
</script>
