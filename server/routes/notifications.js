const express = require('express');
const router = express.Router();
const db = require('../config/db');
const { verifyToken, checkPermission } = require('../middleware/auth');

// Create Announcement (Admin/Super Admin only)
router.post('/announcements', verifyToken, (req, res) => {
  const { title, content } = req.body;

  if (!title || !content) {
    return res.status(400).json({ success: false, message: 'All fields are required' });
  }

  const query = 'INSERT INTO announcements (title, content, created_by) VALUES (?, ?, ?)';
  db.query(query, [title, content, req.user.id], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: 'Error creating announcement' });
    
    // Create a local notification for all users in the ERP
    db.query('SELECT id FROM users', (userErr, users) => {
      if (!userErr && users) {
        users.forEach(u => {
          db.query(
            'INSERT INTO notifications (user_id, title, message, type, triggered_by, action_type) VALUES (?, ?, ?, ?, ?, ?)',
            [u.id, title, content, 'announcement', req.user.id, 'NEW_ANNOUNCEMENT']
          );
        });
      }
    });

    // --- WORKSPACE SYNC ---
    // Instantly push this announcement to the Laravel Workspace database
    try {
      const { colovoPromise } = require('../config/db');
      const crypto = require('crypto');
      
      // Default to company_id = 1 if company handling isn't fully separated yet
      const insertColovoQuery = 'INSERT INTO announcements (company_id, title, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())';
      
      colovoPromise.execute(insertColovoQuery, [1, title, content])
        .then(async () => {
          console.log('[Workspace Sync] Announcement inserted successfully into Colovo DB');
          
          // ALSO send a Notification to the Bell Icon for every user in the company!
          const [users] = await colovoPromise.query('SELECT id FROM users WHERE company_id = ?', [1]);
          for (let u of users) {
             const notifId = crypto.randomUUID();
             const dataJson = JSON.stringify({ title: title, message: content, type: 'announcement' });
             await colovoPromise.execute(
               'INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
               [notifId, 'App\\Notifications\\NewAnnouncement', 'App\\Models\\User', u.id, dataJson]
             );
          }
          console.log('[Workspace Sync] Bell notifications pushed to all employees!');
        })
        .catch(e => {
          console.error('[Workspace Sync Error] Failed to insert announcement:', e.message);
        });
    } catch (e) {
      console.error('[Workspace Sync Error] Missing config:', e.message);
    }
    // -----------------------

    res.json({ success: true, message: 'Announcement created successfully', id: result.insertId });
  });
});

// Get all announcements
router.get('/announcements', verifyToken, (req, res) => {
  const query = `
    SELECT a.*, u.name as created_by_name 
    FROM announcements a
    LEFT JOIN users u ON a.created_by = u.id
    ORDER BY a.created_at DESC
  `;
  db.query(query, (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Error fetching announcements' });
    res.json({ success: true, data: results });
  });
});

// Delete Announcement (Admin/Super Admin/Developer)
router.delete('/announcements/:id', verifyToken, (req, res) => {
  const query = 'DELETE FROM announcements WHERE id = ?';
  db.query(query, [req.params.id], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: 'Error deleting announcement' });
    res.json({ success: true, message: 'Announcement deleted successfully' });
  });
});

// Send Notification to specific user
router.post('/send', verifyToken, (req, res) => {
  const { user_id, title, message, type } = req.body;

  if (!user_id || !title || !message) {
    return res.status(400).json({ success: false, message: 'Missing required fields' });
  }

  const query = 'INSERT INTO notifications (user_id, title, message, type, triggered_by, action_type) VALUES (?, ?, ?, ?, ?, ?)';
  db.query(query, [user_id, title, message, type || 'info', req.user.id, 'MANUAL_NOTIFICATION'], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: 'Error sending notification' });

    // --- WORKSPACE SYNC ---
    // Instantly push this notification to the Laravel Workspace database
    db.query('SELECT email FROM users WHERE id = ?', [user_id], async (userErr, userRows) => {
      if (!userErr && userRows.length > 0) {
        const email = userRows[0].email;
        try {
          const { colovoPromise } = require('../config/db');
          const crypto = require('crypto');
          const [colovoUsers] = await colovoPromise.query('SELECT id FROM users WHERE email = ?', [email]);
          if (colovoUsers.length > 0) {
            const colovoUserId = colovoUsers[0].id;
            const notifId = crypto.randomUUID();
            const dataJson = JSON.stringify({ title: title, message: message, type: type || 'info' });
            await colovoPromise.execute(
              'INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
              [notifId, 'App\\Notifications\\GeneralNotification', 'App\\Models\\User', colovoUserId, dataJson]
            );
            console.log('[Workspace Sync] Manual notification synced to Colovo user ID:', colovoUserId);
          }
        } catch (syncErr) {
          console.error('[Workspace Sync Error] Failed to sync manual notification:', syncErr.message);
        }
      }
    });
    // -----------------------

    res.json({ success: true, message: 'Notification sent successfully', id: result.insertId });
  });
});

// Get notifications for current user
router.get('/user', verifyToken, (req, res) => {
  let query = `
    SELECT n.*, u.name as triggered_by_name 
    FROM notifications n
    LEFT JOIN users u ON n.triggered_by = u.id
  `;

  const queryParams = [];

  // Developer role sees ALL notifications (God-mode access)
  if (req.user.role !== 'Developer') {
    query += ' WHERE n.user_id = ?';
    queryParams.push(req.user.id);
  }

  query += ' ORDER BY n.created_at DESC';

  db.query(query, queryParams, (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Error fetching notifications' });
    res.json({ success: true, data: results });
  });
});

// Mark ALL notifications as read for current user
router.put('/read-all', verifyToken, (req, res) => {
  let query = 'UPDATE notifications SET is_read = TRUE WHERE is_read = FALSE';
  const params = [];

  if (req.user.role !== 'Developer') {
    query += ' AND user_id = ?';
    params.push(req.user.id);
  }

  db.query(query, params, (err, result) => {
    if (err) return res.status(500).json({ success: false, message: 'Error marking all as read' });
    res.json({ success: true, message: `${result.affectedRows} notifications marked as read` });
  });
});

// Mark notification as read
router.put('/:id/read', verifyToken, (req, res) => {
  let query = 'UPDATE notifications SET is_read = TRUE WHERE id = ?';
  const params = [req.params.id];

  if (req.user.role !== 'Developer') {
    query += ' AND user_id = ?';
    params.push(req.user.id);
  }

  db.query(query, params, (err) => {
    if (err) return res.status(500).json({ success: false, message: 'Error updating notification' });
    res.json({ success: true, message: 'Notification marked as read' });
  });
});

// Delete notification
router.delete('/:id', verifyToken, (req, res) => {
  const query = 'DELETE FROM notifications WHERE id = ? AND user_id = ?';
  db.query(query, [req.params.id, req.user.id], (err) => {
    if (err) return res.status(500).json({ success: false, message: 'Error deleting notification' });
    res.json({ success: true, message: 'Notification deleted' });
  });
});

module.exports = router;
