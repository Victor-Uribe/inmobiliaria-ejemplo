<template>
  <section id="inicio" class="promo-reel-section main-banner">
    <div class="promo-reel">
      <div class="promo-noise"></div>
      <div class="promo-light promo-light-one"></div>
      <div class="promo-light promo-light-two"></div>

      <div
        v-for="(slide, index) in slides"
        :key="slide.id || index"
        :class="[
          'property-opening-slide',
          'fullscreen-slide',
          { active: index === currentIndex }
        ]"
      >
        <img
          class="opening-bg-image"
          :src="slide.image"
          :alt="slide.alt || slide.title"
          draggable="false"
        >

        <div class="opening-content">
          <span v-if="slide.kicker">{{ slide.kicker }}</span>

          <h1>{{ slide.title }}</h1>

          <p v-if="slide.description">
            {{ slide.description }}
          </p>

          <a
            v-if="slide.button_url && slide.button_url !== 'null'"
            :href="slide.button_url"
            target="_blank"
            rel="noopener"
          >
            {{ slide.button_text || 'Solicitar información' }}
          </a>
        </div>
      </div>

      <div
        v-if="slides.length > 1"
        ref="miniStrip"
        class="mini-property-strip fullscreen-mini-strip"
      >
        <button
          v-for="(slide, index) in slides"
          :key="`mini-${slide.id || index}`"
          :ref="`thumb-${index}`"
          :class="['mini-property-card', { active: index === currentIndex }]"
          type="button"
          @mousedown.prevent
          @click.prevent.stop="goTo(index)"
        >
          <img
            :src="slide.image"
            :alt="slide.alt || slide.title"
            draggable="false"
          >
          <span>{{ slide.shortTitle || slide.short_title || slide.title }}</span>
        </button>
      </div>

      <div
        v-if="slides.length > 1"
        class="opening-controls fullscreen-controls"
      >
        <button
          type="button"
          aria-label="Propiedad anterior"
          @mousedown.prevent
          @click.prevent.stop="prev"
        >
          <i class="fa-solid fa-angle-left"></i>
        </button>

        <button
          type="button"
          aria-label="Siguiente propiedad"
          @mousedown.prevent
          @click.prevent.stop="next"
        >
          <i class="fa-solid fa-angle-right"></i>
        </button>

        <div class="opening-progress">
          <i :key="`progress-${currentIndex}`"></i>
        </div>
      </div>

      <div class="banner-bottom-line"></div>
    </div>
  </section>
</template>

<script>
import propertyOne from '@/assets/images/property-1.svg'

