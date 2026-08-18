const mysql = require('mysql2/promise');
async function run() {
  const conn = await mysql.createConnection({host:'localhost', user:'root', password:'', database:'colovo'});
  await conn.query("DELETE FROM migrations WHERE migration LIKE '%attendances_table%'");
  console.log('Deleted migration rows');
  process.exit(0);
}
run();
