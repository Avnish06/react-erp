const express = require('express');
const router = express.Router();
const db = require('../config/db');
const { verifyToken } = require('../middleware/auth');
const { sendClientOnboardingEmail, sendProposalEmail } = require('../utils/mailer');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

// Configure Multer for PDF Uploads
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const uploadDir = path.join(__dirname, '../uploads/proposals');
    if (!fs.existsSync(uploadDir)) {
      fs.mkdirSync(uploadDir, { recursive: true });
    }
    cb(null, uploadDir);
  },
  filename: (req, file, cb) => {
    cb(null, `proposal_${Date.now()}_${file.originalname}`);
  }
});
const upload = multer({ storage });

// @route   GET /api/client-management/proposals
// @desc    Get all proposals (or filter by client if logged in as client)
router.get('/proposals', verifyToken, (req, res) => {
  if (req.user.role === 'Client') {
    // Fetch client email from customers table
    db.query('SELECT email FROM customers WHERE id = ?', [req.user.id], (err, results) => {
      if (err || results.length === 0) return res.status(500).json({ success: false, message: 'Database error' });
      const clientEmail = results[0].email;
      
      db.query('SELECT * FROM proposals WHERE client_email = ? ORDER BY created_at DESC', [clientEmail], (err, propResults) => {
        if (err) return res.status(500).json({ success: false, message: 'Database error' });
        res.json({ success: true, proposals: propResults });
      });
    });
  } else {
    // Admin / Employee view
    db.query('SELECT * FROM proposals ORDER BY created_at DESC', (err, results) => {
      if (err) return res.status(500).json({ success: false, message: 'Database error' });
      res.json({ success: true, proposals: results });
    });
  }
});

// @route   POST /api/client-management/proposals
// @desc    Create a new proposal
router.post('/proposals', verifyToken, (req, res) => {
  const { client_name, client_email, project_name, value, details, terms } = req.body;
  const created_by = req.user.id;
  
  db.query(
    'INSERT INTO proposals (client_name, client_email, project_name, value, details, terms, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)',
    [client_name, client_email || null, project_name, value, details, terms, created_by],
    (err, result) => {
      if (err) return res.status(500).json({ success: false, message: 'Database error', error: err.message });
      res.status(201).json({ success: true, message: 'Proposal created', id: result.insertId });
    }
  );
});

// @route   POST /api/client-management/proposals/:id/send-email
// @desc    Upload PDF and send proposal to client via email
router.post('/proposals/:id/send-email', verifyToken, upload.single('proposal_pdf'), (req, res) => {
  const proposalId = req.params.id;
  if (!req.file) {
    return res.status(400).json({ success: false, message: 'No PDF file uploaded' });
  }

  const { client_name, client_email, project_name } = req.body;
  
  if (!client_email) {
    return res.status(400).json({ success: false, message: 'Client email is required to send proposal' });
  }

  const pdfUrl = `/uploads/proposals/${req.file.filename}`;
  const absolutePdfPath = req.file.path;

  // Update proposal record with the saved PDF path
  db.query('UPDATE proposals SET pdf_url = ? WHERE id = ?', [pdfUrl, proposalId], async (err) => {
    if (err) {
      console.error('Error updating proposal PDF URL:', err);
      return res.status(500).json({ success: false, message: 'Database error' });
    }

    // Send Email
    const emailResult = await sendProposalEmail(client_email, client_name, project_name, absolutePdfPath);
    if (emailResult.success) {
      res.json({ success: true, message: 'Proposal emailed to client successfully' });
    } else {
      res.status(500).json({ success: false, message: 'Failed to send email', error: emailResult.error });
    }
  });
});

// @route   PUT /api/client-management/proposals/:id/approve
// @desc    Admin (CEO) approves a proposal
router.put('/proposals/:id/approve', verifyToken, (req, res) => {
  const proposalId = req.params.id;
  
  db.query(
    'UPDATE proposals SET status = ?, admin_approved = ? WHERE id = ?',
    ['Approved', true, proposalId],
    (err, result) => {
      if (err) return res.status(500).json({ success: false, message: 'Database error' });
      res.json({ success: true, message: 'Proposal approved by Admin' });
    }
  );
});

