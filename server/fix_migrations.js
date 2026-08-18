const mysql = require('mysql2/promise');

async function fixMigrations() {
    const conn = await mysql.createConnection({host:'localhost', user:'root', password:'', database:'colovo'});
    
    // Get all tables
    const [tables] = await conn.query('SHOW TABLES');
    const existingTables = tables.map(t => Object.values(t)[0]);
    
    // Get all migrations
    const [migrations] = await conn.query('SELECT id, migration FROM migrations');
    
    for (const row of migrations) {
        const mig = row.migration;
        // Try to guess table name from migration name (e.g. create_users_table)
        const match = mig.match(/create_(.*)_table/);
        if (match) {
            const tableName = match[1];
            if (!existingTables.includes(tableName)) {
                console.log(`Table ${tableName} is missing. Deleting migration: ${mig}`);
                await conn.query('DELETE FROM migrations WHERE id = ?', [row.id]);
            }
        }
    }
    
    console.log('Cleanup complete. Now run php artisan migrate');
    process.exit(0);
}

fixMigrations();
