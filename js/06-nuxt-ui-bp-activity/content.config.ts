import { defineContentConfig, defineCollection, z } from '@nuxt/content'

const schema = z.object({
  title: z.string(),
  description: z.string(),
  category: z.enum(['layout', 'form', 'element', 'navigation', 'data', 'overlay']).optional(),
  label: z.string().optional(),
  icon: z.string().optional()
})

export default defineContentConfig({
  collections: {
    contentActivities: defineCollection({
      source: 'activities/**/*.md',
      type: 'page',
      schema
    })
  }
})
