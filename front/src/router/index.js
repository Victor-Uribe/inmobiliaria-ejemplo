import Vue from 'vue'
import VueRouter from 'vue-router'
import authStore from '@/store/authStore'
import { canAccess, MANAGER_ROLES, PROPERTY_ROLES } from '@/utils/permissions'

import Home from '@/views/Home.vue'
import Properties from '@/views/Properties.vue'
import PropertyDetail from '@/views/PropertyDetail.vue'
import Login from '@/views/admin/Login.vue'
import AdminLayout from '@/views/admin/AdminLayout.vue'
import Dashboard from '@/views/admin/Dashboard.vue'
import AdminProperties from '@/views/admin/Properties.vue'
import Leads from '@/views/admin/Leads.vue'
import Users from '@/views/admin/Users.vue'
import Testimonials from '@/views/admin/Testimonials.vue'
import Banners from '@/views/admin/Banners.vue'

Vue.use(VueRouter)

const router = new VueRouter({
  mode: 'hash',
  routes: [
    { path: '/', name: 'home', component: Home },
    { path: '/propiedades', name: 'properties', component: Properties },
    { path: '/propiedades/:slug', name: 'property-detail', component: PropertyDetail },
    { path: '/admin/login', name: 'admin-login', component: Login },
    {
      path: '/admin',
      component: AdminLayout,
      meta: { requiresAuth: true },
      children: [
        { path: '', name: 'admin-dashboard', component: Dashboard, meta: { roles: PROPERTY_ROLES } },
        { path: 'propiedades', name: 'admin-properties', component: AdminProperties, meta: { roles: PROPERTY_ROLES } },
        { path: 'leads', name: 'admin-leads', component: Leads, meta: { roles: MANAGER_ROLES } },
        { path: 'usuarios', name: 'admin-users', component: Users, meta: { roles: MANAGER_ROLES } },
        { path: 'testimonios', name: 'admin-testimonials', component: Testimonials, meta: { roles: MANAGER_ROLES } },
        { path: 'banners', name: 'admin-banners', component: Banners, meta: { roles: MANAGER_ROLES } }
      ]
    }
  ],
  scrollBehavior (to) {
    if (to.hash) return { selector: to.hash, behavior: 'smooth' }
    return { x: 0, y: 0 }
  }
})

router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(route => route.meta.requiresAuth)
  if (requiresAuth && !authStore.isAuthenticated()) return next({ name: 'admin-login' })

  const restricted = [...to.matched].reverse().find(route => route.meta && route.meta.roles)
  if (restricted && !canAccess(restricted.meta.roles)) return next({ name: 'admin-dashboard' })

  if (to.name === 'admin-login' && authStore.isAuthenticated()) return next({ name: 'admin-dashboard' })
  next()
})

export default router
