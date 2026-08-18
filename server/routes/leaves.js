const express = require('express');
const router = express.Router();
const db = require('../config/db');
const axios = require('axios');

const { verifyToken, checkPermission } = require('../middleware/auth');

const COLOVO_URL = process.env.COLOVO_WORKSPACE_URL || 'http://127.0.0.1:8000';
const ERP_SECRET = process.env.ERP_SHARED_SECRET    || 'default-erp-secret-12345';

// Apply for leave
router.post('/apply', verifyToken, (req, res) => {
  const { user_id, leave_type, start_date, end_date, reason } = req.body;
  const query = 'INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?)';
  db.query(query, [user_id, leave_type, start_date, end_date, reason], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: 'Error applying for leave' });
    res.json({ success: true, message: 'Leave application submitted', id: result.insertId });
  });
});

// Get leave requests for a user
router.get('/:user_id', verifyToken, (req, res) => {
  db.query('SELECT * FROM leave_requests WHERE user_id = ? ORDER BY created_at DESC', [req.params.user_id], (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Error fetching leave requests' });
    res.json({ success: true, data: results });
  });
});

// Get all leave requests (Admin)
router.get('/', verifyToken, checkPermission('view_leaves'), async (req, res) => {
  try {
    // 1. Fetch ERP Leaves
    const erpQuery = `
      SELECT lr.*, COALESCE(u.name, ui.email) as employee_name, ui.email
      FROM leave_requests lr
      JOIN user_identities ui ON lr.user_id = ui.id
      LEFT JOIN users u ON lr.user_id = u.id
      ORDER BY lr.created_at DESC
    `;
    const [erpLeaves] = await db.promise.query(erpQuery);

    const formattedErp = erpLeaves.map(l => ({
      id: `erp-${l.id}`,
      source_id: l.id,
      user_id: l.user_id,
      employee_name: l.employee_name,
      email: l.email,
      leave_type: l.leave_type,
      start_date: l.start_date,
      end_date: l.end_date,
      reason: l.reason,
      status: l.status,
      rejection_reason: l.rejection_reason || null,
      created_at: l.created_at,
      source: 'erp'
    }));

    // 2. Fetch Colovo Leaves
    let formattedColovo = [];
    try {
      const colovoDb = require('../config/db').colovoPromise;
      const colovoQuery = `
        SELECT l.*, u.name as employee_name, u.email
        FROM leaves l
        JOIN users u ON l.user_id = u.id
        WHERE LOWER(l.type) NOT IN ('work from home', 'wfh', 'work_from_home')
        ORDER BY l.created_at DESC
      `;
      const [colovoLeaves] = await colovoDb.query(colovoQuery);
      
      formattedColovo = colovoLeaves.map(l => ({
        id: `colovo-${l.id}`,
        source_id: l.id,
        user_id: l.user_id,
        employee_name: `${l.employee_name} (Colovo)`,
        email: l.email,
        leave_type: l.type,
        start_date: l.start_date,
        end_date: l.end_date,
        reason: l.reason,
        status: l.status,
        rejection_reason: null,
        created_at: l.created_at,
        source: 'colovo'
      }));
    } catch (colovoErr) {
      console.error('[Leaves Fetch] Colovo fetch failed:', colovoErr.message);
    }

    // 3. Merge and Sort
    const combined = [...formattedErp, ...formattedColovo].sort(
      (a, b) => new Date(b.created_at) - new Date(a.created_at)
    );

    res.json({ success: true, data: combined });
  } catch (err) {
    console.error('Error fetching leaves:', err);
    res.status(500).json({ success: false, message: 'Error fetching leave requests' });
  }
});

// Update leave status (Approve/Reject) — also syncs notification to Colovo Workspace
router.put('/:id', verifyToken, checkPermission('manage_leaves'), async (req, res) => {
  const { status, rejection_reason } = req.body;
  const { id } = req.params;

  try {
    if (id.startsWith('colovo-')) {
      const realId = id.replace('colovo-', '');
      const colovoDb = require('../config/db').colovoPromise;
      
      // Update in Colovo DB directly
      await colovoDb.query('UPDATE leaves SET status = ? WHERE id = ?', [status.toLowerCase(), realId]);
      res.json({ success: true, message: `Colovo Leave request ${status} successfully` });
    } else {
      const realId = id.replace('erp-', '');
      const query = 'UPDATE leave_requests SET status = ?, rejection_reason = ? WHERE id = ?';
      await db.promise.query(query, [status, rejection_reason || null, realId]);

      // --- Sync leave status to Colovo Workspace ---
      db.query(
        `SELECT ui.email FROM leave_requests lr
         JOIN user_identities ui ON lr.user_id = ui.id
         WHERE lr.id = ? LIMIT 1`,
        [realId],
        (emailErr, emailRows) => {
          if (!emailErr && emailRows.length > 0) {
            const employeeEmail = emailRows[0].email;
            axios.post(`${COLOVO_URL}/api/sync-leave-status`, {
              employee_email : employeeEmail,
              status         : status,
              reason         : rejection_reason || null,
            }, {
              headers: { 'X-ERP-SECRET': ERP_SECRET }
            }).then(() => {
              console.log(`[Leave Sync] Leave ${status} notification sent to ${employeeEmail} on Colovo Workspace.`);
            }).catch(syncErr => {
              console.error('[Leave Sync] Failed to sync leave status to Colovo Workspace:', syncErr.message);
            });
          }
        }
      );

      res.json({ success: true, message: `Leave ${status} successfully` });
    }
  } catch (err) {
    console.error('Error updating leave status:', err);
    res.status(500).json({ success: false, message: 'Error updating leave status' });
  }
});

module.exports = router;

