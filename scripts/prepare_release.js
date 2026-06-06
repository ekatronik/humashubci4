import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const rootDir = path.resolve(__dirname, '..');
const backendDir = path.resolve(rootDir, 'backend');
const tempReleaseDir = path.resolve(rootDir, 'temp_release');
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
  console.log('🚀 Preparing clean release files...');

  // 1. Clean/Create temp_release folder
  if (fs.existsSync(tempReleaseDir)) {
    console.log('🧹 Cleaning old temp_release folder...');
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
        // Exclude specific files
        /\.git/,
        /tests[\\/]Unit/,
        /tests[\\/]Database/
      ]);
    } else {
      console.warn(`⚠️ Warning: ${item} not found in backend directory.`);
    }
  });

  // 3. Clean local dev environment files from writable/ folder
  console.log('🧹 Cleaning writable folder files...');
  const writablePath = path.join(tempReleaseDir, 'writable');
  if (fs.existsSync(writablePath)) {
    cleanDirectoryContents(path.join(writablePath, 'cache'), ['index.html', '.htaccess']);
    cleanDirectoryContents(path.join(writablePath, 'debugbar'), ['index.html', '.htaccess']);
    cleanDirectoryContents(path.join(writablePath, 'logs'), ['index.html', '.htaccess']);
    cleanDirectoryContents(path.join(writablePath, 'session'), ['index.html', '.htaccess']);
    cleanDirectoryContents(path.join(writablePath, 'uploads'), ['index.html', '.htaccess']);
  }

  // 4. Clean local uploads from public/uploads/
  console.log('🧹 Cleaning public/uploads files...');
  const uploadsPath = path.join(tempReleaseDir, 'public', 'uploads');
  if (fs.existsSync(uploadsPath)) {
    // Keep empty directories but remove actual files
    const clippingsPath = path.join(uploadsPath, 'clippings');
    if (fs.existsSync(clippingsPath)) {
      cleanDirectoryContents(clippingsPath);
      // Create a placeholder file to ensure directories are preserved in zip
      fs.writeFileSync(path.join(clippingsPath, '.gitkeep'), '');
    }
    const mediaPath = path.join(uploadsPath, 'media');
    if (fs.existsSync(mediaPath)) {
      cleanDirectoryContents(mediaPath);
      // Create a placeholder file
      fs.writeFileSync(path.join(mediaPath, '.gitkeep'), '');
    }
  }

  // Ensure env is present and .env is NOT present in release
  const envFile = path.join(tempReleaseDir, 'env');
  const dotEnvFile = path.join(tempReleaseDir, '.env');
  if (fs.existsSync(dotEnvFile)) {
    console.log('🧹 Removing .env file from release...');
    fs.unlinkSync(dotEnvFile);
  }

  // Double check if env file is in tempReleaseDir
  if (!fs.existsSync(envFile) && fs.existsSync(path.join(backendDir, 'env'))) {
    fs.copyFileSync(path.join(backendDir, 'env'), envFile);
  }

  console.log('✨ Clean release preparation complete inside temp_release folder!');
}

main();
