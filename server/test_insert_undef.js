const db = require('./config/db');
db.query(
    'INSERT INTO customers (name, company_name, email, phone, requirements, assigned_to, stage, health_score, portal_access_enabled) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
    ['Test', 'Acme Corp', 'john@acme.com', '+1 555-0192', 'pkom', undefined, 'Won', 100, true],
    (err, result) => {
      if (err) {
        console.error("Error:", err);
      } else {
        console.log("Success:", result);
      }
      process.exit();
    }
  );
