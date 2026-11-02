import fallbackProperties from '@/data/properties'
import fallbackTestimonials from '@/data/testimonials'
import { assetUrl } from './media'
import { money, slugify } from './formatters'

function monthKey (date = new Date()) {
  const value = new Date(date)
  return `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(2, '0')}`
}

function toMonthKey (value) {
  if (!value) return ''
  return String(value).slice(0, 7)
}

function toPeriodDate (value) {
  if (!value) return ''
  const text = String(value)
  return text.length === 7 ? `${text}-01` : text.slice(0, 10)
}

function toNumber (value, fallback = 0) {
  if (typeof value === 'string') {
    const clean = value.replace(/[^0-9.-]+/g, '')
    const number = Number(clean)
    return Number.isNaN(number) ? fallback : number
  }
  const number = Number(value)
  return Number.isNaN(number) ? fallback : number
}

function rawBuyer (property) {
  return property.buyer || property.comprador || property.property_buyer || {}
}

function normalizeBuyer (property) {
  const buyer = rawBuyer(property)
  return {
    id: buyer.id || property.buyer_id || property.property_buyer_id || property.comprador_id || null,
    number: buyer.number || buyer.buyer_number || property.buyer_number || property.numero_comprador || '',
    name: buyer.name || buyer.full_name || buyer.buyer_name || property.buyer_name || property.comprador_nombre || '',
    phone: buyer.phone || buyer.whatsapp || buyer.buyer_phone || property.buyer_phone || property.comprador_telefono || '',
    email: buyer.email || buyer.buyer_email || property.buyer_email || ''
  }
}

function normalizePayments (property) {
  const payments = property.payments || property.property_payments || property.monthly_payments || property.pagos || []

  return payments.map((payment, index) => {
    const appliedPeriod = toPeriodDate(payment.applied_period || payment.period || payment.month || payment.mes)
    const month = toMonthKey(appliedPeriod || payment.month || payment.mes || payment.paid_at || payment.created_at || new Date())

    return {
      raw: payment,
      id: payment.id || index + 1,
      month,
      applied_period: appliedPeriod,
      applied_due_date: payment.applied_due_date || payment.due_date || payment.fecha_limite || '',
      amount: toNumber(payment.amount || payment.monto || property.monthly_payment_amount || 0),
      payment_type: payment.payment_type || payment.type || 'monthly',
      status: payment.status || payment.estatus || 'applied',
      paid_at: payment.paid_at || payment.payment_date || payment.fecha_pago || payment.created_at || '',
      payment_method: payment.payment_method || payment.metodo_pago || '',
      reference: payment.reference || payment.referencia || '',
      folio: payment.folio || '',
      notes: payment.notes || payment.observaciones || ''
    }
  })
}

function normalizePeriods (property) {
  const periods = property.periods || property.payment_periods || property.months || []

  return periods.map((period) => ({
    period: toPeriodDate(period.period || period.applied_period || period.month),
    period_label: period.period_label || period.label || '',
    due_date: period.due_date || period.applied_due_date || '',
    amount_due: toNumber(period.amount_due || period.monthly_payment_amount || property.monthly_payment_amount || 0),
    amount_paid: toNumber(period.amount_paid || period.paid || 0),
    balance: toNumber(period.balance || 0),
    status: period.status || 'pending',
    is_overdue: Boolean(period.is_overdue || period.overdue)
  })).filter(period => period.period)
}


