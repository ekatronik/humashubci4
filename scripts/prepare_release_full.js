import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const rootDir = path.resolve(__dirname, '..');
const backendDir = path.resolve(rootDir, 'backend');
const tempReleaseDir = path.resolve(rootDir, 'temp_release_full');
const rilisDir = path.resolve(rootDir, 'rilis');

// Helper to recursively copy directories
function copyRecursiveSync(src, dest, excludeFilesPatterns = []) {
  const exists = fs.existsSync(src);
  const stats = exists && fs.statSync(src);
  const isDirectory = exists && stats.isDirectory();

  if (isDirectory) {
    if (!fs.existsSync(dest)) {
      fs.mkdirSync(dest, { recursive: true });
    }
    fs.readdirSync(src).forEach((childItemName) => {
      copyRecursiveSync(
        path.join(src, childItemName),
        path.join(dest, childItemName),
        excludeFilesPatterns
      );
    });
  } else {
    // Check if the file matches any exclusion pattern
    const isExcluded = excludeFilesPatterns.some((pattern) => {
      if (typeof pattern === 'string') {
        return src.includes(pattern);
      } else if (pattern instanceof RegExp) {
        return pattern.test(src);
      }
      return false;
    });

    if (!isExcluded) {
      const destDir = path.dirname(dest);
      if (!fs.existsSync(destDir)) {
        fs.mkdirSync(destDir, { recursive: true });
      }
      fs.copyFileSync(src, dest);
    }
  }
}

// Helper to clean directory contents but keep the directory and maybe some files
function cleanDirectoryContents(dirPath, keepFiles = []) {
  if (!fs.existsSync(dirPath)) return;
  fs.readdirSync(dirPath).forEach((item) => {
    const itemPath = path.join(dirPath, item);
    if (keepFiles.includes(item)) {
      return;
    }
    if (fs.statSync(itemPath).isDirectory()) {
      fs.rmSync(itemPath, { recursive: true, force: true });
    } else {
      fs.unlinkSync(itemPath);
    }
  });
}

function main() {
  console.log('🚀 Preparing FULL release files (including data and uploads)...');

  // 1. Clean/Create temp_release_full folder
  if (fs.existsSync(tempReleaseDir)) {
    console.log('🧹 Cleaning old temp_release_full folder...');
    fs.rmSync(tempReleaseDir, { recursive: true, force: true });
  }
  fs.mkdirSync(tempReleaseDir, { recursive: true });

  // 2. Define list of items to copy from backend
  const itemsToCopy = [
    'app',
    'system',
    'public',
    'writable',
    'composer.json',
    'env',
    '.env',
    '.htaccess',
    'version.json',
    'LICENSE',
    'README.md',
    'spark'
  ];

  // Copy each item
  itemsToCopy.forEach((item) => {
    const srcPath = path.join(backendDir, item);
    const destPath = path.join(tempReleaseDir, item);

    if (fs.existsSync(srcPath)) {
      console.log(`📁 Copying ${item}...`);
      copyRecursiveSync(srcPath, destPath, [
        // Exclude specific files/folders
        /\.git/,
        /tests[\\/]Unit/,
        /tests[\\/]Database/
      ]);
    } else {
      console.warn(`⚠️ Warning: ${item} not found in backend directory.`);
    }
  });

  // 3. Clean temporary files from writable/ folder (logs, debugbar, cache)
  // But keep the uploads folder inside writable/ if there are any files!
  console.log('🧹 Cleaning temporary files from writable cache/logs/session...');
  const writablePath = path.join(tempReleaseDir, 'writable');
  if (fs.existsSync(writablePath)) {
    cleanDirectoryContents(path.join(writablePath, 'cache'), ['index.html', '.htaccess']);
    cleanDirectoryContents(path.join(writablePath, 'debugbar'), ['index.html', '.htaccess']);
    cleanDirectoryContents(path.join(writablePath, 'logs'), ['index.html', '.htaccess']);
    cleanDirectoryContents(path.join(writablePath, 'session'), ['index.html', '.htaccess']);
    // Note: we do NOT clean writable/uploads/ because it might contain valid files
  }

  // 4. Add the database.sql dump to the root of the release staging folder
  const dbDumpSrc = path.join(rilisDir, 'database.sql');
  const dbDumpDest = path.join(tempReleaseDir, 'database.sql');
  if (fs.existsSync(dbDumpSrc)) {
    console.log('📁 Copying database.sql into release root...');
    fs.copyFileSync(dbDumpSrc, dbDumpDest);
  } else {
    console.error('❌ Error: database.sql not found in rilis/ directory!');
  }

  console.log('✨ Full release preparation complete inside temp_release_full folder!');
}

main();
