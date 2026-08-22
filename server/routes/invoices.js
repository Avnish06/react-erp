const express = require('express');
const router = express.Router();
const db = require('../config/db');
const { verifyToken, checkPermission } = require('../middleware/auth');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { sendInvoiceEmail } = require('../utils/mailer');

// Setup multer for PDF uploads
const uploadDir = path.join(__dirname, '../uploads/invoices');
if (!fs.existsSync(uploadDir)) {
  fs.mkdirSync(uploadDir, { recursive: true });
}

const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, uploadDir);
  },
  filename: (req, file, cb) => {
    cb(null, `${Date.now()}-${file.originalname}`);
  }
});
const upload = multer({ storage });

// GET /api/invoices - Get all invoices
router.get('/', verifyToken, (req, res) => {
  db.query('SELECT * FROM invoices ORDER BY created_at DESC', (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Error fetching invoices', error: err.message });
    res.status(200).json({ success: true, data: results });
  });
});

// POST /api/invoices - Create new invoice
router.post('/', verifyToken, upload.single('invoice_pdf'), (req, res) => {
  const { id, client_name, client_email, total_amount, invoice_date, currency, is_recurring } = req.body;
  const items = req.body.items ? JSON.parse(req.body.items) : [];
  const pdf_url = req.file ? `/uploads/invoices/${req.file.filename}` : null;

  console.log('Received invoice request:', { id, client_name, client_email, total_amount, pdf_url });

  db.beginTransaction((err) => {
    if (err) return res.status(500).json({ success: false, message: 'Error starting transaction' });

    // Insert Invoice Header
    db.query(
      'INSERT INTO invoices (id, client_name, client_email, total_amount, invoice_date, pdf_url) VALUES (?, ?, ?, ?, ?, ?)',
      [id, client_name, client_email || null, total_amount, invoice_date, pdf_url],
      (err) => {
        if (err) {
          console.error('Invoice Header Error:', err);
          return db.rollback(() => {
            res.status(500).json({ success: false, message: 'Error inserting invoice', error: err.message });
          });
        }

        const finalizeTransaction = () => {
          db.commit((err) => {
            if (err) {
              return db.rollback(() => {
                res.status(500).json({ success: false, message: 'Error committing transaction' });
              });
            }
            // Sync with Finance Module
            const financeQuery = 'INSERT INTO finance_transactions (type, category, amount, currency, exchange_rate, amount_base, date, description, is_recurring) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
            const exchange_rate = currency === 'USD' ? 83.5 : (currency === 'EUR' ? 90.2 : 1);
            const amount_base = total_amount * exchange_rate;
            
            db.query(financeQuery, ['income', 'Sales', total_amount, currency || 'INR', exchange_rate, amount_base, invoice_date, `Invoice ${id} for ${client_name}`, is_recurring === 'true'], (fErr) => {
              if (fErr) console.error('Error syncing invoice to finance:', fErr);
            });

            res.status(201).json({ success: true, message: 'Invoice generated successfully' });
          });
        };

        if (items && items.length > 0) {
          const itemValues = items.map(item => [
            id,
            item.description,
            item.qty,
            item.rate,
            item.qty * item.rate
          ]);
          db.query(
            'INSERT INTO invoice_items (invoice_id, description, quantity, rate, amount) VALUES ?',
            [itemValues],
            (err) => {
              if (err) {
                console.error('Invoice Items Error:', err);
                return db.rollback(() => {
                  res.status(500).json({ success: false, message: 'Error inserting items', error: err.message });
                });
              }
              finalizeTransaction();
            }
          );
        } else {
          finalizeTransaction();
        }
      }
    );
  });
});

// POST /api/invoices/send-email/:id
router.post('/send-email/:id', verifyToken, async (req, res) => {
  const { id } = req.params;

  db.query('SELECT * FROM invoices WHERE id = ?', [id], async (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Error fetching invoice', error: err.message });
    if (results.length === 0) return res.status(404).json({ success: false, message: 'Invoice not found' });

    const invoice = results[0];
    if (!invoice.client_email) {
      return res.status(400).json({ success: false, message: 'No email address on file for this invoice' });
    }
    if (!invoice.pdf_url) {
      return res.status(400).json({ success: false, message: 'No PDF file associated with this invoice' });
    }

    const pdfPath = path.join(__dirname, '..', invoice.pdf_url);
    if (!fs.existsSync(pdfPath)) {
      return res.status(404).json({ success: false, message: 'PDF file missing on server' });
    }

    const result = await sendInvoiceEmail(invoice.client_email, invoice.client_name, invoice.id, invoice.total_amount, pdfPath);
    if (result.success) {
      res.json({ success: true, message: 'Invoice emailed successfully' });
    } else {
      res.status(500).json({ success: false, message: 'Failed to send email', error: result.error });
    }
  });
});

module.exports = router;
