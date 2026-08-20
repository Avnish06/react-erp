const express = require('express');
const router = express.Router();
const db = require('../config/db');
const { verifyToken, checkPermission } = require('../middleware/auth');
const fs = require('fs');
const path = require('path');

// Add reason column to attendance (local ERP database)
db.query(`ALTER TABLE attendance ADD COLUMN IF NOT EXISTS reason VARCHAR(255) DEFAULT NULL`, (err) => {
  if (err && !err.message.includes('Duplicate')) {
    db.query(`SHOW COLUMNS FROM attendance LIKE 'reason'`, (err2, rows) => {
      if (!err2 && rows.length === 0) {
        db.query(`ALTER TABLE attendance ADD COLUMN reason VARCHAR(255) DEFAULT NULL`, () => { });
      }
    });
  }
});

// Add reason column to attendances (Colovo Workspace database)
try {
  const colovoDb = require('../config/db').colovoPromise;
  colovoDb.query(`ALTER TABLE attendances ADD COLUMN IF NOT EXISTS reason VARCHAR(255) DEFAULT NULL`)
    .catch(err => {
      if (!err.message.includes('Duplicate')) {
        colovoDb.query(`SHOW COLUMNS FROM attendances LIKE 'reason'`)
          .then(([rows]) => {
            if (rows.length === 0) {
              colovoDb.query(`ALTER TABLE attendances ADD COLUMN reason VARCHAR(255) DEFAULT NULL`);
            }
          }).catch(() => {});
      }
    });
} catch (e) {
  console.error('[Attendance Migration Error]', e.message);
}

// Helper function to send notification for Attendance override/mark
async function sendAttendanceNotification(user_id, date, status, reason, triggeredBy, companyName) {
  if (!user_id) return;
  try {
    const formattedDate = new Date(date).toLocaleDateString('en-GB');
    const title = 'Attendance Updated by Admin';
    const message = `Your attendance status for ${formattedDate} has been set to "${status}"${reason ? ` (Reason: ${reason})` : ''}.`;
    const type = 'attendance';
    const action_type = 'ATTENDANCE_OVERRIDE';

    // 1. Save in ERP notifications table
    await db.promise.query(
      'INSERT INTO notifications (user_id, title, message, type, triggered_by, action_type, company_name) VALUES (?, ?, ?, ?, ?, ?, ?)',
      [user_id, title, message, type, triggeredBy || null, action_type, companyName || 'Hatbaliya']
    );

    // 2. Fetch user's email in ERP
    const [userRows] = await db.promise.query('SELECT email FROM users WHERE id = ?', [user_id]);
    if (userRows.length > 0) {
      const email = userRows[0].email;
      const { colovoPromise } = require('../config/db');
      const crypto = require('crypto');

      // 3. Find user in Colovo Database
      const [colovoUsers] = await colovoPromise.query('SELECT id FROM users WHERE email = ?', [email]);
      if (colovoUsers.length > 0) {
        const colovoUserId = colovoUsers[0].id;
        const notifId = crypto.randomUUID();
        const dataJson = JSON.stringify({
          title: title,
          message: message,
          type: type,
          date: date,
          status: status
        });

        // 4. Insert into Colovo notifications table
        await colovoPromise.execute(
          'INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
          [notifId, 'App\\Notifications\\GeneralNotification', 'App\\Models\\User', colovoUserId, dataJson]
        );
        console.log('[Workspace Sync] Attendance notification synced to Colovo user ID:', colovoUserId);
      }
    }
  } catch (err) {
    console.error('[Notification Sync Error] Failed to process attendance notification:', err.message);
  }
}



