<template>
  <section id="testimonios" class="section-block testimonials-section">
    <div class="container">
      <div class="section-heading reveal-up">
        <span class="section-kicker">Testimonios</span>
        <h2>Experiencias que generan confianza.</h2>
        <p>Acompañamiento cercano, información clara y seguimiento profesional en cada etapa.</p>
      </div>

      <div class="row g-4">
        <div v-for="testimonial in testimonials" :key="testimonial.id" class="col-lg-4">
          <div class="testimonial-card reveal-scale">
            <p>“{{ testimonial.quote }}”</p>
            <div>
              <strong>{{ testimonial.name }}</strong>
              <span>{{ testimonial.role }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'TestimonialsSection',
  props: {
    testimonials: {
      type: Array,
      required: true
    }
  }
}
</script>
