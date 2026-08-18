const express = require('express');
const router = express.Router();
const db = require('../config/db');
const { verifyToken } = require('../middleware/auth');
const colovoDb = require('../config/db').colovoPromise;

// Ensure table exists
db.query(`CREATE TABLE IF NOT EXISTS daily_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  work_summary TEXT,
  tasks_completed TEXT,
  challenges TEXT,
  plan_tomorrow TEXT,
  mood VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)`, (err) => {
  if (err) console.error('Error creating daily_reports table:', err);
});

// @route   POST /api/daily-reports
// @desc    Submit a new daily report
// @access  Private
router.post('/', verifyToken, (req, res) => {
  const { workSummary, tasksCompleted, challenges, planTomorrow, mood } = req.body;
  const userId = req.user.id; // From verifyToken middleware

  if (!workSummary || workSummary.trim().split(/\s+/).length < 10) {
    return res.status(400).json({ success: false, message: 'Work summary must be at least 10 words' });
  }

  const query = `
    INSERT INTO daily_reports (user_id, work_summary, tasks_completed, challenges, plan_tomorrow, mood)
    VALUES (?, ?, ?, ?, ?, ?)
  `;
  const values = [userId, workSummary, tasksCompleted || '', challenges || '', planTomorrow || '', mood || ''];

  db.query(query, values, (err, result) => {
    if (err) {
      console.error('[Submit Daily Report] Error:', err);
      return res.status(500).json({ success: false, message: 'Server error saving report' });
    }

    res.status(201).json({
      success: true,
      message: 'Daily report submitted successfully',
      reportId: result.insertId
    });
  });
});

// @route   GET /api/daily-reports/all
// @desc    Get all daily reports (Admin/Super Admin/Developer only)
// @access  Private
router.get('/all', verifyToken, async (req, res) => {
  const allowedRoles = ['Admin', 'Super Admin', 'Developer'];
  if (!allowedRoles.includes(req.user.role)) {
    return res.status(403).json({ success: false, message: 'Not authorized' });
  }

  try {
    // 1. Fetch ERP Daily Reports
    const erpQuery = `
      SELECT dr.*, COALESCE(u.name, ui.email) as employee_name, ui.email
      FROM daily_reports dr
      LEFT JOIN user_identities ui ON dr.user_id = ui.id
      LEFT JOIN users u ON dr.user_id = u.id
      ORDER BY dr.created_at DESC
    `;
    const [erpResults] = await db.promise.query(erpQuery);

    const formattedErp = erpResults.map(r => ({
      id: `erp-${r.id}`,
      employee_name: r.employee_name,
      email: r.email,
      work_summary: r.work_summary,
      tasks_completed: r.tasks_completed,
      challenges: r.challenges,
      plan_tomorrow: r.plan_tomorrow,
      mood: r.mood,
      created_at: r.created_at,
      source: 'erp'
    }));

    // 2. Fetch Colovo Daily Reports
    let formattedColovo = [];
    try {
      const colovoQuery = `
        SELECT dr.*, u.name as employee_name, u.email
        FROM daily_reports dr
        JOIN users u ON dr.user_id = u.id
        ORDER BY dr.created_at DESC
      `;
      const [colovoResults] = await colovoDb.query(colovoQuery);
      
      formattedColovo = colovoResults.map(r => ({
        id: `colovo-${r.id}`,
        employee_name: `${r.employee_name} (Colovo)`,
        email: r.email,
        work_summary: 'Submitted via Colovo Workspace',
        tasks_completed: r.tasks_completed,
        challenges: r.challenges,
        plan_tomorrow: r.plan_tomorrow,
        mood: null,
        created_at: r.created_at,
        source: 'colovo'
      }));
    } catch (colovoErr) {
      console.error('[Get All Daily Reports] Colovo fetch failed:', colovoErr.message);
    }

    // 3. Merge and Sort
    const combined = [...formattedErp, ...formattedColovo].sort(
      (a, b) => new Date(b.created_at) - new Date(a.created_at)
    );

    res.json({ success: true, reports: combined });
  } catch (err) {
    console.error('[Get All Daily Reports] Error:', err);
    res.status(500).json({ success: false, message: 'Server error fetching reports' });
  }
});

// @route   GET /api/daily-reports
// @desc    Get user's daily reports history (including Colovo Workspace)
// @access  Private
router.get('/', verifyToken, async (req, res) => {
  const userId = req.user.id;

  try {
    // 1. Fetch ERP Daily Reports
    const erpQuery = `SELECT * FROM daily_reports WHERE user_id = ? ORDER BY created_at DESC`;
    const [erpResults] = await db.promise.query(erpQuery, [userId]);

    // Format ERP reports
    let allReports = erpResults.map(report => ({
      ...report,
      source: 'erp'
    }));

    // 2. Fetch User Email from ERP to find them in Colovo
    const emailQuery = `SELECT email FROM user_identities WHERE id = ?`;
    const [emailResults] = await db.promise.query(emailQuery, [userId]);
    
    if (emailResults.length > 0) {
      const email = emailResults[0].email;
      
      try {
        // 3. Find User in Colovo DB
        const [colovoUsers] = await colovoDb.query('SELECT id FROM users WHERE email = ? LIMIT 1', [email]);
        
        if (colovoUsers.length > 0) {
          const colovoUserId = colovoUsers[0].id;
          
          // 4. Fetch Colovo Daily Reports
          const [colovoReports] = await colovoDb.query('SELECT * FROM daily_reports WHERE user_id = ? ORDER BY created_at DESC', [colovoUserId]);
          
          // Format Colovo reports to match ERP format
          const formattedColovoReports = colovoReports.map(report => ({
            id: `colovo-${report.id}`,
            user_id: userId,
            work_summary: 'Submitted via Colovo Workspace',
            tasks_completed: report.tasks_completed,
            challenges: report.challenges,
            plan_tomorrow: report.plan_tomorrow,
            mood: null,
            created_at: report.created_at,
            source: 'colovo'
          }));

          allReports = [...allReports, ...formattedColovoReports];
        }
      } catch (colovoErr) {
        console.error('[Get My Daily Reports] Colovo fetch failed:', colovoErr.message);
      }
    }

    // Sort combined reports by created_at descending
    allReports.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

    res.json({
      success: true,
      reports: allReports
    });

  } catch (err) {
    console.error('[Get Daily Reports] Error:', err);
    return res.status(500).json({ success: false, message: 'Server error fetching reports' });
  }
});

module.exports = router;
