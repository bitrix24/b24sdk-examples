import fs from 'node:fs/promises'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { glob } from 'glob'
import consola from 'consola'
import { colors } from 'consola/utils'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)
const OUTPUT_DIR = path.resolve(__dirname, '../.output/public')
const DEV_FOLDER = 'dev-folder'

async function applyBaseURL(content) {
  content = content
    .replace(
      /baseURL:\s*["']\/dev-folder\/["']/g,
      'baseURL: \'/\' + window.location.pathname.split(\'/\').filter(Boolean).slice(0, 3).join(\'/\') + \'/\''
    )

  return content
}

async function replacePaths(content, basePath) {
  const devFolderRegex = new RegExp(
    `(href|src|content|data-[^=]+|from|to|url\\(['"]?)\\s*['"]?\\/?${DEV_FOLDER}\\/`,
    'gi'
  )

  return content
    .replace(devFolderRegex, `$1${basePath}`)
    .replace(new RegExp(`['"]\\/?${DEV_FOLDER}\\/`, 'g'), `"${basePath}`)
    .replace(new RegExp(`\\(\\/?${DEV_FOLDER}\\/`, 'g'), `(${basePath}`)
    .replace(new RegExp(`\\/\\/?${DEV_FOLDER}\\/`, 'g'), basePath)
}

async function processFile(filePath) {
  const relativePath = path.relative(OUTPUT_DIR, filePath)

  try {
    let content = await fs.readFile(filePath, 'utf8')

    const pathSegments = relativePath.split(path.sep)
    const depth = pathSegments.length - 1

    const basePath = depth > 0
      ? Array(depth).fill('..').join('/') + '/'
      : './'

    let newContent = await applyBaseURL(content)
    newContent = await replacePaths(newContent, basePath)

    if (newContent !== content) {
      await fs.writeFile(filePath, newContent, 'utf8')
      consola.log(colors.gray(`- Updated paths in: ${relativePath} (depth: ${depth}, path: ${basePath})`))
      return true
    }
    return false
  } catch (error) {
    consola.error(`Error processing ${relativePath}:`, error.message)
    return false
  }
}

async function main() {
  consola.start('Starting path optimization ...')

  try {
    const files = await glob([
      `${OUTPUT_DIR}/**/*.html`,
      `${OUTPUT_DIR}/**/*.js`,
      `${OUTPUT_DIR}/**/*.css`,
      `${OUTPUT_DIR}/**/*.json`,
      `${OUTPUT_DIR}/**/*.webmanifest`
    ], {
      ignore: [
        `${OUTPUT_DIR}/_nuxt/**/*`,
        `${OUTPUT_DIR}/**/_nuxt/**/*`
      ]
    })

    consola.log('')
    consola.info(`Found ${files.length} files to process (skip _nuxt)`)

    const results = await Promise.allSettled(files.map(processFile))

    const processed = results.filter(r => r.status === 'fulfilled' && r.value).length
    const skipped = results.filter(r => r.status === 'fulfilled' && !r.value).length
    const errors = results.filter(r => r.status === 'rejected').length

    consola.log('')
    consola.info(colors.gray('Processing results:'))
    consola.info(colors.gray(`- successfully processed: ${processed} files`))
    consola.info(colors.gray(`- no changes needed: ${skipped} files`))
    consola.info(colors.gray(`- errors: ${errors} files`))

    consola.log('')
    consola.success(`Path optimization completed!`)
    if (errors > 0) process.exit(1)
  } catch (error) {
    consola.error('Critical error:', error.message)
    process.exit(1)
  }
}

main().catch(error => consola.error(error))
