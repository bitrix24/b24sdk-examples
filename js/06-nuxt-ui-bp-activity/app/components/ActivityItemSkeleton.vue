<template>
  <div class="relative bg-white dark:bg-white/10 p-sm2 rounded-md flex flex-row gap-sm border-2 transition-shadow shadow border-base-master/10 dark:border-base-100/20"
  >
    <B24Skeleton class="h-12 w-12 rounded-full" />
    <div class="flex-1 flex flex-col items-start justify-between gap-4">
      <div class="w-full flex flex-col items-start justify-between gap-2">
        <B24Skeleton class="h-2 w-1/3" />
        <B24Skeleton class="h-2 w-full" />
        <B24Skeleton class="h-2 w-2/4" />
        <B24Skeleton class="h-2 w-3/4" />
      </div>
      <div class="w-full flex flex-row gap-1 items-center justify-end">
        <div class="border-2 border-gray-200 dark:border-gray-800 h-[30px] w-[100px] px-lg rounded-full flex items-center justify-center">
          <B24Skeleton class="h-xs2 w-full rounded-3xs" />
        </div>
      </div>
    </div>
  </div>
</template>
