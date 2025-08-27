<template>
  <div class="flex flex-col items-center justify-center gap-16 h-[calc(100vh-60px)]">
    <div
      :class="[
        'edge-dark',
        'backdrop-blur-sm bg-(--ui-color-design-outline-na-bg)',
        'border-1 border-(--ui-color-design-outline-na-stroke)',
        'text-(--ui-color-design-outline-na-content)',
        'max-w-[550px]',
        'my-[20px] mx-(--content-area-shift)',
        'px-[60px] py-[40px]',
        'rounded-[24px]',
        'flex flex-col items-center justify-center gap-[10px]'
      ]"
    >
      <slot />
    </div>
  </div>
</template>
