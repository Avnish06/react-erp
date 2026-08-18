const mysql = require('mysql2/promise');
const bcrypt = require('bcryptjs');

// Helper to hash password using Laravel-compatible bcrypt
async function hashPassword(plainPassword) {
  const salt = await bcrypt.genSalt(10);
  return await bcrypt.hash(plainPassword, salt);
}

// Laravel-specific bcrypt hash for 'password'
const LARAVEL_ADMIN_HASH = '$2y$10$Q5Bd9lBdzKXVDM4l3s.FgeIuXlNWOHKoPL.dqELVGVI09sk3/qeJG'; // password

async function runCleanup() {
  console.log('==================================================');
  console.log('  DATABASE CLEANUP & RESET STARTED');
  console.log('==================================================');

  // Load database configs from .env
  require('dotenv').config({ path: require('path').join(__dirname, '.env') });

  // 1. Establish connections using env details or local default
  const erpConn = await mysql.createConnection({
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'management_system'
  });

  const colovoConn = await mysql.createConnection({
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.COLOVO_DB_USER || process.env.DB_USER || 'root',
    password: process.env.COLOVO_DB_PASSWORD || process.env.DB_PASSWORD || '',
    database: process.env.COLOVO_DB_NAME || 'colovo'
  });

  try {
    // ----------------------------------------------------
    // CLEANING ERP DATABASE (management_system)
    // ----------------------------------------------------
    console.log('\n[1/2] Cleaning ERP Database...');
    await erpConn.query('SET FOREIGN_KEY_CHECKS = 0');

    // Get all tables in ERP
    const [erpTables] = await erpConn.query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    for (const tableObj of erpTables) {
      const tableName = Object.values(tableObj)[0];
      // Keep structural metadata tables intact
      if (!['roles', 'permissions', 'role_permissions', 'system_settings'].includes(tableName)) {
        console.log(`  - Truncating ${tableName}...`);
        await erpConn.query(`TRUNCATE TABLE \`${tableName}\``);
      }
    }
    await erpConn.query('SET FOREIGN_KEY_CHECKS = 1');
    console.log('  ✅ ERP transactional tables cleared.');

    // ----------------------------------------------------
    // CLEANING COLOVO DATABASE (colovo)
    // ----------------------------------------------------
    console.log('\n[2/2] Cleaning Colovo Workspace Database...');
    await colovoConn.query('SET FOREIGN_KEY_CHECKS = 0');

    // Get all tables in Colovo
    const [colovoTables] = await colovoConn.query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    for (const tableObj of colovoTables) {
      const tableName = Object.values(tableObj)[0];
      // Keep Laravel migrations metadata intact
      if (!['migrations'].includes(tableName)) {
        console.log(`  - Truncating ${tableName}...`);
        await colovoConn.query(`TRUNCATE TABLE \`${tableName}\``);
      }
    }
    await colovoConn.query('SET FOREIGN_KEY_CHECKS = 1');
    console.log('  ✅ Colovo transactional tables cleared.');

    // ----------------------------------------------------
    // SEEDING DEFAULT STRUCTURAL RECORDS
    // ----------------------------------------------------
    console.log('\n[3/3] Seeding fresh Admin & Company records...');

    // A. Seed Default Company in both DBs
    const erpCompanyName = 'Hatbaliya Technologies';
    const [erpCompRes] = await erpConn.query(
      'INSERT INTO companies (id, name, email, phone, address, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
      [1, erpCompanyName, 'contact@hatbaliya.com', '1234567890', 'Corporate Office']
    );
    await colovoConn.query(
      'INSERT INTO companies (id, name, email, phone, address, created_at, updated_at, status) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?)',
      [1, erpCompanyName, 'contact@hatbaliya.com', '1234567890', 'Corporate Office', 'active']
    );
    console.log('  - Seeded default Company (Hatbaliya Technologies, ID: 1).');

    // B. Seed Default Department in ERP
    await erpConn.query(
      'INSERT INTO departments (id, name, company_name) VALUES (?, ?, ?)',
      [1, 'Management', erpCompanyName]
    );
    console.log('  - Seeded default Department (Management, ID: 1).');

    // C. Hash Passwords
    const superAdminPass = await hashPassword('superadmin123');
    const adminPass = await hashPassword('admin123');

    // D. Seed Super Admin (ERP)
    const [saUser] = await erpConn.query(
      'INSERT INTO user_identities (email, password, role_id) VALUES (?, ?, ?)',
      ['superadmin@example.com', superAdminPass, 1]
    );
    await erpConn.query(
      'INSERT INTO superadmins (user_id, name, employee_id, status, company_name) VALUES (?, ?, ?, ?, ?)',
      [saUser.insertId, 'Super Admin', 'SA-001', 'Active', erpCompanyName]
    );
    console.log('  - Seeded ERP Super Admin (superadmin@example.com / superadmin123).');

    // E. Seed Admin (ERP)
    const [adUser] = await erpConn.query(
      'INSERT INTO user_identities (email, password, role_id) VALUES (?, ?, ?)',
      ['admin@example.com', adminPass, 2]
    );
    await erpConn.query(
      'INSERT INTO admins (user_id, name, employee_id, status, company_name) VALUES (?, ?, ?, ?, ?)',
      [adUser.insertId, 'Admin User', 'AD-001', 'Active', erpCompanyName]
    );
    console.log('  - Seeded ERP Admin (admin@example.com / admin123).');

    // F. Seed Admin (Colovo)
    const [colovoAdmin] = await colovoConn.query(
      'INSERT INTO users (id, name, email, password, role, department, position, salary, company_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
      [1, 'ESP Admin', 'admin@colovo.com', LARAVEL_ADMIN_HASH, 'admin', 'Management', 'HR Director', 120000.00, 1]
    );
    console.log('  - Seeded Colovo Workspace Admin (admin@colovo.com / password).');

    console.log('\n==================================================');
    console.log('  ✅ DATABASE CLEANUP COMPLETE!');
    console.log('==================================================');

  } catch (err) {
    console.error('❌ Error executing cleanup:', err);
  } finally {
    await erpConn.end();
    await colovoConn.end();
  }
}

runCleanup();
