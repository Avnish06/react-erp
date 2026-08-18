const mysql = require('mysql2');
const dotenv = require('dotenv');
const path = require('path');

dotenv.config({ path: path.join(__dirname, '../.env') });

// ─── Configuration ────────────────────────────────────────────────────────────
const DB_CONFIG = {
  host:              process.env.DB_HOST     || 'localhost',
  port:              parseInt(process.env.DB_PORT || '3306'),
  user:              process.env.DB_USER     || 'root',
  password:          process.env.DB_PASSWORD || '',
  database:          process.env.DB_NAME     || 'management_system',
  waitForConnections: true,
  connectionLimit:   10,
  queueLimit:        0,
  connectTimeout:    10000,   // 10s timeout per connection attempt
  acquireTimeout:    10000,
  enableKeepAlive:   true,
  keepAliveInitialDelay: 0,
};

// ─── Create Pool ───────────────────────────────────────────────────────────────
const pool = mysql.createPool(DB_CONFIG);

const COLOVO_DB_CONFIG = {
  ...DB_CONFIG,
  user: process.env.COLOVO_DB_USER || process.env.DB_USER,
  password: process.env.COLOVO_DB_PASSWORD || process.env.DB_PASSWORD,
  database: process.env.COLOVO_DB_NAME || 'colovo'
};
const colovoPool = mysql.createPool(COLOVO_DB_CONFIG);

// ─── Verify Connection on Startup ─────────────────────────────────────────────
function verifyConnection() {
  pool.getConnection((err, connection) => {
    if (err) {
      console.error('❌ MAIN DATABASE FAILED:', err.code, err.message);
    } else {
      console.log('✅ MAIN DATABASE CONNECTED');
      connection.release();
    }
  });

  colovoPool.getConnection((err, connection) => {
    if (err) {
      console.error('❌ COLOVO DATABASE FAILED:', err.code, err.message);
    } else {
      console.log('✅ COLOVO DATABASE CONNECTED');
      connection.release();
    }
  });
}

verifyConnection();

// ─── Pool Error Handler (keeps server alive on lost connections) ───────────────
pool.on('error', (err) => {
  console.error('[DB Pool Error]', err.code, err.message);
  if (err.code === 'PROTOCOL_CONNECTION_LOST' || err.code === 'ECONNREFUSED') {
    console.warn('[DB] Connection lost — pool will auto-reconnect on next query.');
  }
});

// Export both callback-style pool and promise-based pool
module.exports = pool;
module.exports.promise = pool.promise();
module.exports.colovoPool = colovoPool;
module.exports.colovoPromise = colovoPool.promise();