// @route   PUT /api/client-management/proposals/:id/client-sign
// @desc    Client signs the proposal
router.put('/proposals/:id/client-sign', verifyToken, (req, res) => {
  const proposalId = req.params.id;
  const { signature } = req.body;
  
  db.query(
    'UPDATE proposals SET client_signature = ?, client_signed_at = NOW(), status = ? WHERE id = ?',
    [signature, 'Client Signed', proposalId],
    (err, result) => {
      if (err) return res.status(500).json({ success: false, message: 'Database error' });
      res.json({ success: true, message: 'Proposal signed successfully by client!' });
    }
  );
});

// @route   PUT /api/client-management/proposals/:id/admin-sign
// @desc    Admin counter-signs the proposal
router.put('/proposals/:id/admin-sign', verifyToken, (req, res) => {
  const proposalId = req.params.id;
  const { signature } = req.body;
  
  db.query(
    'UPDATE proposals SET admin_signature = ?, admin_signed_at = NOW(), status = ? WHERE id = ?',
    [signature, 'Fully Executed', proposalId],
    (err, result) => {
      if (err) return res.status(500).json({ success: false, message: 'Database error' });
      res.json({ success: true, message: 'Proposal fully executed!' });
    }
  );
});

// @route   GET /api/client-management/contracts
// @desc    Get all contracts
router.get('/contracts', verifyToken, (req, res) => {
  db.query('SELECT * FROM contracts ORDER BY created_at DESC', (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Database error' });
    res.json({ success: true, contracts: results });
  });
});

// @route   POST /api/client-management/contracts
// @desc    Generate a new contract from an approved proposal
router.post('/contracts', verifyToken, (req, res) => {
  const { proposal_id, client_name, document_content } = req.body;
  const created_by = req.user.id;
  
  db.query(
    'INSERT INTO contracts (proposal_id, client_name, document_content, created_by) VALUES (?, ?, ?, ?)',
    [proposal_id, client_name, document_content, created_by],
    (err, result) => {
      if (err) return res.status(500).json({ success: false, message: 'Database error' });
      res.status(201).json({ success: true, message: 'Contract created', id: result.insertId });
    }
  );
});

// @route   POST /api/client-management/onboard
// @desc    Convert lead into a new customer (Onboarding wizard)
router.post('/onboard', verifyToken, (req, res) => {
  const { lead_id, company_name, email, phone, contact_person, requirements } = req.body;
  const created_by = req.user.id;
  const generatedPassword = `Pass${Math.floor(1000 + Math.random() * 9000)}!`;
  const name = contact_person || company_name;
  
  db.query(
    'INSERT INTO customers (name, company_name, email, phone, requirements, assigned_to, stage, health_score, portal_access_enabled, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    [name, company_name, email, phone, requirements, created_by, 'Won', 100, true, generatedPassword],
    async (err, result) => {
      if (err) return res.status(500).json({ success: false, message: 'Database error', error: err.message });
      
      // Update lead status if lead_id was provided
      if (lead_id) {
        db.query("UPDATE leads SET status = 'Won' WHERE id = ?", [lead_id]);
      }
      
      // Send onboarding email asynchronously
      if (email) {
        sendClientOnboardingEmail(email, name, generatedPassword);
      }
      
      res.status(201).json({ success: true, message: 'Client onboarded successfully', id: result.insertId });
    }
  );
});


// @route   PUT /api/client-management/contracts/:id/sign
// @desc    Client or Admin signs a contract
router.put('/contracts/:id/sign', verifyToken, (req, res) => {
  const contractId = req.params.id;
  const { signature, role } = req.body;
  
  let updateField = role === 'admin' ? 'admin_signature' : 'client_signature';
  
  db.query(
    `UPDATE contracts SET ${updateField} = ?, status = 'Signed' WHERE id = ?`,
    [signature, contractId],
    (err, result) => {
      if (err) return res.status(500).json({ success: false, message: 'Database error', error: err.message });
      res.json({ success: true, message: 'Contract signed successfully' });
    }
  );
});

// @route   GET /api/client-management/health
// @desc    Calculate health score logic (Mock API for Customer 360)
router.get('/health', verifyToken, (req, res) => {
  db.query('SELECT id, name, company_name as company, health_score FROM customers WHERE stage = "Won" OR stage = "Active"', (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Database error' });
    res.json({ success: true, clients: results });
  });
});

module.exports = router;
