const mysql = require('mysql2/promise');
async function run() {
    const conn = await mysql.createConnection({host:'localhost', user:'root', password:'', database:'colovo'});
    const [rows] = await conn.query("SELECT id, name, email, role FROM users WHERE role = 'admin'");
    console.table(rows);
    process.exit(0);
}
run();
