<template>
  <footer class="footer-section">
    <div class="container">
      <div class="row g-4 align-items-start">

        <div class="col-lg-4">
          <h3>Inmobiliaria</h3>
          <p>
            Propiedades, asesoría y seguimiento profesional para comprar, vender o invertir con mayor confianza.
          </p>
        </div>

        <div class="col-lg-2 col-md-4">
          <h4>Propiedades</h4>
          <ul>
            <li>Casas</li>
            <li>Departamentos</li>
            <li>Terrenos</li>
            <li>Locales comerciales</li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-4">
          <h4>Servicios</h4>
          <ul>
            <li>Compra y venta</li>
            <li>Renta de inmuebles</li>
            <li>Asesoría inmobiliaria</li>
            <li>Agenda de visitas</li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-4">
          <h4>Hablemos</h4>
          <p>
            Recibe atención personalizada y encuentra la propiedad adecuada para tu objetivo.
          </p>
          <a href="#contacto">Contactar asesor</a>
        </div>

      </div>

      <div class="footer-bottom">
        <p>© 2026 Andarma Inmobiliaria. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'FooterSection',
  props: {
    whatsapp: {
      type: String,
      required: true
    }
  },
  computed: {
    whatsappLink() {
      const text = encodeURIComponent('Hola, quiero solicitar información sobre propiedades con {{ $env.appName }}. Quiero más información.')
      return `https://wa.me/${this.whatsapp}?text=${text}`
    }
  }
}
</script>
