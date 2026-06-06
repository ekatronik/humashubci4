import fs from 'fs';
import path from 'path';

const assetsDir = 'c:/xampp/htdocs/humashubci/rilis/release/assets';

function main() {
  const files = fs.readdirSync(assetsDir);
  files.forEach(file => {
    if (file.endsWith('.js')) {
      const filePath = path.join(assetsDir, file);
      const content = fs.readFileSync(filePath, 'utf8');
      
      const containsPathname = content.includes('pathname');
      const containsLocation = content.includes('location');
      const containsBaseUrl = content.includes('/api');
      const containsIndexHtml = content.includes('index.html');
      
      if (containsPathname || containsLocation) {
        console.log(`Match in file: ${file}`);
        // Print character indices or surrounding context
        let idx = content.indexOf('pathname');
        if (idx !== -1) {
          console.log(`  Found 'pathname' at index ${idx}: ...${content.substring(idx - 100, idx + 100)}...`);
        }
        let idx2 = content.indexOf('endsWith("index.html")');
        if (idx2 === -1) idx2 = content.indexOf('endsWith(\"index.html\")');
        if (idx2 === -1) idx2 = content.indexOf('index.html');
        if (idx2 !== -1) {
          console.log(`  Found 'index.html' at index ${idx2}: ...${content.substring(idx2 - 100, idx2 + 100)}...`);
        }
      }
    }
  });
}

main();
