const db = require('./config/db');
db.query('DESCRIBE customers', (err, results) => {
  if (err) {
    console.error(err);
  } else {
    console.log(results);
  }
  process.exit();
});
