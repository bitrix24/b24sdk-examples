import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import archiver from 'archiver';
import consola from 'consola';
import { colors } from "consola/utils";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const SOURCE_DIR = path.join(__dirname, '../.output/public');
const OUTPUT_ZIP = path.join(__dirname, '../.output/archiverForB24.zip');

const formatBytes = (bytes, decimals = 2) => {
  if (bytes === 0) return '0 Bytes';

  const k = 1024;
  const sizes = ['Bytes', 'kB', 'Mb', 'Gb'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i];
};

// Checking for folder existence
if (!fs.existsSync(SOURCE_DIR)) {
  consola.error(`Error: Directory ${SOURCE_DIR} not found!`);
  process.exit(1);
}

const output = fs.createWriteStream(OUTPUT_ZIP);
const archive = archiver('zip', { zlib: { level: 9 } });

output.on('close', () => {
  const fileSize = archive.pointer();
  consola.success(`Archive created: ${colors.gray(OUTPUT_ZIP)} ${colors.gray('['+formatBytes(fileSize)+']')}`);
});

archive.on('warning', (err) => {
  if (err.code === 'ENOENT') {
    consola.warn('Archive warning:', err.message);
  } else {
    throw err;
  }
});

archive.on('error', (err) => {
  consola.error('Archive error:', err.message);
  process.exit(1);
});

archive.pipe(output);
archive.directory(SOURCE_DIR, false);
try {
  await archive.finalize();
} catch (err) {
  consola.error('Failed to finalize archive:', err.message);
  process.exit(1);
}