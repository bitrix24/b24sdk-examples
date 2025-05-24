import { defineEventHandler, getQuery } from 'h3'
import { useBitrix24 } from '~~/server/composables/useBitrix24'
import { LoggerBrowser, EnumCrmEntityTypeId } from '@bitrix24/b24jssdk'

const $logger = LoggerBrowser.build('api/companies.add', true)

export default defineEventHandler(async (event) => {
  const session = await requireUserSession(event)

  const { $b24 } = useBitrix24(event, session, $logger)

  const { count } = getQuery(event)
  const countAdd = Number.parseInt(count as string) || 1

  const listTitle = generateUniqueNames(countAdd)
  const commands = listTitle.map((title) => {
    return {
      method: 'crm.item.add',
      params: {
        entityTypeId: EnumCrmEntityTypeId.company,
        fields: {
          title: title
        }
      }
    }
  })

  try {
    const response = await $b24.callBatchByChunk(commands)
    const data = response.getData()

    $logger.log(data)
    return listTitle
  } catch (error) {
    $logger.error('Bitrix24 Error:', error)

    throw createError({
      statusCode: 500,
      message: 'Error from Bitrix24'
    })
  }
})

interface NameGeneratorConfig {
  adjectives: string[]
  nouns: string[]
  verbs: string[]
  suffixes: string[]
  locations: string[]
  techTerms: string[]
}

const config: NameGeneratorConfig = {
  adjectives: [
    'Quantum', 'Global', 'Digital', 'Next', 'Alpha', 'Bright', 'Core',
    'Elite', 'Future', 'Infinite', 'Nova', 'Peak', 'Prime', 'Smart'
  ],
  nouns: [
    'Wave', 'Forge', 'Horizon', 'Vortex', 'Pulse', 'Spark', 'Edge',
    'Sphere', 'Node', 'Circuit', 'Matrix', 'Zenith', 'Dynamo', 'Catalyst'
  ],
  verbs: [
    'Build', 'Create', 'Design', 'Develop', 'Engineer', 'Forge',
    'Innovate', 'Launch', 'Pioneer', 'Transform'
  ],
  suffixes: [
    'Technologies', 'Solutions', 'Labs', 'Systems', 'Dynamics',
    'Ventures', 'Collective', 'Industries', 'Group', 'Network'
  ],
  locations: [
    'Silicon', 'Neon', 'Solar', 'Urban', 'Metro', 'Nexus',
    'Terra', 'Orbital', 'Hyper', 'Cyber'
  ],
  techTerms: [
    'AI', 'Cloud', 'Blockchain', 'IoT', 'VR', 'AR', 'ML',
    'Data', 'Crypto', 'Nano', 'Bio', 'Neuro'
  ]
}

const generateCompanyName = (): string => {
  const patterns = [
    // Pattern 1: [Adjective] [Noun] [Suffix]
    () => `${randomItem(config.adjectives)} ${randomItem(config.nouns)} ${randomItem(config.suffixes)}`,

    // Pattern 2: [Location][TechTerm] [Suffix]
    () => `${randomItem(config.locations)}${randomItem(config.techTerms)} ${randomItem(config.suffixes)}`,

    // Pattern 3: [Verb] [Noun]
    () => `${randomItem(config.verbs)} ${randomItem(config.nouns)}`,

    // Pattern 4: [Adjective][TechTerm]
    () => `${randomItem(config.adjectives)}${randomItem(config.techTerms)}`,

    // Pattern 5: [Location] [Noun] Labs
    () => `${randomItem(config.locations)} ${randomItem(config.nouns)} Labs`,

    // Pattern 6: [TechTerm] + [Noun]
    () => `${randomItem(config.techTerms)}${randomItem(config.nouns)}`
  ]

  return randomItem(patterns)()
}

const randomItem = <T>(arr: T[]): T => arr[Math.floor(Math.random() * arr.length)]

const generateUniqueNames = (count: number): string[] => {
  const names = new Set<string>()
  while (names.size < count) {
    names.add(generateCompanyName())
  }
  return Array.from(names)
}
