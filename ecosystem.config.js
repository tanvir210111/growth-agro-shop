/**
 * PM2 Ecosystem Configuration for Production VPS / CloudPanel
 * 
 * Usage:
 *   pm2 start ecosystem.config.js --env production
 *   pm2 reload ecosystem.config.js
 *   pm2 status
 *   pm2 logs chicken-booster
 */

module.exports = {
  apps: [
    {
      name: 'chicken-booster',
      script: 'server.js',
      instances: 1, // Single instance for SQLite database integrity
      exec_mode: 'fork',
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      env: {
        NODE_ENV: 'development',
        PORT: 3000
      },
      env_production: {
        NODE_ENV: 'production',
        PORT: 3000
      },
      error_file: './logs/pm2-err.log',
      out_file: './logs/pm2-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      merge_logs: true,
      time: true
    }
  ]
};
