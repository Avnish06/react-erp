const mysql = require('mysql2/promise');
async function fix() {
  const conn = await mysql.createConnection({host:'localhost', user:'root', password:'', database:'colovo'});
  await conn.query("UPDATE notifications SET type = 'App\\\\Notifications\\\\NewAnnouncement'");
  await conn.query("UPDATE notifications SET notifiable_type = 'App\\\\Models\\\\User'");
  console.log('Fixed DB');
  process.exit(0);
}
fix();
