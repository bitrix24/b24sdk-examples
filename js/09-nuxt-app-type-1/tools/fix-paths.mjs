import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';
import { glob } from 'glob';
import consola from 'consola';
import { colors } from "consola/utils";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const OUTPUT_DIR = path.resolve(__dirname, '../.output/public');
const DEV_FOLDER = 'dev-folder';

async function applyCriticalFixes(content) {
  content = content.replace(
    /buildAssetsDir:\s*["']\/_nuxt\/["']/g,
    'buildAssetsDir:"_nuxt/"'
  );

  content = content.replace(
    /baseURL:\s*["']\/dev-folder\/["']/g,
    'baseURL: window.location.pathname.slice(0, window.location.pathname.lastIndexOf(\'/\') + 1)'
  );

  return content;
}

async function replacePaths(content) {
  const relativePrefix = '';
  const devFolderRegex = new RegExp(
    `(href|src|content|data-[^=]+|from|to|url\\(['"]?)\\s*['"]?\\/?${DEV_FOLDER}\\/`,
    'gi'
  );

  return content
    .replace(devFolderRegex, `$1${relativePrefix}`)
    .replace(new RegExp(`['"]\\/?${DEV_FOLDER}\\/`, 'g'), `"${relativePrefix}`)
    .replace(new RegExp(`\\(\\/?${DEV_FOLDER}\\/`, 'g'), `(${relativePrefix}`)
    .replace(new RegExp(`\\/\\/?${DEV_FOLDER}\\/`, 'g'), '/');
}

async function processFile(filePath) {
  const relativePath = path.relative(OUTPUT_DIR, filePath);

  try {
    let content = await fs.readFile(filePath, 'utf8');

    content = await applyCriticalFixes(content);
    const newContent = await replacePaths(content);

    if (newContent !== content) {
      await fs.writeFile(filePath, newContent, 'utf8');
      consola.log(colors.gray(`- Updated paths in: ${relativePath}`));
      return true;
    }
    return false;
  } catch (error) {
    consola.error(`Error processing ${relativePath}:`, error.message);
    return false;
  }
}

async function main() {
  consola.start('Starting path optimization ...');

  try {
    const files = await glob([
      `${OUTPUT_DIR}/**/*.html`,
      `${OUTPUT_DIR}/**/*.js`,
      `${OUTPUT_DIR}/**/*.css`,
      `${OUTPUT_DIR}/**/*.json`,
      `${OUTPUT_DIR}/**/*.webmanifest`
    ]);

    consola.log('');
    consola.info(`Found ${files.length} files to process`);

    const results = await Promise.allSettled(files.map(processFile));

    const processed = results.filter(r => r.status === 'fulfilled' && r.value).length;
    const skipped = results.filter(r => r.status === 'fulfilled' && !r.value).length;
    const errors = results.filter(r => r.status === 'rejected').length;

    consola.log('');
    consola.info(colors.gray('Processing results:'));
    consola.info(colors.gray(`- successfully processed: ${processed} files`));
    consola.info(colors.gray(`- no changes needed: ${skipped} files`));
    consola.info(colors.gray(`- errors: ${errors} files`));

    consola.log('');
    consola.success(`Path optimization completed!`);
    if (errors > 0) process.exit(1);
  } catch (error) {
    consola.error('Critical error:', error.message);
    process.exit(1);
  }
}

main();