function buildComputedPeriods (property) {
  const firstPaymentPeriod = toPeriodDate(property.first_payment_period || property.first_payment_date)
  const monthlyPaymentAmount = toNumber(property.monthly_payment_amount || property.monthly_payment || 0)
  const financedAmount = toNumber(property.financed_amount || 0)

  if (!firstPaymentPeriod || monthlyPaymentAmount <= 0 || financedAmount <= 0) return []

  const payments = normalizePayments(property)
  const paidByMonth = payments
    .filter(payment => payment.status !== 'cancelled' && payment.payment_type === 'monthly')
    .reduce((carry, payment) => {
      const key = toMonthKey(payment.applied_period || payment.month)
      carry[key] = (carry[key] || 0) + toNumber(payment.amount)
      return carry
    }, {})

  const totalMonths = Math.ceil(financedAmount / monthlyPaymentAmount)
  const dueDay = Number(property.payment_due_day || property.due_day || 1)
  const today = new Date()
  const start = new Date(`${firstPaymentPeriod.slice(0, 7)}-01T00:00:00`)
  const periods = []

  for (let i = 0; i < totalMonths; i += 1) {
    const periodDate = new Date(start.getFullYear(), start.getMonth() + i, 1)
    const period = monthKey(periodDate)
    const dueDate = new Date(periodDate.getFullYear(), periodDate.getMonth(), Math.min(dueDay, new Date(periodDate.getFullYear(), periodDate.getMonth() + 1, 0).getDate()))
    const remainingBeforeThisMonth = financedAmount - (monthlyPaymentAmount * i)
    const amountDue = Math.min(monthlyPaymentAmount, remainingBeforeThisMonth)
    const amountPaid = paidByMonth[period] || 0
    const balance = Math.max(0, amountDue - amountPaid)
    const status = amountPaid >= amountDue ? 'paid' : amountPaid > 0 ? 'partial' : 'pending'

    periods.push({
      period: `${period}-01`,
      period_label: periodDate.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' }),
      due_date: dueDate.toISOString().slice(0, 10),
      amount_due: amountDue,
      amount_paid: amountPaid,
      balance,
      status,
      is_overdue: balance > 0 && today > dueDate
    })
  }

  return periods
}

function backendSummary (property, payments) {
  const summary = property.summary || property.payment_summary || null
  const status = property.payment_status || null
  const source = summary || (status && typeof status === 'object' ? status : null)
  if (!source) return null

  const overdueMonths = toNumber(source.overdue_months || source.months_late || source.monthsLate || 0)
  const color = source.color || (overdueMonths <= 0 ? 'green' : overdueMonths <= 2 ? 'yellow' : 'red')
  const label = source.label || (overdueMonths <= 0 ? 'Al corriente' : `${overdueMonths} ${overdueMonths === 1 ? 'mes' : 'meses'} de atraso`)

  return {
    ...source,
    monthsLate: overdueMonths,
    overdue_months: overdueMonths,
    color,
    label,
    payments
  }
}

export function paymentSummary (property) {
  const payments = normalizePayments(property.raw || property)
  const summary = backendSummary(property.raw || property, payments) || backendSummary(property, payments)
  if (summary) return summary

  const explicitLate = toNumber(
    property.months_late ||
    property.meses_atraso ||
    (property.payment_status && property.payment_status.months_late) ||
    (property.payment_status && property.payment_status.monthsLate) ||
    0
  )

  let monthsLate = explicitLate
  const monthlyAmount = toNumber(property.monthly_payment_amount || property.monthly_payment || 0)

  if (!monthsLate && monthlyAmount) {
    const paidByMonth = payments
      .filter(payment => payment.status !== 'cancelled' && payment.payment_type === 'monthly')
      .reduce((carry, payment) => {
        const key = payment.month || toMonthKey(payment.applied_period)
        carry[key] = (carry[key] || 0) + toNumber(payment.amount)
        return carry
      }, {})

    const dueDay = Number(property.payment_due_day || property.due_day || 1)
    const firstPeriod = property.first_payment_period || property.first_payment_date || null
    const today = new Date()
    const currentDueDate = new Date(today.getFullYear(), today.getMonth(), dueDay)
    const checkDate = today >= currentDueDate ? today : new Date(today.getFullYear(), today.getMonth() - 1, dueDay)
    const startDate = firstPeriod ? new Date(`${String(firstPeriod).slice(0, 7)}-01T00:00:00`) : new Date(checkDate.getFullYear(), checkDate.getMonth() - 11, 1)
    let cursor = new Date(startDate.getFullYear(), startDate.getMonth(), 1)

    while (cursor <= checkDate) {
      const key = monthKey(cursor)
      if ((paidByMonth[key] || 0) < monthlyAmount) monthsLate += 1
      cursor = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1)
    }
  }

  const color = monthsLate <= 0 ? 'green' : monthsLate <= 2 ? 'yellow' : 'red'
  const label = monthsLate <= 0 ? 'Al corriente' : `${monthsLate} ${monthsLate === 1 ? 'mes' : 'meses'} de atraso`

  const totalPaid = payments
    .filter(payment => payment.status !== 'cancelled')
    .reduce((sum, payment) => sum + toNumber(payment.amount), 0)
  const monthlyPaid = payments
    .filter(payment => payment.status !== 'cancelled' && payment.payment_type === 'monthly')
    .reduce((sum, payment) => sum + toNumber(payment.amount), 0)
  const salePrice = toNumber(property.sale_price || property.price || property.precio || 0)
  const discountAmount = toNumber(property.discount_amount || property.descuento || 0)
  const downPaymentAmount = toNumber(property.down_payment_amount || property.down_payment || property.enganche || 0)
  const financedAmount = toNumber(property.financed_amount || Math.max(0, salePrice - discountAmount - downPaymentAmount))

  return {
    monthsLate,
    overdue_months: monthsLate,
    color,
    label,
    total_paid: totalPaid,
    monthly_paid: monthlyPaid,
    balance: Math.max(0, salePrice - discountAmount - totalPaid),
    financed_balance: Math.max(0, financedAmount - monthlyPaid),
    payments
  }
}

