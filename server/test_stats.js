const db = require('./config/db');

async function test() {
  try {
    const [tables] = await db.promise().query('SHOW TABLES');
    let totalRows = 0;
    for (const table of tables) {
      const tableName = Object.values(table)[0];
      const [countResult] = await db.promise().query(`SELECT COUNT(*) as cnt FROM \`${tableName}\``);
      totalRows += countResult[0].cnt;
    }
    console.log('Stats success', totalRows);
  } catch (err) {
    console.error('Stats Exception:', err);
  }
  process.exit(0);
}

test();
