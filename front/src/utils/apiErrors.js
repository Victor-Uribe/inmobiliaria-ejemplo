export const validationErrors = (error) => {
  const responseErrors = error && error.response && error.response.data && error.response.data.errors
  if (!responseErrors || typeof responseErrors !== 'object') return {}

  return Object.keys(responseErrors).reduce((errors, key) => {
    const value = responseErrors[key]
    errors[key] = Array.isArray(value) ? value.join(' ') : String(value)
    return errors
  }, {})
}

export const errorMessage = (error, fallback = 'No se pudo completar la accion.') => {
  const data = error && error.response && error.response.data
  if (data && data.message) return data.message
  return fallback
}
