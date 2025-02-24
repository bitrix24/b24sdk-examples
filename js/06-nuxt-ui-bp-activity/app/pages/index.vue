<script setup lang="ts">
definePageMeta({
	layout: 'clear',
	title: 'bpActivity'
})
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-16 h-screen">
    <h1 class="font-b24-secondary text-h1 sm:text-8xl font-light">
      Bitrix24 UI - bpActivity
    </h1>
    
    <div class="flex flex-wrap items-center gap-2">
	    <B24Button
		    rounded
		    label="Activity list"
		    color="primary"
		    to="/activity-list"
	    />
    </div>
  </div>
</template>
