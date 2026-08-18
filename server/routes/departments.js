const express = require('express');
const router = express.Router();
const db = require('../config/db');

const { verifyToken, checkPermission } = require('../middleware/auth');

// Get all departments
router.get('/', verifyToken, (req, res) => {
  // Extract the first word of the company name to do a broad fuzzy match (handles 'Colvo' vs 'Colvo Corporation')
  const companyPrefix = req.company_name.split(' ')[0];
  const fuzzyCompanyName = `${companyPrefix}%`;
  
  db.query('SELECT * FROM departments WHERE company_name LIKE ?', [fuzzyCompanyName], (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Error fetching departments' });
    res.json({ success: true, data: results });
  });
});

// Add department
router.post('/', verifyToken, checkPermission('manage_departments'), (req, res) => {
  const { name } = req.body;
  
  if (!name || !name.trim()) {
    return res.status(400).json({ success: false, message: 'Department name is required' });
  }

  const trimmedName = name.trim();

  const companyPrefix = req.company_name.split(' ')[0];
  const fuzzyCompanyName = `${companyPrefix}%`;

  // Check if department already exists to prevent duplicate entry error
  db.query('SELECT id FROM departments WHERE name = ? AND company_name LIKE ?', [trimmedName, fuzzyCompanyName], (err, results) => {
    if (err) {
      return res.status(500).json({ success: false, message: 'Database error checking duplicate department' });
    }

    if (results.length > 0) {
      return res.status(400).json({ success: false, message: `Department "${trimmedName}" already exists.` });
    }

    db.query('INSERT INTO departments (name, company_name) VALUES (?, ?)', [trimmedName, req.company_name], (err, result) => {
      if (err) {
        if (err.code === 'ER_DUP_ENTRY') {
          return res.status(400).json({ success: false, message: `Department "${trimmedName}" already exists.` });
        }
        console.error('❌ SQL ERROR IN ADD DEPARTMENT:', err);
        return res.status(500).json({ success: false, message: 'Error adding department: ' + err.message });
      }
      res.json({ success: true, message: 'Department added successfully', id: result.insertId });
    });
  });
});

module.exports = router;