// Clock In
router.post('/clock-in', verifyToken, (req, res) => {
  const { user_id, employee_name, date, clock_in, image_capture } = req.body;

  // 1. Check if already clocked in today
  const checkQuery = 'SELECT * FROM attendance WHERE user_id = ? AND date = ?';
  db.query(checkQuery, [user_id, date], (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Server error' });
    if (results.length > 0) {
      return res.status(400).json({ success: false, message: 'Attendance already marked for today' });
    }

    // 2. Handle Image Capture
    let imageUrl = null;
    try {
      if (image_capture) {
        const base64Data = image_capture.replace(/^data:image\/\w+;base64,/, '');
        const buffer = Buffer.from(base64Data, 'base64');
        const filename = `attendance_${user_id}_${Date.now()}.png`;
        const uploadDir = path.join(__dirname, '../uploads/attendance');

        // Ensure directory exists
        if (!fs.existsSync(uploadDir)) {
          fs.mkdirSync(uploadDir, { recursive: true });
        }

        const uploadPath = path.join(uploadDir, filename);
        fs.writeFileSync(uploadPath, buffer);
        const host = req.get('host');
        imageUrl = `${req.protocol}://${host}/uploads/attendance/${filename}`;
        console.log('Image saved successfully to:', uploadPath);
      }
    } catch (imageErr) {
      console.error('Error processing/saving image:', imageErr);
      return res.status(500).json({ success: false, message: 'Error processing image: ' + imageErr.message });
    }

    const query = 'INSERT INTO attendance (user_id, employee_name, date, clock_in, image_url, status) VALUES (?, ?, ?, ?, ?, "Present")';
    db.query(query, [user_id, employee_name, date, clock_in, imageUrl], async (err, result) => {
      if (err) {
        console.error('Database Error during clock-in:', err);
        return res.status(500).json({ success: false, message: 'Database error: ' + err.message });
      }

      // Sync to Colovo Database
      try {
        const [userRows] = await db.promise().query('SELECT email FROM users WHERE id = ?', [user_id]);
        if (userRows.length > 0) {
          const email = userRows[0].email;
          const colovoDb = require('../config/db').colovoPromise;
          const [colovoUsers] = await colovoDb.query('SELECT id FROM users WHERE email = ?', [email]);
          if (colovoUsers.length > 0) {
            const colovoUserId = colovoUsers[0].id;
            await colovoDb.query(
              'INSERT INTO attendances (user_id, date, clock_in, check_in_photo, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
              [colovoUserId, date, clock_in, imageUrl, 'present']
            );
            console.log('[Workspace Sync] Clock-in synced to Colovo user ID:', colovoUserId);
          }
        }
      } catch (syncErr) {
        console.error('[Workspace Sync Error] Failed to sync clock-in:', syncErr.message);
      }

      res.json({ success: true, message: 'Clocked in successfully', id: result.insertId });
    });
  });
});

// Ensure clock_out_image_url column exists
db.query(`ALTER TABLE attendance ADD COLUMN IF NOT EXISTS clock_out_image_url VARCHAR(255) DEFAULT NULL`, (err) => {
  if (err) console.error('[Attendance] Could not add clock_out_image_url column:', err.message);
});

// Clock Out
router.post('/clock-out', verifyToken, (req, res) => {
  const { user_id, date, clock_out, image_capture } = req.body;

  let imageUrl = null;
  try {
    if (image_capture) {
      const base64Data = image_capture.replace(/^data:image\/\w+;base64,/, '');
      const buffer = Buffer.from(base64Data, 'base64');
      const filename = `clockout_${user_id}_${Date.now()}.png`;
      const uploadDir = path.join(__dirname, '../uploads/attendance');

      if (!fs.existsSync(uploadDir)) {
        fs.mkdirSync(uploadDir, { recursive: true });
      }

      const uploadPath = path.join(uploadDir, filename);
      fs.writeFileSync(uploadPath, buffer);
      const host = req.get('host');
      imageUrl = `${req.protocol}://${host}/uploads/attendance/${filename}`;
    }
  } catch (imageErr) {
    console.error('Error processing clock-out image:', imageErr);
    return res.status(500).json({ success: false, message: 'Error processing image' });
  }

  const query = 'UPDATE attendance SET clock_out = ?, clock_out_image_url = ? WHERE user_id = ? AND date = ?';
  db.query(query, [clock_out, imageUrl, user_id, date], async (err) => {
    if (err) return res.status(500).json({ success: false, message: 'Error clocking out' });

    // Sync to Colovo Database
    try {
      const [userRows] = await db.promise().query('SELECT email FROM users WHERE id = ?', [user_id]);
      if (userRows.length > 0) {
        const email = userRows[0].email;
        const colovoDb = require('../config/db').colovoPromise;
        const [colovoUsers] = await colovoDb.query('SELECT id FROM users WHERE email = ?', [email]);
        if (colovoUsers.length > 0) {
          const colovoUserId = colovoUsers[0].id;
          await colovoDb.query(
            'UPDATE attendances SET clock_out = ?, check_out_photo = ?, updated_at = NOW() WHERE user_id = ? AND date = ?',
            [clock_out, imageUrl, colovoUserId, date]
          );
          console.log('[Workspace Sync] Clock-out synced to Colovo user ID:', colovoUserId);
        }
      }
    } catch (syncErr) {
      console.error('[Workspace Sync Error] Failed to sync clock-out:', syncErr.message);
    }

    res.json({ success: true, message: 'Clocked out successfully' });
  });
});

