/**
 * Used to facilitate the process of pkerevoda
 * - all united
 * - translated
 * - divided
 * node tools/make-process-md.mjs join content/activities/en result-join.md
 * node tools/make-process-md.mjs split result-join.md content/activities/de
 */

import fs from 'node:fs'
import path from 'node:path'

const SEPARATOR = '************'

function escapeRegExp(string) {
  return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

function clearDirectory(dirPath) {
  if (!fs.existsSync(dirPath)) return

  const files = fs.readdirSync(dirPath)

  for (const file of files) {
    const filePath = path.join(dirPath, file)
    const stats = fs.statSync(filePath)

    if (stats.isDirectory()) {
      continue
    }

    try {
      fs.unlinkSync(filePath)
      console.log(`Deleted old file: ${file}`)
    } catch (error) {
      console.error(`Error deleting file ${filePath}:`, error.message)
    }
  }
}

async function joinFiles(inputDir, outputFile = 'result-join.md') {
  try {
    const files = fs.readdirSync(inputDir)
      .filter(file => file.endsWith('.md'))
      .map(file => path.join(inputDir, file))

    if (files.length === 0) {
      console.error('No markdown files found in directory')
      return
    }

    const parts = []
    for (const filePath of files) {
      const content = fs.readFileSync(filePath, 'utf8')
      const fileName = path.basename(filePath)
      parts.push(`FILE:${fileName}\n${content}`)
    }

    const outputContent = parts.join(`\n${SEPARATOR}\n`)
    fs.writeFileSync(outputFile, outputContent)
    console.log(`Successfully joined files to ${outputFile}`)
  } catch (error) {
    console.error('Error joining files:', error.message)
  }
}

async function splitFile(inputFile, outputDir) {
  try {
    if (!fs.existsSync(outputDir)) {
      fs.mkdirSync(outputDir, { recursive: true })
    } else {
      clearDirectory(outputDir)
    }

    const content = fs.readFileSync(inputFile, 'utf8')
    const separatorRegex = new RegExp(`\\n${escapeRegExp(SEPARATOR)}\\n`)
    const parts = content.split(separatorRegex)

    for (const part of parts) {
      const match = part.match(/^FILE:(.+\.md)$/m)
      if (!match) {
        console.error('Skipping invalid part without FILE header')
        continue
      }

      const fileName = match[1]
      const fileContent = part
        .replace(/^FILE:.+\.md$/m, '')
        .trim()

      const outputPath = path.join(outputDir, fileName)
      fs.writeFileSync(outputPath, fileContent)
      console.log(`Created new file: ${fileName}`)
    }
  } catch (error) {
    console.error('Error splitting file:', error.message)
  }
}

const args = process.argv.slice(2)

if (args[0] === 'join' && args[1]) {
  joinFiles(args[1], args[2])
} else if (args[0] === 'split' && args[1] && args[2]) {
  splitFile(args[1], args[2])
} else {
  console.log([
    'Usage:',
    '  Join MD files: node script.mjs join <input_dir> [output_file]',
    '  Split MD file: node script.mjs split <input_file> <output_dir>',
    '',
    'Examples:',
    '  node script.mjs join ./docs',
    '  node script.mjs split ./result-join.md ./split-files'
  ].join('\n'))
}
