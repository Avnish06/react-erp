const express = require('express');
const router = express.Router();
const db = require('../config/db');
const { verifyToken, checkPermission } = require('../middleware/auth');

// Get all tickets (Admin/Super Admin/Developer sees all, User sees theirs)
// Get all tickets (Admin/Super Admin/Developer sees all, User sees theirs)
router.get('/', verifyToken, async (req, res) => {
  const { id: user_id, role } = req.user;
  const hasElevatedAccess = role === 'Super Admin' || role === 'Admin' || role === 'Developer';

  try {
    // 1. Fetch ERP Tickets
    let erpQuery = `
      SELECT t.*, COALESCE(u.name, ui.email) as creator_name, ui.email
      FROM tickets t
      JOIN user_identities ui ON t.user_id = ui.id
      LEFT JOIN users u ON t.user_id = u.id
    `;
    const params = [];
    if (!hasElevatedAccess) {
      erpQuery += ' WHERE t.user_id = ?';
      params.push(user_id);
    }
    erpQuery += ' ORDER BY t.created_at DESC';

    const [erpTickets] = await db.promise.query(erpQuery, params);

    const formattedErp = erpTickets.map(t => ({
      id: `erp-${t.id}`,
      source_id: t.id,
      user_id: t.user_id,
      creator_name: t.creator_name,
      email: t.email,
      title: t.title,
      description: t.description,
      priority: t.priority,
      category: t.category,
      status: t.status,
      created_at: t.created_at,
      source: 'erp'
    }));

    // 2. Fetch Colovo Tickets (only if admin/developer/super admin sees all or if we can match email)
    let formattedColovo = [];
    try {
      const colovoDb = require('../config/db').colovoPromise;
      let colovoQuery = `
        SELECT eq.*, u.name as creator_name, u.email
        FROM employee_queries eq
        JOIN users u ON eq.user_id = u.id
      `;
      const cParams = [];
      if (!hasElevatedAccess) {
        // Fetch user email to match
        const [me] = await db.promise.query('SELECT email FROM user_identities WHERE id = ?', [user_id]);
        if (me.length > 0) {
          colovoQuery += ' WHERE u.email = ?';
          cParams.push(me[0].email);
        } else {
          colovoQuery += ' WHERE 1=0'; // No match
        }
      }
      colovoQuery += ' ORDER BY eq.created_at DESC';

      const [colovoTickets] = await colovoDb.query(colovoQuery, cParams);
      
      formattedColovo = colovoTickets.map(t => ({
        id: `colovo-${t.id}`,
        source_id: t.id,
        user_id: t.user_id,
        creator_name: `${t.creator_name} (Colovo)`,
        email: t.email,
        title: t.subject,
        description: t.description,
        priority: 'Medium',
        category: 'Colovo Query',
        status: t.status === 'resolved' ? 'Resolved' : t.status === 'in-progress' ? 'In Progress' : 'Pending',
        created_at: t.created_at,
        source: 'colovo'
      }));
    } catch (colovoErr) {
      console.error('[Tickets Fetch] Colovo fetch failed:', colovoErr.message);
    }

    // 3. Merge and Sort
    const combined = [...formattedErp, ...formattedColovo].sort(
      (a, b) => new Date(b.created_at) - new Date(a.created_at)
    );

    res.json({ success: true, data: combined });
  } catch (err) {
    console.error('Error fetching tickets:', err);
    res.status(500).json({ success: false, message: 'Error fetching tickets' });
  }
});

// Create a ticket
router.post('/', verifyToken, (req, res) => {
  const { title, description, priority, category } = req.body;
  const user_id = req.body.user_id || req.user.id;

  const query = 'INSERT INTO tickets (user_id, title, description, priority, category) VALUES (?, ?, ?, ?, ?)';

  db.query(query, [user_id, title, description, priority || 'Medium', category || 'Issue'], (err, result) => {
    if (err) {
      console.error('Error creating ticket:', err);
      return res.status(500).json({ success: false, message: 'Error creating ticket' });
    }
    res.json({ success: true, message: 'Ticket created successfully', id: result.insertId });
  });
});

// Update ticket status or assignment (Admin/Super Admin/Developer)
router.put('/:id', verifyToken, checkPermission('manage_tickets'), async (req, res) => {
  const { status, assigned_to } = req.body;
  const { id } = req.params;

  try {
    if (id.startsWith('colovo-')) {
      const realId = id.replace('colovo-', '');
      const colovoDb = require('../config/db').colovoPromise;
      let colovoStatus = 'pending';
      if (status === 'Resolved') colovoStatus = 'resolved';
      else if (status === 'In Progress') colovoStatus = 'in-progress';
      
      await colovoDb.query('UPDATE employee_queries SET status = ? WHERE id = ?', [colovoStatus, realId]);
      res.json({ success: true, message: 'Colovo ticket updated successfully' });
    } else {
      const realId = id.replace('erp-', '');
      let query = 'UPDATE tickets SET ';
      const params = [];

      if (status) {
        query += 'status = ?';
        params.push(status);
      }

      if (assigned_to) {
        if (params.length > 0) query += ', ';
        query += 'assigned_to = ?';
        params.push(assigned_to);
      }

      query += ' WHERE id = ?';
      params.push(realId);

      await db.promise.query(query, params);
      res.json({ success: true, message: 'Ticket updated successfully' });
    }
  } catch (err) {
    console.error('Error updating ticket:', err);
    res.status(500).json({ success: false, message: 'Error updating ticket' });
  }
});

// Delete ticket (Admin/Super Admin/Developer)
router.delete('/:id', verifyToken, checkPermission('manage_tickets'), async (req, res) => {
  const { id } = req.params;
  try {
    if (id.startsWith('colovo-')) {
      const realId = id.replace('colovo-', '');
      const colovoDb = require('../config/db').colovoPromise;
      await colovoDb.query('DELETE FROM employee_queries WHERE id = ?', [realId]);
    } else {
      const realId = id.replace('erp-', '');
      await db.promise.query('DELETE FROM tickets WHERE id = ?', [realId]);
    }
    res.json({ success: true, message: 'Ticket deleted successfully' });
  } catch (err) {
    console.error('Error deleting ticket:', err);
    res.status(500).json({ success: false, message: 'Error deleting ticket' });
  }
});

module.exports = router;
