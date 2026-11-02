export function assetUrl (value, fallback = '') {
  if (!value) return fallback
  if (/^https?:\/\//i.test(value) || value.startsWith('data:') || value.startsWith('blob:')) return value

  const apiBase = process.env.VUE_APP_API_URL || 'http://localhost:8000/api'
  const origin = apiBase.replace(/\/api\/?$/, '')
  const clean = String(value).replace(/^\/+/, '')

  if (clean.startsWith('storage/')) return `${origin}/${clean}`
  return `${origin}/storage/${clean}`
}
