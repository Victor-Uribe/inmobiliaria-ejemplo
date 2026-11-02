const swal = () => window.Swal

const nativeConfirm = (message) => Promise.resolve(window.confirm(message))

export const successAlert = (title = 'Listo', text = 'La acción se realizo correctamente.') => {
  if (!swal()) return Promise.resolve(window.alert(text))

  return swal().fire({
    icon: 'success',
    title,
    text,
    timer: 1800,
    showConfirmButton: false
  })
}

export const errorAlert = (title = 'Ocurrió un error', text = 'No se pudo completar la acción.') => {
  if (!swal()) return Promise.resolve(window.alert(text))

  return swal().fire({
    icon: 'error',
    title,
    text,
    confirmButtonText: 'Entendido',
    confirmButtonColor: '#d4a960'
  })
}

export const confirmDelete = async ({
  title = '¿Eliminar registro?',
  text = 'Esta accion no se puede deshacer.',
  confirmButtonText = 'Si, eliminar'
} = {}) => {
  if (!swal()) return nativeConfirm(text)

  const result = await swal().fire({
    icon: 'warning',
    title,
    text,
    showCancelButton: true,
    confirmButtonText,
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#64748b',
    reverseButtons: true
  })

  return result.isConfirmed
}

export const confirmAction = async ({
  title = '¿Confirmar accion?',
  text = 'Confirma para continuar.',
  confirmButtonText = 'Confirmar'
} = {}) => {
  if (!swal()) return nativeConfirm(text)

  const result = await swal().fire({
    icon: 'question',
    title,
    text,
    showCancelButton: true,
    confirmButtonText,
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#d4a960',
    cancelButtonColor: '#64748b',
    reverseButtons: true
  })

  return result.isConfirmed
}
