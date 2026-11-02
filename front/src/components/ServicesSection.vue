<template>
  <section class="section-block services-section">
    <div class="container">
      <div class="section-heading reveal-up">
        <span class="section-kicker">Servicios inmobiliarios</span>
        <h2>Compra, vende, renta o invierte con acompañamiento profesional.</h2>
        <p>
          Te ofrecemos soluciones inmobiliarias claras, atención personalizada y seguimiento en cada etapa para avanzar con seguridad.
        </p>
      </div>

      <div class="row g-4">
        <div v-for="service in services" :key="service.title" class="col-md-6 col-lg-3">
          <div class="service-card reveal-up">
            <div class="service-icon">
              <i class="fa-solid" :class="service.icon"></i>
            </div>
            <h3>{{ service.title }}</h3>
            <p>{{ service.text }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'ServicesSection',
  data () {
    return {
      services: [
        {
          icon: 'fa-solid fa-house',
          title: 'Venta de propiedades',
          text: 'Promoción y seguimiento para conectar con compradores.'
        },
        {
          icon: 'fa-solid fa-key',
          title: 'Compra de inmuebles',
          text: 'Opciones claras y asesoría para elegir mejor.'
        },
        {
          icon: 'fa-solid fa-building',
          title: 'Renta de espacios',
          text: 'Casas, departamentos, locales y oficinas disponibles.'
        },
        {
          icon: 'fa-solid fa-chart-line',
          title: 'Inversión inmobiliaria',
          text: 'Oportunidades para crecer tu patrimonio.'
        }
      ]
    }
  }
}
</script>
