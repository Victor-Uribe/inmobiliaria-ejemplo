<template>
  <section id="beneficios" class="section-block about-section">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-5">
          <div class="section-heading text-start reveal-up">
            <span class="section-kicker">Asesoría con visión comercial</span>

            <h2>
              Compra, vende e invierte con una estrategia clara.
            </h2>

            <p>
              Te acompañamos con información confiable, atención personalizada y seguimiento profesional para que cada operación avance con seguridad.
            </p>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="benefit-grid">
            <div class="benefit-card reveal-scale">
              <div class="benefit-card-content">
                <span>01</span>
                <h3>Compra segura</h3>
                <p>
                  Te guiamos para elegir con información clara.
                </p>
              </div>
            </div>
            <div class="benefit-card reveal-scale">
              <div class="benefit-card-content">
                <span>02</span>
                <h3>Venta estratégica</h3>
                <p>
                  Ayudamos a presentar y posicionar mejor tu propiedad.
                </p>
              </div>
            </div>

            <div class="benefit-card reveal-scale">
              <div class="benefit-card-content">
                <span>03</span>
                <h3>Inversión patrimonial</h3>
                <p>
                  Identificamos oportunidades con potencial de crecimiento.
                </p>
              </div>
            </div>

            <div class="benefit-card reveal-scale">
              <div class="benefit-card-content">
                <span>04</span>
                <h3>Seguimiento profesional</h3>
                <p>
                  Acompañamiento desde el primer contacto hasta el cierre.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'AboutSection'
}
</script>

<style scoped>
.about-section {
  position: relative;
  overflow: hidden;
  background:
    radial-gradient(circle at 75% 20%, rgba(218, 176, 92, .12), transparent 34%),
    linear-gradient(180deg, #090806 0%, #050505 100%);
}

.benefit-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

.benefit-card {
  position: relative;
  min-height: 240px;
  padding: 34px;
  border-radius: 28px;
  overflow: hidden;
  background:
    linear-gradient(145deg, rgba(255,255,255,.09), rgba(255,255,255,.03));
  border: 1px solid rgba(255,255,255,.13);
  box-shadow: 0 28px 70px rgba(0,0,0,.32);
}

.benefit-card-featured {
  min-height: 500px;
  grid-row: span 2;
  display: flex;
  align-items: flex-end;
  background: #111;
  border-color: rgba(230, 190, 104, .25);
}

.benefit-card-image {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  transform: scale(1.04);
  opacity: .82;
  transition: transform .5s ease, opacity .5s ease;
}

.benefit-card-featured:hover .benefit-card-image {
  transform: scale(1.08);
  opacity: .9;
}

.benefit-card-overlay {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg, rgba(0,0,0,.08) 0%, rgba(0,0,0,.78) 72%, rgba(0,0,0,.94) 100%),
    linear-gradient(90deg, rgba(0,0,0,.55) 0%, rgba(0,0,0,.12) 100%);
  z-index: 1;
}

.benefit-card-content {
  position: relative;
  z-index: 2;
}

.benefit-card span {
  display: inline-block;
  margin-bottom: 20px;
  color: #f2d180;
  font-size: 13px;
  font-weight: 900;
  letter-spacing: .18em;
}

.benefit-card h3 {
  color: #fff;
  font-size: clamp(21px, 2vw, 28px);
  line-height: 1.08;
  font-weight: 800;
  margin: 0 0 18px;
  max-width: 360px;
}

.benefit-card p {
  color: rgba(255,255,255,.68);
  font-size: 17px;
  line-height: 1.65;
  margin: 0;
  max-width: 360px;
}

.benefit-card-featured h3,
.benefit-card-featured p {
  max-width: 390px;
}

@media (max-width: 991px) {
  .benefit-grid {
    grid-template-columns: 1fr;
  }

  .benefit-card-featured {
    grid-row: auto;
    min-height: 430px;
  }
}

@media (max-width: 575px) {
  .benefit-card {
    min-height: 220px;
    padding: 26px;
    border-radius: 22px;
  }

  .benefit-card-featured {
    min-height: 380px;
  }

  .benefit-card p {
    font-size: 15px;
  }
}
</style>