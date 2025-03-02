import { defineContentConfig, defineCollection, z } from '@nuxt/content'
import { EActivityCategory } from './app/types'

const schema = z.object({
  title: z.string(),
  description: z.string(),
  category: z.array(z.nativeEnum(EActivityCategory)).optional(),
  badges: z.array(z.string()).optional(),
  avatar: z.string().optional()
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
