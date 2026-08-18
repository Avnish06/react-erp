const db = require('./config/db');
const bcrypt = require('bcryptjs');

async function createEmployee() {
    // Get command line arguments
    const args = process.argv.slice(2);
    
    if (args.length < 4) {
        console.log("Usage: node create_employee.js <email> <password> <full_name> <company_name> [role_id]");
        console.log("Example: node create_employee.js newuser@test.com pass123 \"John Doe\" \"Hatbaliya technology\" 3");
        process.exit(1);
    }

    const email = args[0];
    const password = args[1];
    const name = args[2];
    const companyName = args[3];
    // Default role_id to 3 (Employee ERP) if not provided
    const roleId = args[4] ? parseInt(args[4]) : 3;
    
    try {
        console.log(`Creating employee with Email: ${email}, Name: ${name}, Company: ${companyName}, Role ID: ${roleId}`);
        console.log("Hashing password...");
        const hashedPassword = await bcrypt.hash(password, 10);
        
        const identityQuery = 'INSERT INTO user_identities (email, password, role_id) VALUES (?, ?, ?)';
        
        db.query(identityQuery, [email, hashedPassword, roleId], (err, result) => {
            if (err) {
                console.error("Error creating identity:", err.message);
                process.exit(1);
            }
            
            console.log("User identity created. Inserting profile...");
            const userId = result.insertId;
            // Generate a random EMP id for this user
            const empId = `EMP-${Math.floor(1000 + Math.random() * 9000)}`;
            const profileQuery = 'INSERT INTO employees (user_id, name, employee_id, status, company_name) VALUES (?, ?, ?, ?, ?)';
            
            db.query(profileQuery, [userId, name, empId, 'Active', companyName], (err, res) => {
                 if (err) {
                     console.error("Error creating profile:", err.message);
                 } else {
                     console.log(`✅ Employee created successfully! (User ID: ${userId}, Employee ID: ${empId})`);
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
