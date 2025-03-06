/**
 * A small helper script that resizes pictures to 40x40
 * from /public/activities to /public/activities/resize
 */
import path from 'node:path'
import fs from 'node:fs/promises'
import sharp from 'sharp'

const projectRoot = process.cwd()

const inputDir = path.join(projectRoot, 'public', 'activities')
const outputDir = path.join(inputDir, 'resize')

async function clearOutputDir() {
  try {
    await fs.access(outputDir)
    const files = await fs.readdir(outputDir)
    await Promise.all(files.map(file =>
      fs.unlink(path.join(outputDir, file))
    ))
    console.log('✅ Folder resize clear')
  } catch (error) {
    if (error.code === 'ENOENT') {
      await fs.mkdir(outputDir, { recursive: true })
      console.log('📁 Folder resize create')
    } else {
      throw error
    }
  }
}

async function isWebpFile(filePath) {
  try {
    const stats = await fs.stat(filePath)
    return stats.isFile() && filePath.toLowerCase().endsWith('.webp')
  } catch {
    return false
  }
}

async function processImages() {
  try {
    const files = await fs.readdir(inputDir)

    const webpFiles = (await Promise.all(
      files.map(async (file) => {
        const filePath = path.join(inputDir, file)
        return (await isWebpFile(filePath)) ? file : null
      })
    )).filter(Boolean)

    if (!webpFiles.length) {
      console.log('🖼️ WebP files not found')
      return
    }

    console.log(`🔧 Found ${webpFiles.length} files for processing`)

    await Promise.all(webpFiles.map(async (file) => {
      const inputPath = path.join(inputDir, file)
      const outputPath = path.join(outputDir, file)

      await sharp(inputPath)
        .resize(40, 40, {
          fit: 'contain',
          background: { r: 0, g: 0, b: 0, alpha: 0 },
          withoutEnlargement: true
        })
        .toFile(outputPath)

      console.log(`✔️ Processed: ${file}`)
    }))
  } catch (error) {
    console.error('🚨 Processing error:', error)
  }
}

async function main() {
  try {
    console.log('🔄 Starting the process...')
    await clearOutputDir()
    await processImages()
    console.log('🎉 Processing complete!')
  } catch (error) {
    console.error('💥 Critical error:', error)
    process.exit(1)
  }
}

await main()
