#!/usr/bin/env bash
# ==============================================================================
# Production Deployment Script (Ubuntu VPS / CloudPanel)
# Usage:
#   chmod +x scripts/deploy.sh
#   ./scripts/deploy.sh
# ==============================================================================

set -e

echo "=== [1/6] Pulling latest code from Git ==="
git pull origin main || echo "Git pull skipped (local copy or not a git repo)"

echo "=== [2/6] Verifying Node.js environment ==="
node -v
npm -v

echo "=== [3/6] Running Database Backup ==="
node scripts/backup_db.js

echo "=== [4/6] Synchronizing Landing Page Artifacts ==="
node sync_chicken_booster.js

echo "=== [5/6] Executing Automated Test Suite ==="
node test_phase1.js
node test_phase2.js
node test_phase3.js
node test_phase4.js
node test_phase5.js
node test_phase6.js

echo "=== [6/6] Reloading PM2 Cluster ==="
if command -v pm2 &> /dev/null; then
  pm2 reload ecosystem.config.js --env production || pm2 start ecosystem.config.js --env production
  echo "✓ PM2 process reloaded successfully!"
else
  echo "⚠️ PM2 not found globally. Please start via: node server.js or npm start"
fi

echo "=================================================="
echo "🎉 DEPLOYMENT COMPLETED SUCCESSFULLY!"
echo "=================================================="
