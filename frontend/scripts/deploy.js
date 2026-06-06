import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const frontendDist = path.resolve(__dirname, '../dist');
const backendPublic = path.resolve(__dirname, '../../backend/public');

function copyFolderRecursiveSync(source, target) {
  let files = [];

  // Check if folder needs to be created or clean
  const targetFolder = path.join(target, path.basename(source));
  if (!fs.existsSync(targetFolder)) {
    fs.mkdirSync(targetFolder, { recursive: true });
  } else {
    // Empty directory content first
    const items = fs.readdirSync(targetFolder);
    for (const item of items) {
      fs.rmSync(path.join(targetFolder, item), { recursive: true, force: true });
    }
  }

  // Copy
  if (fs.lstatSync(source).isDirectory()) {
    files = fs.readdirSync(source);
    files.forEach(function (file) {
      const curSource = path.join(source, file);
      if (fs.lstatSync(curSource).isDirectory()) {
        copyFolderRecursiveSync(curSource, targetFolder);
      } else {
        fs.copyFileSync(curSource, path.join(targetFolder, file));
      }
    });
  }
}

function deploy() {
  console.log('🚀 Starting deployment script...');

  if (!fs.existsSync(frontendDist)) {
    console.error('❌ Frontend dist folder not found. Please run "npm run build" first.');
    process.exit(1);
  }

  // 1. Copy assets folder
  const srcAssets = path.join(frontendDist, 'assets');
  if (fs.existsSync(srcAssets)) {
    console.log('📁 Copying assets...');
    copyFolderRecursiveSync(srcAssets, backendPublic);
    console.log('✅ Assets copied.');
  }

  // 2. Copy index.html
  const srcIndexHtml = path.join(frontendDist, 'index.html');
  const destIndexHtml = path.join(backendPublic, 'index.html');
  if (fs.existsSync(srcIndexHtml)) {
    console.log('📄 Copying index.html...');
    fs.copyFileSync(srcIndexHtml, destIndexHtml);
    console.log('✅ index.html copied.');
  }

  console.log('🎉 Deployment sync completed successfully!');
}

deploy();
