const db = require('./config/db');

const createProposalsTable = `
CREATE TABLE IF NOT EXISTS proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(255) NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    admin_approved BOOLEAN DEFAULT FALSE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
`;

const createContractsTable = `
CREATE TABLE IF NOT EXISTS contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proposal_id INT,
    client_name VARCHAR(255) NOT NULL,
    document_content TEXT,
    status VARCHAR(50) DEFAULT 'Pending',
    admin_signature TEXT,
    client_signature TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
`;

db.query(createProposalsTable, (err) => {
    if (err) {
        console.error('Error creating proposals table:', err);
        process.exit(1);
    }
    console.log('Proposals table verified/created.');
    
    db.query(createContractsTable, (err) => {
        if (err) {
            console.error('Error creating contracts table:', err);
            process.exit(1);
        }
        console.log('Contracts table verified/created.');
        console.log('All client management tables are ready!');
        process.exit(0);
    });
});
