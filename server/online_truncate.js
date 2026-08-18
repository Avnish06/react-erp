const mysql = require('mysql2/promise');
const bcrypt = require('bcryptjs');
const path = require('path');

// Helper to hash password
async function hashPassword(plainPassword) {
  const salt = await bcrypt.genSalt(10);
  return await bcrypt.hash(plainPassword, salt);
}

// Laravel-specific bcrypt hash for 'password'
const LARAVEL_ADMIN_HASH = '$2y$10$Q5Bd9lBdzKXVDM4l3s.FgeIuXlNWOHKoPL.dqELVGVI09sk3/qeJG'; // password

async function runOnlineCleanup() {
  console.log('==================================================');
  console.log('  ONLINE HOSTINGER DATABASE RESET STARTED');
  console.log('==================================================');

  // Load Hostinger production credentials from the parent .env file
  require('dotenv').config({ path: path.join(__dirname, '../.env') });

  const DB_HOST = process.env.DB_HOST || '127.0.0.1';
  const DB_USER = process.env.DB_USER || 'u372812109_Hatbaliya_123';
  const DB_PASSWORD = process.env.DB_PASSWORD || 'Hatbaliya@123';
  const DB_NAME = process.env.DB_NAME || 'u372812109_erp_crm_react';

  const COLOVO_DB_USER = process.env.COLOVO_DB_USER || 'u372812109_workspace_DB';
  const COLOVO_DB_PASSWORD = process.env.COLOVO_DB_PASSWORD || 'Workspace@125';
  const COLOVO_DB_NAME = process.env.COLOVO_DB_NAME || 'u372812109_workspace_DB';

  console.log(`Connecting to ERP DB: ${DB_NAME} on ${DB_HOST}...`);
  const erpConn = await mysql.createConnection({
    host: DB_HOST,
    user: DB_USER,
    password: DB_PASSWORD,
    database: DB_NAME
  });

  console.log(`Connecting to Colovo DB: ${COLOVO_DB_NAME} on ${DB_HOST}...`);
  const colovoConn = await mysql.createConnection({
    host: DB_HOST,
    user: COLOVO_DB_USER,
    password: COLOVO_DB_PASSWORD,
    database: COLOVO_DB_NAME
  });

  try {
    // ----------------------------------------------------
    // CLEANING ERP DATABASE (management_system)
    // ----------------------------------------------------
    console.log('\n[1/2] Cleaning Online ERP Database...');
    await erpConn.query('SET FOREIGN_KEY_CHECKS = 0');

    const [erpTables] = await erpConn.query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    for (const tableObj of erpTables) {
      const tableName = Object.values(tableObj)[0];
      if (!['roles', 'permissions', 'role_permissions', 'system_settings'].includes(tableName)) {
        console.log(`  - Truncating ${tableName}...`);
        await erpConn.query(`TRUNCATE TABLE \`${tableName}\``);
      }
    }
    await erpConn.query('SET FOREIGN_KEY_CHECKS = 1');
    console.log('  ✅ Online ERP tables cleared.');

    // ----------------------------------------------------
    // CLEANING COLOVO DATABASE (colovo)
    // ----------------------------------------------------
    console.log('\n[2/2] Cleaning Online Colovo Workspace Database...');
    await colovoConn.query('SET FOREIGN_KEY_CHECKS = 0');

    const [colovoTables] = await colovoConn.query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    for (const tableObj of colovoTables) {
      const tableName = Object.values(tableObj)[0];
      if (!['migrations'].includes(tableName)) {
        console.log(`  - Truncating ${tableName}...`);
        await colovoConn.query(`TRUNCATE TABLE \`${tableName}\``);
      }
    }
    await colovoConn.query('SET FOREIGN_KEY_CHECKS = 1');
    console.log('  ✅ Online Colovo tables cleared.');

    // ----------------------------------------------------
    // SEEDING DEFAULT RECORDS
    // ----------------------------------------------------
    console.log('\n[3/3] Seeding fresh Admin & Company records...');

    const erpCompanyName = 'Hatbaliya Technologies';
    await erpConn.query(
      'INSERT INTO companies (id, name, email, phone, address, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
      [1, erpCompanyName, 'contact@hatbaliya.com', '1234567890', 'Corporate Office']
    );
    await colovoConn.query(
      'INSERT INTO companies (id, name, email, phone, address, created_at, updated_at, status) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?)',
      [1, erpCompanyName, 'contact@hatbaliya.com', '1234567890', 'Corporate Office', 'active']
    );
    console.log('  - Seeded default Company (Hatbaliya Technologies, ID: 1).');

    await erpConn.query(
      'INSERT INTO departments (id, name, company_name) VALUES (?, ?, ?)',
      [1, 'Management', erpCompanyName]
    );
    console.log('  - Seeded default Department (Management, ID: 1).');

    const superAdminPass = await hashPassword('superadmin123');
    const adminPass = await hashPassword('admin123');

    const [saUser] = await erpConn.query(
      'INSERT INTO user_identities (email, password, role_id) VALUES (?, ?, ?)',
      ['superadmin@example.com', superAdminPass, 1]
    );
    await erpConn.query(
      'INSERT INTO superadmins (user_id, name, employee_id, status, company_name) VALUES (?, ?, ?, ?, ?)',
      [saUser.insertId, 'Super Admin', 'SA-001', 'Active', erpCompanyName]
    );
    console.log('  - Seeded Super Admin (superadmin@example.com / superadmin123).');

    const [adUser] = await erpConn.query(
      'INSERT INTO user_identities (email, password, role_id) VALUES (?, ?, ?)',
      ['admin@example.com', adminPass, 2]
    );
    await erpConn.query(
      'INSERT INTO admins (user_id, name, employee_id, status, company_name) VALUES (?, ?, ?, ?, ?)',
      [adUser.insertId, 'Admin User', 'AD-001', 'Active', erpCompanyName]
    );
    console.log('  - Seeded Admin (admin@example.com / admin123).');

    await colovoConn.query(
      'INSERT INTO users (id, name, email, password, role, department, position, salary, company_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
      [1, 'ESP Admin', 'admin@colovo.com', LARAVEL_ADMIN_HASH, 'admin', 'Management', 'HR Director', 120000.00, 1]
    );
    console.log('  - Seeded Colovo Admin (admin@colovo.com / password).');

    console.log('\n==================================================');
    console.log('  ✅ ONLINE DATABASE RESET COMPLETE!');
    console.log('==================================================');

  } catch (err) {
    console.error('❌ Error executing online cleanup:', err);
  } finally {
    await erpConn.end();
    await colovoConn.end();
  }
}

runOnlineCleanup();
