<script setup lang="ts">
useHead({
  title: 'Testing Bitrix24 Hooks'
})
</script>

<template>
  <div class="flex flex-col justify-center items-center">
    <B24Advice :avatar="{ src: '/avatar/assistant.png' }">
      <ProseH2>Hello!</ProseH2>
      Let's test the work of Bitrix24 webhooks.<br><br>
      First, edit the <ProseCode>.env</ProseCode> file.<br>
      Then open the desired page and check the work
    </B24Advice>
  </div>
</template>