export default {
  name: 'PromoBanner',

  props: {
    whatsapp: {
      type: String,
      required: true
    },
    properties: {
      type: Array,
      default: () => []
    },
    banners: {
      type: Array,
      default: () => []
    }
  },

  data () {
    return {
      currentIndex: 0,
      intervalId: null,
      defaultSlides: [
        {
          id: 'default-1',
          image: propertyOne,
          alt: 'Casa residencial con jardín',
          kicker: 'Ixtapaluca · Casa familiar',
          title: 'Residencial con jardín',
          shortTitle: 'Casa familiar',
          description: 'Ideal para compradores que buscan ubicación, diseño y comodidad en Ixtapaluca.',
          cta: 'Agendar visita'
        }
      ]
    }
  },

  computed: {
    slides () {
      if (this.banners && this.banners.length) {
        return this.banners.map((banner, index) => ({
          id: banner.id || `banner-${index}`,
          image: banner.image,
          alt: banner.alt || banner.title || 'Banner inmobiliario',
          kicker: banner.kicker || '',
          title: banner.title || '',
          shortTitle: banner.shortTitle || banner.short_title || banner.title || '',
          description: banner.description || '',
          button_url: banner.button_url,
          button_text: banner.button_text || 'Solicitar información'
        }))
      }

      if (this.properties && this.properties.length) {
        return this.properties.slice(0, 3).map((property, index) => ({
          id: property.id || `property-${index}`,
          image: property.image,
          alt: property.title || 'Propiedad inmobiliaria',
          kicker: property.location || property.municipality || '',
          title: property.title || 'Propiedad en venta',
          shortTitle: property.title || 'Propiedad',
          description: property.description || '',
          cta: 'Ver detalles',
          slug: property.slug
        }))
      }

      return this.defaultSlides
    }
  },

  watch: {
    slides: {
      handler () {
        if (!this.slides.length) return

        if (this.currentIndex >= this.slides.length) {
          this.currentIndex = 0
        }

        this.resetAuto()
      },
      deep: true
    }
  },

  mounted () {
    this.startAuto()
  },

  beforeDestroy () {
    this.stopAuto()
  },

  methods: {
    slideWhatsappLink (slide) {
      const text = encodeURIComponent(`Hola, me interesa esta propiedad: ${slide.title}. Quiero más información.`)
      return `https://wa.me/${this.whatsapp}?text=${text}`
    },

    startAuto () {
      this.stopAuto()

      if (!this.slides || this.slides.length <= 1) return

      this.intervalId = window.setInterval(() => {
        this.next(false)
      }, 5000)
    },

    stopAuto () {
      if (this.intervalId) {
        window.clearInterval(this.intervalId)
        this.intervalId = null
      }
    },

    resetAuto () {
      this.startAuto()
    },

    next (reset = true) {
      if (!this.slides || this.slides.length <= 1) return

      this.currentIndex = (this.currentIndex + 1) % this.slides.length

      if (reset) this.resetAuto()
    },

    prev () {
      if (!this.slides || this.slides.length <= 1) return

      this.currentIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length
      this.resetAuto()
    },

    goTo (index) {
      if (index === this.currentIndex) return
      if (index < 0 || index >= this.slides.length) return

      this.currentIndex = index
      this.resetAuto()
      this.centerMiniCard(index)
    },

    centerMiniCard (index) {
      this.$nextTick(() => {
        const strip = this.$refs.miniStrip
        const thumbRef = this.$refs[`thumb-${index}`]

        if (!strip || !thumbRef) return

        const thumb = Array.isArray(thumbRef) ? thumbRef[0] : thumbRef
        if (!thumb) return

        const stripWidth = strip.clientWidth
        const thumbLeft = thumb.offsetLeft
        const thumbWidth = thumb.offsetWidth

        strip.scrollTo({
          left: thumbLeft - (stripWidth / 2) + (thumbWidth / 2),
          behavior: 'smooth'
        })
      })
    }
  }
}
</script>

<style scoped>
.promo-reel-section {
  position: relative;
  width: 100%;
  overflow: hidden;
  background: #050505;
}

.promo-reel {
  position: relative;
  min-height: 100vh;
  overflow: hidden;
  background: #050505;
  isolation: isolate;
}

.property-opening-slide {
  position: absolute;
  inset: 0;
  z-index: 1;
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transition: opacity .55s ease, visibility .55s ease;
}

.property-opening-slide.active {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
  z-index: 2;
}

.opening-bg-image {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  min-height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
  user-select: none;
  pointer-events: none;
  transform: none !important;
}

.property-opening-slide::after {
  content: "";
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: none;
  background:
    linear-gradient(90deg, rgba(0,0,0,.95) 0%, rgba(0,0,0,.62) 38%, rgba(0,0,0,.18) 100%),
    linear-gradient(180deg, rgba(0,0,0,.10) 0%, rgba(0,0,0,.88) 100%);
}

.opening-content {
  position: relative;
  z-index: 3;
  min-height: 100vh;
  width: min(920px, 92vw);
  padding: 150px 0 130px clamp(22px, 6vw, 100px);
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.opening-content span {
  color: #f4d487;
  font-size: clamp(12px, 1vw, 15px);
  font-weight: 900;
  letter-spacing: .18em;
  text-transform: uppercase;
  margin-bottom: 18px;
}

.opening-content h1 {
  color: #fff;
  font-size: clamp(54px, 8vw, 132px);
  line-height: .86;
  font-weight: 900;
  text-transform: uppercase;
  margin: 0 0 24px;
  max-width: 980px;
}

.opening-content p {
  color: rgba(255,255,255,.78);
  font-size: clamp(16px, 1.3vw, 22px);
  line-height: 1.6;
  max-width: 540px;
  margin: 0 0 28px;
}

.opening-content a {
  width: fit-content;
  border-radius: 999px;
  padding: 14px 24px;
  background: #f4d487;
  color: #111;
  font-weight: 900;
  text-decoration: none;
  box-shadow: 0 16px 35px rgba(0,0,0,.28);
}

.fullscreen-mini-strip {
  position: absolute;
  right: clamp(20px, 7vw, 110px);
  bottom: 92px;
  z-index: 8;
  display: flex;
  gap: 14px;
  max-width: min(670px, 84vw);
  overflow-x: auto;
  overflow-y: visible;
  padding: 30px 4px;
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
}

.fullscreen-mini-strip::-webkit-scrollbar {
  display: none;
}

.mini-property-card {
  flex: 0 0 140px;
  width: 140px;
  height: 92px;
  position: relative;
  overflow: hidden;
  border-radius: 14px;
  border: 1px solid rgba(244, 212, 135, .28);
  background: rgba(0,0,0,.65);
  padding: 0;
  cursor: pointer;
  outline: none;
  transform: translateZ(0);
}

.mini-property-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
  pointer-events: none;
  user-select: none;
}

