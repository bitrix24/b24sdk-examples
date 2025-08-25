<template>
  <div
    :class="[
      'w-full ',
      'backdrop-blur-sm bg-(--ui-color-design-outline-na-bg)',
      'border-1 border-(--ui-color-design-outline-na-stroke)',
      'text-(--ui-color-design-outline-na-content)',
      'max-w-[550px]',
      'px-[15px] py-[10px]',
      'rounded-(--ui-border-radius-md)',
      'flex flex-col items-start justify-between gap-[10px]'
    ]"
  >
    <slot />
  </div>
</template>
