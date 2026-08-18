const mysql = require('mysql2/promise');

async function run() {
  const conn = await mysql.createConnection({host:'localhost', user:'root', password:'', database:'colovo'});
  await conn.query("UPDATE companies SET name = 'Colvo Corporation', email = 'contact@colvocorporation.com' WHERE id = 1");
  console.log('Company updated');
  process.exit(0);
}

run();