// Mark employee attendance (Admin/Super Admin/Developer)
router.post('/mark', verifyToken, async (req, res) => {
  const { user_id, date, status, reason } = req.body;
  const { role } = req.user;

  try {
    const isElevated = role === 'Super Admin' || role === 'Developer' || role === 'Admin';
    if (!isElevated) {
      return res.status(403).json({ success: false, message: 'Access Denied' });
    }

    if (!user_id || !date || !status) {
      return res.status(400).json({ success: false, message: 'Missing required fields' });
    }

    // 1. Fetch user email and name to sync with Colovo (Query from users view to get both fields)
    const [userRows] = await db.promise.query('SELECT email, name FROM users WHERE id = ?', [user_id]);
    if (userRows.length === 0) {
      return res.status(404).json({ success: false, message: 'Employee not found' });
    }
    const { email, name: employeeName } = userRows[0];

    // 2. Mark in local ERP attendance table
    const [existingErp] = await db.promise.query('SELECT id FROM attendance WHERE user_id = ? AND date = ?', [user_id, date]);
    if (existingErp.length > 0) {
      await db.promise.query(
        'UPDATE attendance SET status = ?, reason = ? WHERE user_id = ? AND date = ?',
        [status, reason || null, user_id, date]
      );
    } else {
      await db.promise.query(
        'INSERT INTO attendance (user_id, employee_name, date, status, reason) VALUES (?, ?, ?, ?, ?)',
        [user_id, employeeName || 'Employee', date, status, reason || null]
      );
    }

    // 3. Sync to Colovo Database
    try {
      const colovoDb = require('../config/db').colovoPromise;
      const [colovoUsers] = await colovoDb.query('SELECT id FROM users WHERE email = ?', [email]);
      if (colovoUsers.length > 0) {
        const colovoUserId = colovoUsers[0].id;
        const [existingColovo] = await colovoDb.query('SELECT id FROM attendances WHERE user_id = ? AND date = ?', [colovoUserId, date]);
        if (existingColovo.length > 0) {
          await colovoDb.query(
            'UPDATE attendances SET status = ?, reason = ? WHERE user_id = ? AND date = ?',
            [status.toLowerCase(), reason || null, colovoUserId, date]
          );
        } else {
          await colovoDb.query(
            'INSERT INTO attendances (user_id, date, status, reason, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
            [colovoUserId, date, status.toLowerCase(), reason || null]
          );
        }
        console.log('[Workspace Sync] Attendance marked and synced to Colovo user ID:', colovoUserId);
      }
    } catch (syncErr) {
      console.error('[Workspace Sync Error] Failed to sync marked attendance:', syncErr.message);
    }

    // 4. Send Notification
    await sendAttendanceNotification(user_id, date, status, reason, req.user ? req.user.id : null, req.company_name);

    res.json({ success: true, message: `Attendance marked as ${status} successfully` });
  } catch (err) {
    console.error('Error marking attendance:', err);
    res.status(500).json({ success: false, message: 'Server error marking attendance' });
  }
});

// Get attendance for a specific user
router.get('/:user_id', verifyToken, async (req, res) => {
  try {
    const userId = req.params.user_id;
    // 1. Get user email from management_system
    const [idRows] = await require('../config/db').promise.query('SELECT email FROM users WHERE id = ?', [userId]);
    if (idRows.length === 0) return res.status(404).json({ success: false, message: 'User not found' });
    const userEmail = idRows[0].email;

    const colovoDb = require('../config/db').colovoPromise;
    // 2. Get colovo user ID
    const [colovoUsers] = await colovoDb.query('SELECT id FROM users WHERE email = ?', [userEmail]);
    if (colovoUsers.length === 0) return res.json({ success: true, data: [] });
    const colovoUserId = colovoUsers[0].id;

    const query = `
      SELECT 
          a.id, 
          u.name as employee_name,
          a.date,
          a.clock_in,
          a.clock_out,
          a.status,
          a.reason,
          a.check_in_photo as image_url,
          a.check_out_photo as clock_out_image_url
      FROM attendances a
      JOIN users u ON a.user_id = u.id
      WHERE a.user_id = ?
      ORDER BY a.date DESC, a.clock_in DESC
    `;
    const [results] = await colovoDb.query(query, [colovoUserId]);
    res.json({ success: true, data: results });
  } catch (err) {
    console.error('Error fetching attendance for user from colovo:', err);
    res.status(500).json({ success: false, message: 'Error fetching attendance' });
  }
});

