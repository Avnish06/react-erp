const db = require('./config/db');
db.query('SELECT * FROM users WHERE name LIKE "%Aashu%" OR name LIKE "%Super Admin%"', (err, results) => {
  if (err) {
    console.error(err);
  } else {
    console.log("Users:", results);
  }
  
  db.query('SELECT * FROM user_identities', (err, res2) => {
     console.log("User Identities:", res2);
     process.exit();
  });
});
