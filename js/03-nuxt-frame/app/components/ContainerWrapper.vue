<template>
  <div
    :class="[
      'backdrop-blur-sm bg-(--ui-color-design-outline-na-bg)',
      'border-1 border-(--ui-color-design-outline-na-stroke)',
      'rounded-[20px]',
      'px-[22px] py-[5px]'
    ]"
  >
    <slot />
</div>
</template>