function firstImage (property) {
  const main = property.main_image_url || property.main_image || property.image_url || property.image
  if (main) return main

  const gallery = property.images || property.property_images || property.gallery || []
  const image = gallery[0]
  return image && (image.image_url || image.image_path || image.path || image.url)
}

export function unwrap (response) {
  const data = response && response.data !== undefined ? response.data : response
  return data && data.data !== undefined ? data.data : data
}

export function normalizeProperty (property, index = 0) {
  const fallback = fallbackProperties[index % fallbackProperties.length]
  const buyerRaw = rawBuyer(property)
  const state = property.state || property.estado || 'Estado de Mexico'
  const municipality = property.municipality || property.municipio || property.city || 'Ixtapaluca'
  const rawPrice = property.public_price || property.price || property.precio || fallback.price
  const slug = property.slug || slugify(property.title || fallback.title)
  const buyer = normalizeBuyer(property)
  const salePrice = toNumber(property.sale_price || buyerRaw.sale_price || property.precio_venta || rawPrice)
  const discountAmount = toNumber(property.discount_amount || buyerRaw.discount_amount || property.descuento || 0)
  const downPaymentAmount = toNumber(property.down_payment_amount || buyerRaw.down_payment_amount || property.down_payment || buyerRaw.down_payment || property.enganche || 0)
  const monthlyPaymentAmount = toNumber(property.monthly_payment_amount || buyerRaw.monthly_payment_amount || property.monthly_payment || property.monto_mensual || 0)
  const paymentDueDay = toNumber(property.payment_due_day || buyerRaw.payment_due_day || property.due_day || property.dia_pago || 1)
  const saleDate = property.sale_date || buyerRaw.sale_date || ''
  const firstPaymentPeriod = toPeriodDate(property.first_payment_period || buyerRaw.first_payment_period || property.first_payment_date || buyerRaw.first_payment_date)
  const financedAmount = Math.max(0, salePrice - discountAmount - downPaymentAmount)
  let periods = normalizePeriods({ ...property, monthly_payment_amount: monthlyPaymentAmount })
  if (!periods.length) {
    periods = buildComputedPeriods({
      ...property,
      monthly_payment_amount: monthlyPaymentAmount,
      payment_due_day: paymentDueDay,
      first_payment_period: firstPaymentPeriod,
      financed_amount: financedAmount
    })
  }
  const summary = paymentSummary({ ...property, buyer, monthly_payment_amount: monthlyPaymentAmount, payment_due_day: paymentDueDay, first_payment_period: firstPaymentPeriod })

  return {
    raw: property,
    id: property.id || fallback.id || index + 1,
    user_id: property.user_id || property.advisor_id || property.asesor_id,
    title: property.title || property.titulo || fallback.title,
    slug,
    price: money(rawPrice),
    rawPrice,
    location: `${municipality}, ${state}`,
    state,
    municipality,
    bedrooms: Number(property.bedrooms || property.recamaras || property.rooms || fallback.bedrooms || 0),
    bathrooms: Number(property.bathrooms || property.banos || property.baths || fallback.bathrooms || 0),
    area: Number(property.area || property.construction_area || property.land_area || fallback.area || 0),
    type: property.operation_type || property.type || property.tipo || 'Venta',
    status: property.status || property.estatus || (buyer.id ? 'sold' : 'active'),
    featured: Boolean(property.is_featured || property.featured || property.destacada || fallback.featured),
    description: property.description || property.descripcion || fallback.description,
    image: assetUrl(firstImage(property), fallback.image),
    images: normalizeImages(property, fallback.image),
    contact: property.contact || property.phone || property.telefono || '',
    buyer,
    buyer_id: buyer.id,
    buyer_number: buyer.number,
    buyer_name: buyer.name,
    buyer_phone: buyer.phone,
    buyer_email: buyer.email,
    sale_price: salePrice,
    discount_amount: discountAmount,
    down_payment_amount: downPaymentAmount,
    monthly_payment_amount: monthlyPaymentAmount,
    payment_due_day: paymentDueDay,
    sale_date: saleDate,
    first_payment_period: firstPaymentPeriod,
    buyer_notes: buyerRaw.notes || property.buyer_notes || property.notes || '',
    financed_amount: financedAmount,
    periods,
    next_pending_period: (periods.find(period => period.status !== 'paid') || {}).period || '',
    payment_status: summary,
    payments: summary.payments
  }
}

