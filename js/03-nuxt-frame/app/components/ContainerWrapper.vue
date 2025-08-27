<template>
  <div
    :class="[
      'backdrop-blur-sm bg-(--ui-color-design-outline-na-bg)',
      'border-1 border-(--ui-color-design-outline-na-stroke)',
      'rounded-(--ui-border-radius-md)'
    ]"
  >
    <slot />
</div>
</template>
