<script setup lang="ts">
useHead({
  title: 'Testing Bitrix24 Hooks'
})
</script>

<template>
  <AdviceCenter>
    <B24Advice
      class="w-full max-w-[550px]"
      :b24ui="{ descriptionWrapper: 'w-full' }"
      :avatar="{ src: '/avatar/assistant.png' }"
    >
      <ProseH2>Hello!</ProseH2>
      <ProseP>Let's test the work of Bitrix24 webhooks.</ProseP>
      <ProseP>
        First, edit the <ProseCode color="air-primary">
          .env
        </ProseCode> file.
      </ProseP>
      <ProseP>Then open the desired page and check the work.</ProseP>
    </B24Advice>
  </AdviceCenter>
</template>
