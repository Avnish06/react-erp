const db = require('./config/db');
const bcrypt = require('bcryptjs');

async function createEmployee() {
    const email = 'mrankitkashyap123@gmail.com';
    const password = '123456789';
    const name = 'Ankit kashyap';
    const companyName = 'Hatbaliya technology';
    
    try {
        console.log("Hashing password...");
        // Hash the password just like your auth routes do
        const hashedPassword = await bcrypt.hash(password, 10);
        
        console.log("Inserting user identity...");
        // role_id 3 = Employee ERP
        const identityQuery = 'INSERT INTO user_identities (email, password, role_id) VALUES (?, ?, ?)';
        
        db.query(identityQuery, [email, hashedPassword, 3], (err, result) => {
            if (err) {
                console.error("Error creating identity:", err);
                process.exit(1);
            }
            
            console.log("User identity created. Inserting profile...");
            const userId = result.insertId;
            const profileQuery = 'INSERT INTO employees (user_id, name, employee_id, status, company_name) VALUES (?, ?, ?, ?, ?)';
            
            db.query(profileQuery, [userId, name, 'EMP-001', 'Active', companyName], (err, res) => {
                 if (err) {
                     console.error("Error creating profile:", err);
                 } else {
                     console.log("Employee created successfully! You can now log in.");
                 }
                 process.exit();
            });
        });
    } catch (e) {
        console.error("Caught error:", e);
        process.exit(1);
    }
}

createEmployee();
