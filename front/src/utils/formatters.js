export function money (value) {
  if (typeof value === 'string' && value.includes('$')) return value
  const number = Number(value || 0)
  return number.toLocaleString('es-MX', {
    style: 'currency',
    currency: 'MXN',
    maximumFractionDigits: 0
  })
}

export function slugify (value) {
  return String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')
}

export function propertyWhatsAppMessage (property) {
  return [
    `Hola, me interesa la propiedad: ${property.title}.`,
    `Precio: ${property.price}.`,
    `Ubicacion: ${property.location}.`,
    'Me puedes dar mas informacion?'
  ].join(' ')
}

export function paymentReminderMessage (property) {
  const buyerName = property.buyer_name || (property.buyer && property.buyer.name) || 'cliente'
  const status = property.payment_status || {}
  return [
    `Hola ${buyerName}, te contactamos de Inmobiliaria.`,
    `La propiedad "${property.title}" presenta ${status.label || 'pagos pendientes'}.`,
    'Por favor comunicate con nosotros para regularizar tu pago mensual.'
  ].join(' ')
}
