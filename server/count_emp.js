const mysql = require('mysql2/promise');
async function run() {
    const conn = await mysql.createConnection({host:'localhost', user:'root', password:'', database:'colovo'});
    const [rows] = await conn.query("SELECT COUNT(*) as count FROM users WHERE role = 'employee'");
    console.log('Total Employees: ' + rows[0].count);
    process.exit(0);
}
run();