export function normalizeProperties (items) {
  return (items || []).map((item, index) => normalizeProperty(item, index))
}

export function normalizePropertyPaymentData (property, data = {}) {
  const payload = unwrap(data) || {}
  const raw = {
    ...(property.raw || property),
    buyer: payload.buyer || (property.raw && property.raw.buyer) || property.buyer || {},
    payments: payload.payments || payload.data || (property.raw && property.raw.payments) || property.payments || [],
    summary: payload.summary || payload.payment_summary || (property.raw && property.raw.summary) || property.payment_status || null,
    periods: payload.periods || (property.raw && property.raw.periods) || property.periods || []
  }

  return normalizeProperty(raw)
}

export function normalizeImages (property, fallback) {
  const gallery = property.images || property.property_images || property.gallery || []
  const images = gallery.map((item) => ({
    id: item.id || item.property_image_id || item.url || item.image_url,
    url: assetUrl(item.image_url || item.image_path || item.path || item.url, '')
  })).filter(item => item.url)

  const main = assetUrl(firstImage(property), fallback)
  if (main && !images.some(item => item.url === main)) images.unshift({ id: 'main', url: main })
  return images
}

export function normalizeTestimonial (item, index = 0) {
  const fallback = fallbackTestimonials[index % fallbackTestimonials.length]
  return {
    id: item.id || fallback.id || index + 1,
    name: item.name || item.client_name || item.cliente || fallback.name,
    role: item.role || item.position || 'Cliente Inmobiliaria',
    quote: item.quote || item.comment || item.comentario || fallback.quote,
    photo: assetUrl(item.photo_url || item.photo, '')
  }
}

export function normalizeTestimonials (items) {
  return (items || []).map((item, index) => normalizeTestimonial(item, index))
}

export function normalizeBanner (item, index = 0) {
  const fallback = fallbackProperties[index % fallbackProperties.length]
  return {
    id: item.id || `banner-${index}`,
    image: assetUrl(item.image_url || item.image || item.banner_image, fallback.image),
    alt: item.title || fallback.title,
    kicker: item.kicker || item.subtitle || 'Inmobiliaria · Ixtapaluca',
    title: item.title || fallback.title,
    shortTitle: item.short_title || item.title || fallback.title,
    description: item.description || item.subtitle || fallback.description,
    cta: item.button_text || item.cta || 'Solicitar informacion',
    button_url: item.button_url || item.cta_url || '',
    button_text: item.button_text || item.button_text || ''
  }
}

export function normalizeBanners (items) {
  return (items || []).map((item, index) => normalizeBanner(item, index))
}
