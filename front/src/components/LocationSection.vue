<template>
  <section id="ubicacion" class="section-block location-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-5">
          <div class="section-heading text-start reveal-up">
            <span class="section-kicker">Zona de operación</span>
            <h2>Propiedades en ubicaciones clave para vivir, invertir o emprender.</h2>
            <p>
                  Te ayudamos a encontrar opciones residenciales, comerciales y de inversión en zonas con potencial, buena conectividad y oportunidades reales.
            </p>
            <!-- <ul class="location-list">
              <li>Zonas residenciales</li>
              <li>Áreas comerciales</li>
              <li>Zonas de crecimiento</li>
              <li>Los Reyes La Paz</li>
              <li>Nezahualcóyotl</li>
            </ul> -->
          </div>
        </div>

        <div class="col-lg-7">
          <div class="map-card reveal-scale">
            <img src="@/assets/images/nacional.png" alt="Mapa conceptual de cobertura en Estado de México">
            <div class="map-glass">
              <span>Cobertura inmobiliaria</span>
              <strong>Zonas estratégicas</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'LocationSection'
}
</script>
