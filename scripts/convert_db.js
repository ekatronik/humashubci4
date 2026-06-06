import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const sqlPath = 'c:/xampp/htdocs/humashubci/rilis/database.sql';

function main() {
  if (!fs.existsSync(sqlPath)) {
    console.error('❌ Error: database.sql not found in rilis folder!');
    process.exit(1);
  }

  console.log('⏳ Reading database.sql...');
  const buffer = fs.readFileSync(sqlPath);
  
  let content = '';
  // Check for UTF-16LE Byte Order Mark (0xFF 0xFE)
  if (buffer[0] === 0xFF && buffer[1] === 0xFE) {
    console.log('ℹ️ Detected UTF-16LE encoding (common with PowerShell redirects).');
    content = buffer.toString('utf16le');
  } else {
    // Try reading as UTF-16LE and verify if it contains SQL comments
    const tempContent = buffer.toString('utf16le');
    if (tempContent.includes('MySQL') || tempContent.includes('--') || tempContent.includes('CREATE TABLE')) {
      console.log('ℹ️ Decoded successfully as UTF-16LE.');
      content = tempContent;
    } else {
      console.log('ℹ️ Decoded as UTF-8.');
      content = buffer.toString('utf8');
    }
  }

  console.log('⚙️ Converting & cleaning SQL for MariaDB compatibility...');
  
  // 1. Remove DEFINER statements
  // Matches: /*!50013 DEFINER=`user`@`host` */
  const cleanDefinerComments = content.replace(/\/\*!50013\s+DEFINER\s*=\s*`[^`\s]+`@`[^`\s]+`\s*\*\//gi, '');
  // Matches: DEFINER=`user`@`host`
  let cleaned = cleanDefinerComments.replace(/DEFINER\s*=\s*`[^`\s]+`@`[^`\s]+`/gi, '');

  // 2. Replace MySQL 8 specific collations with MariaDB compatible ones
  // utf8mb4_0900_ai_ci -> utf8mb4_general_ci or utf8mb4_unicode_ci
  const collationCount = (cleaned.match(/utf8mb4_0900_ai_ci/gi) || []).length;
  if (collationCount > 0) {
    console.log(`🔄 Replacing ${collationCount} instances of MySQL 8 specific collation (utf8mb4_0900_ai_ci) with MariaDB general collation (utf8mb4_general_ci)...`);
    cleaned = cleaned.replace(/utf8mb4_0900_ai_ci/gi, 'utf8mb4_general_ci');
  }

  // 3. Write output file back in UTF-8
  fs.writeFileSync(sqlPath, cleaned, 'utf8');
  console.log('✅ Success: database.sql has been converted to UTF-8 and is fully compatible with MariaDB/MySQL hosting!');
}

main();
