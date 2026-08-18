const db = require('./config/db');
db.query('SHOW CREATE TABLE customers', (err, results) => {
  if (err) {
    console.error(err);
  } else {
    console.log(results[0]['Create Table']);
  }
  process.exit();
});
