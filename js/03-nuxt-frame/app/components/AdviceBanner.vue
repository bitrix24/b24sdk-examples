<template>
  <div
    :class="[
      'w-full edge-dark',
      'lg:max-w-[550px]',
      'lg:fixed lg:z-10 lg:top-[25px] lg:right-[25px] lg:mx-(--content-area-shift) ',
      'lg:backdrop-blur-sm lg:bg-(--ui-color-design-outline-na-bg)',
      'lg:border-1 lg:border-(--ui-color-design-outline-na-stroke)',
      'lg:my-[20px]',
      'lg:px-[10px] lg:py-[20px] pb-[24px]',
      'lg:rounded-[24px]',
      'flex flex-col items-start lg:items-center justify-center gap-[10px]'
    ]"
  >
    <slot />
  </div>
</template>
