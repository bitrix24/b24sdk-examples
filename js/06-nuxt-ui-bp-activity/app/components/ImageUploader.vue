<script setup lang="ts">
import { ref } from 'vue'

const props = withDefaults(defineProps<{
  maxSize?: number
  quality?: number
}>(), {
  maxSize: 800,
  quality: 0.8
})

const emit = defineEmits<{
  imageUpload: [string]
  imageError: [string]
}>()

const previewImage = ref<string | null>(null)

const handleImageUpload = async (event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]

  if (!file) return

  try {
    const processedImage = await processImage(file)

    if (processedImage) {
      emit('imageUpload', processedImage)
      // localStorage.setItem('webpImage', processedImage)
      previewImage.value = processedImage
    }
  } catch (error) {
    emit('imageError', 'Error processing image')
    console.error('Error processing image:', error)
  }
}

const processImage = (file: File): Promise<string | null> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()

    reader.onload = async (e) => {
      const img = new Image()
      img.src = e.target?.result as string

      img.onload = () => {
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d')
        if (!ctx) return reject('Canvas context not found')

        // Calculate new dimensions
        let width = img.width
        let height = img.height

        if (width > height && width > props.maxSize) {
          height = height * (props.maxSize / width)
          width = props.maxSize
        } else if (height > props.maxSize) {
          width = width * (props.maxSize / height)
          height = props.maxSize
        }

        // Set canvas dimensions
        canvas.width = width
        canvas.height = height

        // Draw and compress image
        ctx.drawImage(img, 0, 0, width, height)

        // Convert to WebP with quality
        canvas.toBlob(
          (blob) => {
            if (!blob) return reject('Conversion failed')
            const reader = new FileReader()
            reader.onloadend = () => resolve(reader.result as string)
            reader.readAsDataURL(blob)
          },
          'image/webp',
          props.quality
        )
      }

      img.onerror = reject
    }
    reader.readAsDataURL(file)
  })
}
</script>

<template>
  <div>
    <B24Input
      type="file"
      accept="image/*"
      @change="handleImageUpload"
    />
    <div v-if="previewImage">
      <h3>Preview:</h3>
      <img :src="previewImage" alt="Resized image" />
    </div>
  </div>
</template>
