const db = require('./config/db');

const queries = [
  'ALTER TABLE proposals ADD COLUMN client_signature LONGTEXT;',
  'ALTER TABLE proposals ADD COLUMN admin_signature LONGTEXT;',
  'ALTER TABLE proposals ADD COLUMN client_signed_at TIMESTAMP;',
  'ALTER TABLE proposals ADD COLUMN admin_signed_at TIMESTAMP;',
  'ALTER TABLE proposals MODIFY COLUMN client_signature LONGTEXT;',
  'ALTER TABLE proposals MODIFY COLUMN admin_signature LONGTEXT;'
];

let completed = 0;
for (const q of queries) {
  db.query(q, (err) => {
    if (err && err.code !== 'ER_DUP_FIELDNAME') {
      console.log('Error executing query:', q, err.message);
    } else {
      console.log('Successfully executed:', q);
    }
    completed++;
    if (completed === queries.length) {
      console.log('Done altering proposals table.');
      process.exit(0);
    }
  });
}
