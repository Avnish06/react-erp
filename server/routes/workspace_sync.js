const express = require('express');
const router = express.Router();
const crypto = require('crypto');
const { verifyToken } = require('../middleware/auth'); // If you want to secure these endpoints

// Use colovoPromise directly to query the Colovo Workspace (Laravel) database
const colovoDb = require('../config/db').colovoPromise;

// Helper function to resolve employee by email
async function getColovoUser(email) {
  const [users] = await colovoDb.query('SELECT * FROM users WHERE email = ? LIMIT 1', [email]);
  if (users.length === 0) return null;
  return users[0];
}

// 1. Get Employee Details from Colovo Workspace
router.get('/employee/:email/details', verifyToken, async (req, res) => {
  try {
    const user = await getColovoUser(req.params.email);
    if (!user) return res.status(404).json({ success: false, message: 'Employee not found in Colovo Workspace' });

    // Exclude password and sensitive info
    delete user.password;
    delete user.remember_token;
    delete user.face_descriptor;

    res.json({ success: true, data: user });
  } catch (err) {
    console.error('[Sync API] Error fetching details:', err);
    res.status(500).json({ success: false, message: 'Server error fetching employee details' });
  }
});

// 2. Get Employee Attendance
router.get('/employee/:email/attendance', verifyToken, async (req, res) => {
  try {
    const user = await getColovoUser(req.params.email);
    if (!user) return res.status(404).json({ success: false, message: 'Employee not found in Colovo Workspace' });

    const [attendances] = await colovoDb.query(
      'SELECT * FROM attendances WHERE user_id = ? ORDER BY date DESC LIMIT 50',
      [user.id]
    );

    res.json({ success: true, data: attendances });
  } catch (err) {
    console.error('[Sync API] Error fetching attendance:', err);
    res.status(500).json({ success: false, message: 'Server error fetching attendance' });
  }
});

// 3. Get Employee Leaves
router.get('/employee/:email/leaves', verifyToken, async (req, res) => {
  try {
    const user = await getColovoUser(req.params.email);
    if (!user) return res.status(404).json({ success: false, message: 'Employee not found in Colovo Workspace' });

    const [leaves] = await colovoDb.query(
      'SELECT * FROM leaves WHERE user_id = ? ORDER BY created_at DESC LIMIT 50',
      [user.id]
    );

    res.json({ success: true, data: leaves });
  } catch (err) {
    console.error('[Sync API] Error fetching leaves:', err);
    res.status(500).json({ success: false, message: 'Server error fetching leaves' });
  }
});

// 4. Push Notification to Employee's Dashboard
router.post('/employee/:email/notify', verifyToken, async (req, res) => {
  try {
    const { title, message } = req.body;
    if (!title || !message) {
      return res.status(400).json({ success: false, message: 'Title and message are required' });
    }

    const user = await getColovoUser(req.params.email);
    if (!user) return res.status(404).json({ success: false, message: 'Employee not found in Colovo Workspace' });

    // Format data as JSON for Laravel's notification structure
    const notificationData = JSON.stringify({
      title: title,
      message: message,
      source: 'React ERP'
    });

    const notificationId = crypto.randomUUID();
    const notificationType = 'App\\Notifications\\GeneralNotification'; // Adjust based on your Laravel app

    await colovoDb.query(
      'INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
      [notificationId, notificationType, 'App\\Models\\User', user.id, notificationData]
    );

    res.json({ success: true, message: 'Notification pushed successfully to Colovo Workspace' });
  } catch (err) {
    console.error('[Sync API] Error pushing notification:', err);
    res.status(500).json({ success: false, message: 'Server error pushing notification' });
  }
});

module.exports = router;
