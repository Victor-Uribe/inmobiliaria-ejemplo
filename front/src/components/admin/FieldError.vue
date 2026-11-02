<template>
  <small v-if="message" class="admin-field-error">{{ message }}</small>
</template>

<script>
export default {
  name: 'FieldError',
  props: {
    message: {
      type: String,
      default: ''
    }
  }
}
</script>
