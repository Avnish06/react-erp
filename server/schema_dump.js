const mysql = require('mysql2/promise');

async function dump() {
  const conn1 = await mysql.createConnection({host:'localhost', user:'root', password:'', database:'management_system'});
  const [res] = await conn1.query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
  console.log('--- VIEWS ---');
  console.table(res);
  process.exit(0);
}
dump();
