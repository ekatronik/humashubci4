import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const rootDir = path.resolve(__dirname, '..');
const releaseDir = path.resolve(rootDir, 'rilis', 'release');

function main() {
  const publicDir = path.join(releaseDir, 'public');

  if (!fs.existsSync(publicDir)) {
    console.log('ℹ️ public folder not found in rilis/release. Reorganization may have already been done.');
    return;
  }

  console.log('🚀 Reorganizing release folder to make it zero-config (root-level)...');

  // 1. Move all files and folders from rilis/release/public/ to rilis/release/
  const items = fs.readdirSync(publicDir);
  items.forEach((item) => {
    const srcPath = path.join(publicDir, item);
    const destPath = path.join(releaseDir, item);

    if (fs.existsSync(destPath)) {
      if (fs.statSync(destPath).isDirectory()) {
        fs.rmSync(destPath, { recursive: true, force: true });
      } else {
        fs.unlinkSync(destPath);
      }
    }
    fs.renameSync(srcPath, destPath);
    console.log(`➡️ Moved ${item} to root.`);
  });

  // Remove the now empty public directory
  fs.rmdirSync(publicDir);
  console.log('🧹 Removed empty public folder.');

  // 2. Modify paths in index.php
  const indexPath = path.join(releaseDir, 'index.php');
  if (fs.existsSync(indexPath)) {
    let indexContent = fs.readFileSync(indexPath, 'utf8');
    indexContent = indexContent.replace(
      "require FCPATH . '../app/Config/Paths.php';",
      "require FCPATH . 'app/Config/Paths.php';"
    );
    fs.writeFileSync(indexPath, indexContent, 'utf8');
    console.log('📝 Adjusted paths in index.php.');
  }

  // 3. Modify paths in reset_pass.php
  const resetPassPath = path.join(releaseDir, 'reset_pass.php');
  if (fs.existsSync(resetPassPath)) {
    let resetContent = fs.readFileSync(resetPassPath, 'utf8');
    resetContent = resetContent.replace(
      "__DIR__ . '/../.env'",
      "__DIR__ . '/.env'"
    );
    fs.writeFileSync(resetPassPath, resetContent, 'utf8');
    console.log('📝 Adjusted paths in reset_pass.php.');
  }

  // 4. Modify paths in install.php
  const installPath = path.join(releaseDir, 'install.php');
  if (fs.existsSync(installPath)) {
    let installContent = fs.readFileSync(installPath, 'utf8');
    
    // Replace path templates from /../ to / in install.php
    installContent = installContent.replace(/__DIR__\s*\.\s*'\/..\//g, "__DIR__ . '/");
    installContent = installContent.replace(/__DIR__\s*\.\s*'\/..'/g, "__DIR__");
    
    // Specifically fix require statement:
    installContent = installContent.replace(
      "require __DIR__ . '/../app/Config/Paths.php';",
      "require __DIR__ . '/app/Config/Paths.php';"
    );
    
    // Specifically fix other occurrences:
    installContent = installContent.replace(
      "__DIR__ . '/../writable'",
      "__DIR__ . '/writable'"
    );
    installContent = installContent.replace(
      "__DIR__ . '/../.env'",
      "__DIR__ . '/.env'"
    );
    installContent = installContent.replace(
      "__DIR__ . '/../env'",
      "__DIR__ . '/env'"
    );
    
    fs.writeFileSync(installPath, installContent, 'utf8');
    console.log('📝 Adjusted paths in install.php.');
  }

  // 5. Modify paths in spark
  const sparkPath = path.join(releaseDir, 'spark');
  if (fs.existsSync(sparkPath)) {
    let sparkContent = fs.readFileSync(sparkPath, 'utf8');
    sparkContent = sparkContent.replace(
      "define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);",
      "define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);"
    );
    sparkContent = sparkContent.replace(
      "require FCPATH . '../app/Config/Paths.php';",
      "require FCPATH . 'app/Config/Paths.php';"
    );
    fs.writeFileSync(sparkPath, sparkContent, 'utf8');
    console.log('📝 Adjusted paths in spark.');
  }

  console.log('✅ Reorganization completed successfully!');
}

main();
