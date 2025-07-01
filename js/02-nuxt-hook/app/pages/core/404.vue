<script setup lang="ts">
showError({
  statusCode: 404,
  statusMessage: 'CustomError',
  data: {
    homePageIsHide: false
  },
  cause: new Error('Some custom Error'),
  fatal: true
})
</script>

<template>
  <div>
    Some Text
  </div>
</template>
