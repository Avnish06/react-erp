const db = require('./config/db');

const alterCustomersTable = `
ALTER TABLE customers 
ADD COLUMN health_score INT DEFAULT 100,
ADD COLUMN portal_access_enabled BOOLEAN DEFAULT TRUE,
ADD COLUMN password VARCHAR(255) DEFAULT 'password123';
`;

const alterStageEnum = `
ALTER TABLE customers 
MODIFY COLUMN stage ENUM('Prospect','Active','Loyal','Won') DEFAULT 'Prospect';
`;

db.query(alterCustomersTable, (err) => {
    if (err) {
        console.error('Error adding columns to customers table:', err);
    } else {
        console.log('Successfully added columns to customers table.');
    }
    
    db.query(alterStageEnum, (err) => {
        if (err) {
            console.error('Error modifying stage enum in customers table:', err);
        } else {
            console.log('Successfully updated stage enum in customers table.');
        }
        process.exit(0);
    });
});
