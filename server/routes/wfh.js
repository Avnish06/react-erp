const express = require('express');
const router = express.Router();
const db = require('../config/db');
const { verifyToken, checkPermission } = require('../middleware/auth');

// Request WFH (Employees)
router.post('/', verifyToken, (req, res) => {
  const { date, reason } = req.body;
  const user_id = req.user.id;

  if (!date) return res.status(400).json({ success: false, message: 'Date is required' });

  const query = 'INSERT INTO wfh_requests (user_id, date, reason) VALUES (?, ?, ?)';
  db.query(query, [user_id, date, reason], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: 'DB error: ' + err.message });
    
    // Notify admins
    const adminQuery = `SELECT id FROM users WHERE role IN ('Admin', 'Super Admin')`;
    db.query(adminQuery, (adminErr, admins) => {
      if (!adminErr && admins) {
        db.query(`SELECT name FROM users WHERE id = ?`, [user_id], (uErr, uRes) => {
          const empName = (!uErr && uRes.length > 0) ? uRes[0].name : 'An employee';
          const title = 'New WFH Application';
          const message = `${empName} applied for Work From Home on ${date}. Reason: ${reason}`;
          admins.forEach(admin => {
            db.query(
              'INSERT INTO notifications (user_id, title, message, type, triggered_by, action_type) VALUES (?, ?, ?, ?, ?, ?)',
              [admin.id, title, message, 'info', user_id, 'NEW_WFH_REQUEST']
            );
          });
        });
      }
    });

    res.json({ success: true, message: 'WFH request submitted successfully', id: result.insertId });
  });
});

// Get my WFH requests
router.get('/my', verifyToken, (req, res) => {
  const user_id = req.user.id;
  const query = 'SELECT * FROM wfh_requests WHERE user_id = ? ORDER BY date DESC';
  db.query(query, [user_id], (err, results) => {
    if (err) {
      console.error('WFH My Fetch Error:', err);
      return res.status(500).json({ success: false, message: 'DB error: ' + err.message });
    }
    res.json({ success: true, data: results });
  });
});

// Get all WFH requests (Admins+)
router.get('/all', verifyToken, async (req, res) => {
  const { role } = req.user;
  if (role !== 'Admin' && role !== 'Super Admin' && role !== 'Developer') {
    return res.status(403).json({ success: false, message: 'Access denied' });
  }

  try {
    // 1. Fetch ERP WFH requests
    const erpQuery = `
      SELECT w.*, COALESCE(u.name, ui.email) as employee_name, u.employee_id 
      FROM wfh_requests w
      JOIN user_identities ui ON w.user_id = ui.id
      LEFT JOIN users u ON w.user_id = u.id
      ORDER BY w.date DESC, w.created_at DESC
    `;
    const [erpResults] = await db.promise.query(erpQuery);
    
    const formattedErp = erpResults.map(w => ({
      id: `erp-${w.id}`,
      source_id: w.id,
      user_id: w.user_id,
      employee_id: w.employee_id,
      employee_name: w.employee_name,
      date: w.date,
      reason: w.reason,
      status: w.status,
      reviewed_by: w.reviewed_by,
      reviewed_at: w.reviewed_at,
      created_at: w.created_at,
      source: 'erp'
    }));

    // 2. Fetch Colovo WFH requests (leaves table where type is 'Work From Home' or 'WFH')
    let formattedColovo = [];
    try {
      const colovoDb = require('../config/db').colovoPromise;
      const colovoQuery = `
        SELECT l.*, u.name as employee_name, u.email
        FROM leaves l
        JOIN users u ON l.user_id = u.id
        WHERE LOWER(l.type) IN ('work from home', 'wfh', 'work_from_home')
        ORDER BY l.start_date DESC
      `;
      const [colovoResults] = await colovoDb.query(colovoQuery);
      
      formattedColovo = colovoResults.map(l => ({
        id: `colovo-${l.id}`,
        source_id: l.id,
        user_id: l.user_id,
        employee_id: null,
        employee_name: `${l.employee_name} (Colovo)`,
        date: l.start_date,
        reason: `${l.reason || 'No reason provided'} (Dates: ${new Date(l.start_date).toLocaleDateString()} to ${new Date(l.end_date).toLocaleDateString()})`,
        status: l.status.charAt(0).toUpperCase() + l.status.slice(1),
        reviewed_by: null,
        reviewed_at: l.updated_at,
        created_at: l.created_at,
        source: 'colovo'
      }));
    } catch (colovoErr) {
      console.error('[WFH Fetch] Colovo fetch failed:', colovoErr.message);
    }

    const combined = [...formattedErp, ...formattedColovo].sort(
      (a, b) => new Date(b.created_at) - new Date(a.created_at)
    );

    res.json({ success: true, data: combined });
  } catch (err) {
    console.error('WFH All Fetch Error:', err);
    res.status(500).json({ success: false, message: 'DB error: ' + err.message });
  }
});

// Update WFH status (Admins+)
router.put('/:id/status', verifyToken, async (req, res) => {
  const { status } = req.body;
  const { id } = req.params;
  const reviewer_id = req.user.id;
  const { role } = req.user;

  if (role !== 'Admin' && role !== 'Super Admin' && role !== 'Developer') {
    return res.status(403).json({ success: false, message: 'Access denied' });
  }

  if (!['Approved', 'Rejected'].includes(status)) {
    return res.status(400).json({ success: false, message: 'Invalid status' });
  }

  try {
    if (id.startsWith('colovo-')) {
      const realId = id.replace('colovo-', '');
      const colovoDb = require('../config/db').colovoPromise;
      await colovoDb.query('UPDATE leaves SET status = ? WHERE id = ?', [status.toLowerCase(), realId]);
      res.json({ success: true, message: `Colovo WFH request ${status.toLowerCase()} successfully` });
    } else {
      const realId = id.replace('erp-', '');
      const query = 'UPDATE wfh_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?';
      await db.promise.query(query, [status, reviewer_id, realId]);
      res.json({ success: true, message: `WFH request ${status.toLowerCase()} successfully` });
    }
  } catch (err) {
    console.error('Error updating WFH status:', err);
    res.status(500).json({ success: false, message: 'DB error: ' + err.message });
  }
});

// Check WFH status for a user on a specific date
router.get('/check/:date', verifyToken, (req, res) => {
  const { date } = req.params;
  const user_id = req.user.id;

  const query = 'SELECT status FROM wfh_requests WHERE user_id = ? AND date = ? AND status = "Approved"';
  db.query(query, [user_id, date], (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'DB error' });
    res.json({ success: true, isApproved: results.length > 0 });
  });
});

module.exports = router;
