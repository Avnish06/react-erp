const express = require('express');
const router = express.Router();
const db = require('../config/db');
const { verifyToken, checkPermission } = require('../middleware/auth');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

// Ensure uploads directory exists
const uploadDir = path.join(__dirname, '../uploads');
if (!fs.existsSync(uploadDir)) {
  fs.mkdirSync(uploadDir, { recursive: true });
}

// Configure multer storage
const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, uploadDir);
  },
  filename: function (req, file, cb) {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, 'logo-' + uniqueSuffix + path.extname(file.originalname));
  }
});
const upload = multer({ storage: storage });

// Note: we use colovoPromise for all queries in this route since it hits the Laravel database directly
const colovoDb = db.colovoPromise;

// Get all companies
router.get('/', verifyToken, async (req, res) => {
  try {
    const [companies] = await colovoDb.query('SELECT id, name, email, phone, address, logo, created_at FROM companies ORDER BY name ASC');
    res.json({ success: true, data: companies });
  } catch (error) {
    console.error('Error fetching companies:', error);
    res.status(500).json({ success: false, message: 'Failed to fetch companies' });
  }
});

// Add new company
router.post('/', verifyToken, checkPermission('manage_users'), upload.single('logo'), async (req, res) => {
  const { name, email, phone, address } = req.body;
  
  if (!name) return res.status(400).json({ success: false, message: 'Company name is required' });

  try {
    const [existing] = await colovoDb.query('SELECT id FROM companies WHERE name = ?', [name]);
    if (existing.length > 0) {
      return res.status(400).json({ success: false, message: 'Company already exists' });
    }

    const companyEmail = email || `contact@${name.toLowerCase().replace(/\s+/g, '')}.com`;
    const companyPhone = phone || null;
    const companyAddress = address || 'Unknown';
    const logoUrl = req.file ? `/uploads/${req.file.filename}` : null;
    const now = new Date();

    const [result] = await colovoDb.query(
      'INSERT INTO companies (name, email, phone, address, logo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
      [name, companyEmail, companyPhone, companyAddress, logoUrl, now, now]
    );

    res.status(201).json({ success: true, message: 'Company created successfully', id: result.insertId });
  } catch (error) {
    console.error('Error creating company:', error);
    res.status(500).json({ success: false, message: 'Failed to create company' });
  }
});

// Update company
router.put('/:id', verifyToken, checkPermission('manage_users'), upload.single('logo'), async (req, res) => {
  const { id } = req.params;
  const { name, email, phone, address } = req.body;
  
  if (!name) return res.status(400).json({ success: false, message: 'Company name is required' });

  try {
    const [existing] = await colovoDb.query('SELECT id FROM companies WHERE name = ? AND id != ?', [name, id]);
    if (existing.length > 0) {
      return res.status(400).json({ success: false, message: 'Company name already in use' });
    }

    const companyEmail = email || `contact@${name.toLowerCase().replace(/\s+/g, '')}.com`;
    const companyPhone = phone || null;
    const companyAddress = address || 'Unknown';
    const now = new Date();

    let query = 'UPDATE companies SET name = ?, email = ?, phone = ?, address = ?, updated_at = ?';
    let queryParams = [name, companyEmail, companyPhone, companyAddress, now];

    if (req.file) {
      query += ', logo = ?';
      queryParams.push(`/uploads/${req.file.filename}`);
    }

    query += ' WHERE id = ?';
    queryParams.push(id);

    const [result] = await colovoDb.query(query, queryParams);

    if (result.affectedRows === 0) {
      return res.status(404).json({ success: false, message: 'Company not found' });
    }

    res.json({ success: true, message: 'Company updated successfully' });
  } catch (error) {
    console.error('Error updating company:', error);
    res.status(500).json({ success: false, message: 'Failed to update company' });
  }
});

// Delete company
router.delete('/:id', verifyToken, checkPermission('manage_users'), async (req, res) => {
  const { id } = req.params;

  try {
    const [result] = await colovoDb.query('DELETE FROM companies WHERE id = ?', [id]);
    
    if (result.affectedRows === 0) {
      return res.status(404).json({ success: false, message: 'Company not found' });
    }

    res.json({ success: true, message: 'Company deleted successfully' });
  } catch (error) {
    console.error('Error deleting company:', error);
    res.status(500).json({ success: false, message: 'Failed to delete company' });
  }
});

module.exports = router;
