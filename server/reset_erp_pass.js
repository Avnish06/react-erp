const mysql = require('mysql2/promise');
const bcrypt = require('bcryptjs');

async function run() {
    const conn = await mysql.createConnection({host:'localhost', user:'root', password:'', database:'management_system'});
    const hashedPassword = await bcrypt.hash('admin123', 10);
    await conn.query("UPDATE user_identities SET password = ? WHERE email = 'superadmin@example.com'", [hashedPassword]);
    console.log('Password reset for superadmin@example.com to admin123');
    process.exit(0);
}
run();