// Get all attendance (Admin)
router.get('/', verifyToken, checkPermission('view_attendance'), async (req, res) => {
  const query = `
      SELECT 
          a.id, 
          u.name as employee_name,
          a.date,
          a.clock_in,
          a.clock_out,
          a.status,
          a.reason,
          a.check_in_photo as image_url,
          a.check_out_photo as clock_out_image_url,
          u.role as role_name
      FROM attendances a
      LEFT JOIN users u ON a.user_id = u.id
      ORDER BY a.date DESC, a.clock_in DESC
  `;
  try {
    const colovoDb = require('../config/db').colovoPromise;
    const [results] = await colovoDb.query(query);
    res.json({ success: true, data: results });
  } catch (err) {
    console.error('Error fetching attendance from colovo:', err);
    res.status(500).json({ success: false, message: 'Error fetching attendance' });
  }
});

// Update attendance record (Super Admin/Developer or Admin with override)
router.put('/:id', verifyToken, async (req, res) => {
  const { date, clock_in, clock_out, status, reason } = req.body;
  const { id } = req.params;
  const { role } = req.user;

  try {
    const isElevated = role === 'Super Admin' || role === 'Developer';

    if (!isElevated) {
      console.log(`Non-elevated user attempt. Role: ${role}`);
      if (role === 'Admin') {
        const [settingsRows] = await db.promise.query('SELECT setting_value FROM system_settings WHERE setting_key = "override_attendance"');
        const canOverride = settingsRows.length > 0 && settingsRows[0].setting_value === 'true';
        console.log(`Admin override check: canOverride=${canOverride}`);

        if (!canOverride) {
          return res.status(403).json({ success: false, message: 'Access Denied: Attendance override is disabled.' });
        }
      } else {
        return res.status(403).json({ success: false, message: 'Access Denied: Insufficient Permissions' });
      }
    }

    // 1. Update in local ERP attendance table
    const query = 'UPDATE attendance SET date = ?, clock_in = ?, clock_out = ?, status = ?, reason = ? WHERE id = ?';
    db.query(query, [date, clock_in, clock_out, status, reason || null, id], async (err, result) => {
      if (err) {
        console.error('Error updating attendance:', err);
        return res.status(500).json({ success: false, message: 'Error updating attendance record' });
      }

      // 2. Sync changes to Colovo database
      try {
        const [attRows] = await db.promise.query('SELECT user_id, date FROM attendance WHERE id = ?', [id]);
        if (attRows.length > 0) {
          const { user_id, date: attDate } = attRows[0];
          const [userRows] = await db.promise.query('SELECT email FROM users WHERE id = ?', [user_id]);
          if (userRows.length > 0) {
            const email = userRows[0].email;
            const colovoDb = require('../config/db').colovoPromise;
            const [colovoUsers] = await colovoDb.query('SELECT id FROM users WHERE email = ?', [email]);
            if (colovoUsers.length > 0) {
              const colovoUserId = colovoUsers[0].id;
              
              const [existingColovo] = await colovoDb.query('SELECT id FROM attendances WHERE user_id = ? AND date = ?', [colovoUserId, date]);
              if (existingColovo.length > 0) {
                await colovoDb.query(
                  'UPDATE attendances SET status = ?, reason = ?, clock_in = ?, clock_out = ? WHERE user_id = ? AND date = ?',
                  [status.toLowerCase(), reason || null, clock_in || null, clock_out || null, colovoUserId, date]
                );
              } else {
                await colovoDb.query(
                  'INSERT INTO attendances (user_id, date, status, reason, clock_in, clock_out, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())',
                  [colovoUserId, date, status.toLowerCase(), reason || null, clock_in || null, clock_out || null]
                );
              }
              console.log('[Workspace Sync] Attendance update synced to Colovo user ID:', colovoUserId);
            }
          }

          // 3. Send Notification
          await sendAttendanceNotification(user_id, date, status, reason, req.user ? req.user.id : null, req.company_name);
        }
      } catch (syncErr) {
        console.error('[Workspace Sync Error] Failed to sync attendance update:', syncErr.message);
      }

      res.json({ success: true, message: 'Attendance record updated successfully' });
    });
  } catch (err) {
    console.error('Error in attendance update:', err);
    res.status(500).json({ success: false, message: 'Server error' });
  }
});

module.exports = router;
