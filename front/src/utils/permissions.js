import authStore from '@/store/authStore'

export const MANAGER_ROLES = ['super-admin', 'admin']
export const PROPERTY_ROLES = ['super-admin', 'admin', 'asesor']

export function currentRole () {
  return authStore.getRole()
}

export function canAccess (roles = []) {
  if (!roles.length) return true
  return roles.includes(currentRole())
}

export function canManageSystem () {
  return MANAGER_ROLES.includes(currentRole())
}

export function isAdvisor () {
  return currentRole() === 'asesor'
}
