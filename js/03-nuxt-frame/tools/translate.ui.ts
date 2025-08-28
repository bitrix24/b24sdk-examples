/**
 * Translate i18n/locales
 * @see https://platform.deepseek.com/usage
 */
import fs from 'node:fs/promises'
import path from 'node:path'
import { createInterface } from 'node:readline/promises'
import OpenAI from 'openai'
import { config } from 'dotenv'
import { contentLocales } from '../i18n/i18n.map'

config({ path: '.env' })

const rl = createInterface({
  input: process.stdin,
  output: process.stdout
})

const EXCLUDED_WORDS = ['AI', 'CRM', 'Bitrix24']
const CONTENT_PATH = './i18n/locales'

const openai = new OpenAI({
  baseURL: 'https://api.deepseek.com',
  apiKey: process.env.DEEPSEEK_API_KEY
})

async function translateText(
  text: string,
  sourceLang: string,
  targetLang: string
) {
  try {
    const completion = await openai.chat.completions.create({
      model: 'deepseek-chat',
      messages: [{
        role: 'user',
        content: `Translate the following JSON value from ${sourceLang} to ${targetLang}.
                  Keep all placeholders like {0}, {name} intact.
                  Don't translate: ${EXCLUDED_WORDS.join(', ')}.
                  Never add explanations.
                  Return only the translation without any additional text or quotes.
                  Text: ${JSON.stringify(text)}`
      }],
      temperature: 1.3
    })

    if (null === completion) {
      return ''
    }
    return (((completion.choices[0] || {})?.message || {}).content || '')
      .replaceAll('```json', '')
      .replaceAll('```', '')
      .trim()
  } catch (error) {
    console.error('Translation error:', (error instanceof Error) ? error?.message : error)
    return text
  }
}

async function main() {
  try {
    // Getting a list of languages
    const files = await fs.readdir(CONTENT_PATH)
    const locales = files
      .filter(f => f.endsWith('.json'))
      .map(f => f.replace('.json', ''))

    if (locales.length < 2) {
      throw new Error('Need at least 2 locale files')
    }

    // Select source language
    console.log('Available locales:', locales.join(', '))
    const sourceLang = await rl.question('Enter source locale: ')
    if (!locales.includes(sourceLang)) {
      throw new Error(`Main locale ${sourceLang} not found`)
    }

    // Loading the main language
    const mainPath = path.join(CONTENT_PATH, `${sourceLang}.json`)
    const mainData = JSON.parse(await fs.readFile(mainPath, 'utf-8'))

    const localeInfo: Record<string, {
      code: string
      name: string
      file: string
    }> = {}
    contentLocales.forEach((row) => {
      localeInfo[row.code] = row
    })

    // Processing other languages
    for (const locale of locales.filter(l => l !== sourceLang)) {
      const localePath = path.join(CONTENT_PATH, `${locale}.json`)

      console.log(`Translating [${localeInfo[sourceLang]?.name || '??'} (${localeInfo[sourceLang]?.code || sourceLang})] to [${localeInfo[locale]?.name || '??'} (${localeInfo[locale]?.code || locale})] ...`)

      // Translation and update
      const translated = await translateText(
        mainData,
        `${localeInfo[sourceLang]?.name || '??'} (${localeInfo[sourceLang]?.code || sourceLang})`,
        `${localeInfo[locale]?.name || '??'} (${localeInfo[locale]?.code || locale})`
      )
      const saveData = JSON.parse(translated)

      // Saving the updated file
      await fs.writeFile(localePath, JSON.stringify(saveData, null, 2) + '\n')
      console.log(`✅ Successfully updated ${locale}.json\n`)
    }
  } catch (error) {
    console.error('Error:', (error instanceof Error) ? error?.message : error)
  } finally {
    rl.close()
  }
}

main()
