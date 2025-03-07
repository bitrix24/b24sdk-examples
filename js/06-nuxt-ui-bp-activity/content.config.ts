import { defineContentConfig, defineCollection, z } from '@nuxt/content'
import { EActivityCategory } from './app/types'

const schema = z.object({
  title: z.string(),
  description: z.string(),
  categories: z.array(z.nativeEnum(EActivityCategory)).optional(),
  badges: z.array(z.string()).optional(),
  avatar: z.string().optional()
})

export default defineContentConfig({
  collections: {
    contentActivities_en: defineCollection({
      source: 'activities/en/**/*.md',
      type: 'page',
      schema
    }),
    contentActivities_de: defineCollection({
      source: 'activities/de/**/*.md',
      type: 'page',
      schema
    }),
    contentActivities_ru: defineCollection({
      source: 'activities/ru/**/*.md',
      type: 'page',
      schema
    }),
    contentActivities_ar: defineCollection({
      source: 'activities/ar/**/*.md',
      type: 'page',
      schema
    })
  }
})