.mini-property-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0,0,0,.08), rgba(0,0,0,.78));
  pointer-events: none;
}

.mini-property-card span {
  position: absolute;
  left: 8px;
  right: 8px;
  bottom: 7px;
  z-index: 2;
  color: #fff;
  font-size: 9px;
  line-height: 1.05;
  font-weight: 900;
  text-transform: uppercase;
  text-align: center;
  text-shadow: 0 2px 8px rgba(0,0,0,.9);
  pointer-events: none;
}

.mini-property-card.active {
  border-color: #f4d487;
  box-shadow: 0 0 0 2px rgba(244, 212, 135, .25);
}

.fullscreen-controls {
  position: absolute;
  right: clamp(20px, 7vw, 110px);
  bottom: 38px;
  z-index: 8;
  display: flex;
  align-items: center;
  gap: 10px;
  width: min(420px, 84vw);
}

.fullscreen-controls button {
  width: 34px;
  height: 34px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.25);
  background: rgba(0,0,0,.45);
  color: #fff;
  cursor: pointer;
  line-height: 1;
  font-size: 25px;
  display: grid;
  place-items: center;
  outline: none;
}

.fullscreen-controls button:hover {
  background: rgba(244, 212, 135, .18);
  border-color: rgba(244, 212, 135, .7);
}

.opening-progress {
  flex: 1;
  height: 3px;
  overflow: hidden;
  border-radius: 999px;
  background: rgba(255,255,255,.32);
  margin-left: 12px;
}

.opening-progress i {
  display: block;
  height: 100%;
  width: 100%;
  background: #f4d487;
  transform-origin: left;
  animation: bannerProgress 5s linear forwards;
}

@keyframes bannerProgress {
  from {
    transform: scaleX(0);
  }

  to {
    transform: scaleX(1);
  }
}

.promo-noise,
.promo-light,
.banner-bottom-line {
  pointer-events: none;
}

.promo-noise {
  position: absolute;
  inset: 0;
  z-index: 6;
  opacity: .08;
  background-image:
    radial-gradient(circle at 1px 1px, rgba(255,255,255,.45) 1px, transparent 0);
  background-size: 18px 18px;
  mix-blend-mode: screen;
}

.promo-light {
  position: absolute;
  z-index: 5;
  width: 420px;
  height: 420px;
  border-radius: 999px;
  filter: blur(80px);
  opacity: .22;
}

.promo-light-one {
  left: -120px;
  top: 20%;
  background: rgba(244, 212, 135, .45);
}

.promo-light-two {
  right: -120px;
  bottom: 10%;
  background: rgba(255, 255, 255, .22);
}

.banner-bottom-line {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 1px;
  z-index: 9;
  background: linear-gradient(90deg, transparent, rgba(244, 212, 135, .55), transparent);
}

@media (max-width: 991px) {
  .opening-content {
    padding: 130px 22px 240px;
    justify-content: flex-end;
  }

  .opening-content h1 {
    font-size: clamp(44px, 15vw, 82px);
  }

  .fullscreen-mini-strip {
    left: 18px;
    right: 18px;
    bottom: 92px;
    max-width: none;
  }

  .fullscreen-controls {
    left: 18px;
    right: 18px;
    bottom: 38px;
    width: auto;
  }

  .mini-property-card {
    flex-basis: 126px;
    width: 126px;
    height: 84px;
  }
}

@media (max-width: 575px) {
  .promo-reel {
    min-height: 100svh;
  }

  .opening-content {
    min-height: 100svh;
    padding: 120px 18px 225px;
  }

  .opening-content h1 {
    font-size: clamp(40px, 17vw, 68px);
  }

  .opening-content span {
    font-size: 11px;
  }

  .opening-content p {
    font-size: 15px;
  }

  .mini-property-card {
    flex-basis: 112px;
    width: 112px;
    height: 76px;
  }

  .mini-property-card span {
    font-size: 8px;
  }
}
</style>