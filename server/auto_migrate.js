const dbPromise = require('./config/db').promise;

async function runAutoMigration() {
  console.log('[AutoMigrate] Starting automatic database verification...');
  try {
    const queries = [
      "ALTER TABLE admins ADD COLUMN company_name VARCHAR(255) DEFAULT 'Colvo Corporation'",
      "ALTER TABLE employees ADD COLUMN company_name VARCHAR(255) DEFAULT 'Colvo Corporation'",
      "ALTER TABLE developers ADD COLUMN company_name VARCHAR(255) DEFAULT 'Colvo Corporation'",
      "ALTER TABLE vendors ADD COLUMN company_name VARCHAR(255) DEFAULT 'Colvo Corporation'",
      "ALTER TABLE leave_requests ADD COLUMN company_name VARCHAR(255) DEFAULT 'Colvo Corporation'",
      "ALTER TABLE projects ADD COLUMN company_name VARCHAR(255) DEFAULT 'Colvo Corporation'",
      "ALTER TABLE payroll ADD COLUMN company_name VARCHAR(255) DEFAULT 'Colvo Corporation'",
      "ALTER TABLE notifications ADD COLUMN company_name VARCHAR(255) DEFAULT 'Colvo Corporation'",
      "ALTER TABLE departments ADD COLUMN company_name VARCHAR(255) DEFAULT 'Colvo Corporation'"
    ];
    
    for (const q of queries) {
      try {
        await dbPromise.query(q);
        console.log('[AutoMigrate] Added missing column.');
      } catch (err) {
        // Ignore duplicate column errors silently
      }
    }

    console.log('[AutoMigrate] Recreating users view...');
    await dbPromise.query("DROP VIEW IF EXISTS users");
    const createViewQuery = `
      CREATE VIEW users AS 
      SELECT 
        ui.id AS id, 
        ui.email AS email, 
        ui.password AS password, 
        ui.role_id AS role_id, 
        ui.created_at AS joined_at, 
        COALESCE(sa.name, ad.name, em.name, dev.name, CONCAT(v.first_name, ' ', v.last_name)) AS name, 
        COALESCE(sa.employee_id, ad.employee_id, em.employee_id, dev.employee_id, v.vendor_id) AS employee_id, 
        v.vendor_id AS vendor_id, 
        COALESCE(sa.status, ad.status, em.status, dev.status, v.status) AS status, 
        em.department_id AS department_id, 
        COALESCE(sa.phone, v.phone) AS phone, 
        COALESCE(sa.company_name, ad.company_name, em.company_name, dev.company_name, v.company_name) AS company_name 
      FROM user_identities ui 
      LEFT JOIN superadmins sa ON ui.id = sa.user_id 
      LEFT JOIN admins ad ON ui.id = ad.user_id 
      LEFT JOIN employees em ON ui.id = em.user_id 
      LEFT JOIN developers dev ON ui.id = dev.user_id 
      LEFT JOIN vendors v ON ui.id = v.user_id
    `;
    await dbPromise.query(createViewQuery);
    console.log('[AutoMigrate] Successfully restored users view! Database is healthy.');
  } catch (err) {
    console.error('[AutoMigrate] Failed to run migration:', err.message);
  }
}

// Run it once, wait 2 seconds for DB to connect
setTimeout(runAutoMigration, 2000);
