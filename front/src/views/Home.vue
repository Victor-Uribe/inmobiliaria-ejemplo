<template>
  <main>
    <Navbar :whatsapp="whatsapp" />
    <PromoBanner :whatsapp="whatsapp" :properties="properties" :banners="banners" />
    <FeaturedProperties :properties="properties" :whatsapp="whatsapp" />
    <AboutSection />
    <ServicesSection />
    <TestimonialsSection :testimonials="testimonials" />
    <LocationSection />
    <ContactSection :whatsapp="whatsapp" />
    <FooterSection :whatsapp="whatsapp" />
  </main>
</template>

<script>
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import Navbar from '@/components/Navbar.vue'
import PromoBanner from '@/components/PromoBanner.vue'
import FeaturedProperties from '@/components/FeaturedProperties.vue'
import AboutSection from '@/components/AboutSection.vue'
import ServicesSection from '@/components/ServicesSection.vue'
import TestimonialsSection from '@/components/TestimonialsSection.vue'
import LocationSection from '@/components/LocationSection.vue'
import ContactSection from '@/components/ContactSection.vue'
import FooterSection from '@/components/FooterSection.vue'
import fallbackProperties from '@/data/properties'
import fallbackTestimonials from '@/data/testimonials'
import publicService from '@/services/publicService'
import { normalizeProperties, normalizeTestimonials, normalizeBanners, unwrap } from '@/utils/adapters'

gsap.registerPlugin(ScrollTrigger)

export default {
  name: 'Home',
  components: {
    Navbar,
    PromoBanner,
    FeaturedProperties,
    AboutSection,
    ServicesSection,
    TestimonialsSection,
    LocationSection,
    ContactSection,
    FooterSection
  },
  data () {
    return {
      whatsapp: process.env.VUE_APP_WHATSAPP_PHONE || '525566266152',
      properties: normalizeProperties(fallbackProperties),
      testimonials: normalizeTestimonials(fallbackTestimonials),
      banners: []
    }
  },
  created () {
    this.loadContent()
  },
  mounted () {
    document.title = 'Inmobiliaria | Propiedades en Ixtapaluca, Estado de Mexico'

    gsap.utils.toArray('.reveal-up').forEach((element) => {
      gsap.fromTo(
        element,
        { y: 70, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 1,
          ease: 'power3.out',
          scrollTrigger: {
            trigger: element,
            start: 'top 86%'
          }
        }
      )
    })

    gsap.utils.toArray('.reveal-scale').forEach((element) => {
      gsap.fromTo(
        element,
        { scale: 0.94, opacity: 0 },
        {
          scale: 1,
          opacity: 1,
          duration: 1.1,
          ease: 'power3.out',
          scrollTrigger: {
            trigger: element,
            start: 'top 84%'
          }
        }
      )
    })
  },
  methods: {
    async loadContent () {
      try {
        const response = await publicService.featured()
        const items = unwrap(response)
        this.properties = normalizeProperties(items && items.length ? items : fallbackProperties)
      } catch (error) {
        this.properties = normalizeProperties(fallbackProperties)
      }

      try {
        const response = await publicService.testimonials()
        this.testimonials = normalizeTestimonials(unwrap(response))
      } catch (error) {
        this.testimonials = normalizeTestimonials(fallbackTestimonials)
      }

      try {
        const response = await publicService.banners()
        this.banners = normalizeBanners(unwrap(response))
      } catch (error) {
        this.banners = []
      }
    }
  }
}
</script>
