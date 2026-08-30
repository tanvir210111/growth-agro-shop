/**
 * Automated Database Backup Utility
 * 
 * Safely creates a timestamped snapshot of the SQLite database.
 * Usage:
 *   node scripts/backup_db.js
 *   npm run db:backup
 * 
 * Can be configured as a daily cron job in CloudPanel / Ubuntu:
 *   0 2 * * * cd /home/cloudpanel/htdocs/yourdomain.com && npm run db:backup >> /var/log/db_backup.log 2>&1
 */

const fs = require('fs');
const path = require('path');
const { db } = require('../server/db');

const dataDir = path.join(__dirname, '..', 'data');
const backupDir = path.join(dataDir, 'backups');

if (!fs.existsSync(backupDir)) {
  fs.mkdirSync(backupDir, { recursive: true });
}

const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
const backupFilename = `backup_database_${timestamp}.sqlite`;
const backupFilePath = path.join(backupDir, backupFilename);

console.log(`[Backup] Starting backup to: ${backupFilePath}...`);

try {
  // Execute SQLite VACUUM INTO for consistent online backup without locking writes
  const escapedPath = backupFilePath.replace(/'/g, "''");
  db.exec(`VACUUM INTO '${escapedPath}';`);

  const stats = fs.statSync(backupFilePath);
  console.log(`✓ [Backup Success] Saved ${backupFilename} (${(stats.size / 1024).toFixed(2)} KB)`);

  // Rotate old backups (Keep latest 30 backups)
  const allBackups = fs.readdirSync(backupDir)
    .filter(f => f.startsWith('backup_database_') && f.endsWith('.sqlite'))
    .map(f => ({
      name: f,
      path: path.join(backupDir, f),
      time: fs.statSync(path.join(backupDir, f)).mtime.getTime()
    }))
    .sort((a, b) => b.time - a.time);

  if (allBackups.length > 30) {
    const toDelete = allBackups.slice(30);
    toDelete.forEach(oldFile => {
      fs.unlinkSync(oldFile.path);
      console.log(`[Backup Prune] Removed old backup: ${oldFile.name}`);
    });
  }

} catch (err) {
  console.error('[Backup Failed]:', err.message);
  process.exit(1);
}
