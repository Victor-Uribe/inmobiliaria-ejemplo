const TOKEN_KEY = 'andarma_token'
const USER_KEY = 'andarma_user'

function clean (value) {
  return String(value || '')
    .trim()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/\s+/g, '-')
    .replace(/_/g, '-')
}

function roleFromId (id) {
  return { 1: 'super-admin', 2: 'admin', 3: 'asesor' }[Number(id)] || null
}

function normalizeRole (user) {
  if (!user) return null
  const candidates = [
    typeof user.role === 'string' ? user.role : null,
    user.role && typeof user.role === 'object' ? user.role.slug : null,
    user.role && typeof user.role === 'object' ? user.role.name : null,
    user.role_slug,
    user.role_name,
    user.roleId,
    user.role_id,
    user.type,
    user.user_type
  ].filter(Boolean)

  for (const candidate of candidates) {
    const value = clean(candidate)
    if (['super-admin', 'superadmin', 'root'].includes(value)) return 'super-admin'
    if (['admin', 'administrador'].includes(value)) return 'admin'
    if (['asesor', 'agent', 'agente'].includes(value)) return 'asesor'
  }

  return roleFromId(user.role_id) || roleFromId(user.roleId) || roleFromId(user.profile_id)
}

function normalizeUser (user) {
  const normalized = { ...(user || {}) }
  const role = normalizeRole(normalized)
  normalized.role = typeof normalized.role === 'object' && normalized.role !== null ? normalized.role : {}
  normalized.role.slug = role || normalized.role.slug || null
  normalized.role.name = normalized.role.name || role || 'Sin rol'
  return normalized
}

export default {
  setSession (token, user) {
    localStorage.setItem(TOKEN_KEY, token)
    localStorage.setItem(USER_KEY, JSON.stringify(normalizeUser(user)))
  },
  getToken () {
    return localStorage.getItem(TOKEN_KEY)
  },
  getUser () {
    const raw = localStorage.getItem(USER_KEY)
    return raw ? normalizeUser(JSON.parse(raw)) : null
  },
  getRole () {
    const user = this.getUser()
    return user && user.role ? user.role.slug : null
  },
  isAuthenticated () {
    return Boolean(this.getToken())
  },
  logout () {
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
  }
}
