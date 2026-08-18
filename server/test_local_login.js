const mysql = require('mysql2/promise');
const bcrypt = require('bcryptjs');

async function testLocalLogin() {
  const conn = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'management_system'
  });

  try {
    const email = 'hatbaliyagroup@gmail.com';
    const password = 'Hatbaliya@123';

    const [rows] = await conn.query('SELECT * FROM user_identities WHERE email = ?', [email]);
    if (rows.length === 0) {
      console.log('❌ User not found locally.');
      return;
    }

    const user = rows[0];
    console.log('User password in DB:', user.password);
    console.log('User password length:', user.password.length);
    console.log('Expected hash:', '$2b$10$/UlsfRxQ1IToemqr2OgOcuAturM8caLEZf7FVxQi85Bxohdg6ghxS');
    console.log('Is exactly equal:', user.password === '$2b$10$/UlsfRxQ1IToemqr2OgOcuAturM8caLEZf7FVxQi85Bxohdg6ghxS');

    const isMatch = await bcrypt.compare(password, user.password);
    console.log(`Password comparison match result for ${email}:`, isMatch);

  } catch (err) {
    console.error('Error during test:', err);
  } finally {
    await conn.end();
  }
}

testLocalLogin();
