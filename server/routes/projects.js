const express = require('express');
const router = express.Router();
const db = require('../config/db');
const { verifyToken } = require('../middleware/auth');

// Helper function to send notification for Project Assignment
async function sendProjectAssignmentNotification(assigned_to, projectName, triggeredBy, companyName) {
  if (!assigned_to) return;
  try {
    const title = 'New Project Assigned';
    const message = `You have been assigned to the project: "${projectName}"`;
    const type = 'project';
    const action_type = 'PROJECT_ASSIGNMENT';

    // 1. Save in ERP notifications table
    await db.promise.query(
      'INSERT INTO notifications (user_id, title, message, type, triggered_by, action_type, company_name) VALUES (?, ?, ?, ?, ?, ?, ?)',
      [assigned_to, title, message, type, triggeredBy || null, action_type, companyName || 'Hatbaliya']
    );

    // 2. Fetch user's email in ERP
    const [userRows] = await db.promise.query('SELECT email FROM user_identities WHERE id = ?', [assigned_to]);
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
          project_name: projectName
        });

        // 4. Insert into Colovo notifications table
        await colovoPromise.execute(
          'INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
          [notifId, 'App\\Notifications\\GeneralNotification', 'App\\Models\\User', colovoUserId, dataJson]
        );
        console.log('[Workspace Sync] Project notification synced to Colovo user ID:', colovoUserId);
      }
    }
  } catch (err) {
    console.error('[Notification Sync Error] Failed to process project notification:', err.message);
  }
}

// Helper function to send notification for Task Assignment
async function sendTaskAssignmentNotification(assigned_to, taskTitle, projectName, triggeredBy, companyName) {
  if (!assigned_to) return;
  try {
    const title = 'New Task Assigned';
    const message = `You have been assigned to the task: "${taskTitle}" under project: "${projectName}"`;
    const type = 'task';
    const action_type = 'TASK_ASSIGNMENT';

    // 1. Save in ERP notifications table
    await db.promise.query(
      'INSERT INTO notifications (user_id, title, message, type, triggered_by, action_type, company_name) VALUES (?, ?, ?, ?, ?, ?, ?)',
      [assigned_to, title, message, type, triggeredBy || null, action_type, companyName || 'Hatbaliya']
    );

    // 2. Fetch user's email in ERP
    const [userRows] = await db.promise.query('SELECT email FROM user_identities WHERE id = ?', [assigned_to]);
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
          task_title: taskTitle,
          project_name: projectName
        });

        // 4. Insert into Colovo notifications table
        await colovoPromise.execute(
          'INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
          [notifId, 'App\\Notifications\\GeneralNotification', 'App\\Models\\User', colovoUserId, dataJson]
        );
        console.log('[Workspace Sync] Task notification synced to Colovo user ID:', colovoUserId);
      }
    }
  } catch (err) {
    console.error('[Notification Sync Error] Failed to process task notification:', err.message);
  }
}

// Add created_at column to tasks if it doesn't exist
db.query(`ALTER TABLE tasks ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP`, (err) => {
  if (err && !err.message.includes('Duplicate')) {
    // MySQL 5.x doesn't support IF NOT EXISTS on ALTER, try a workaround
    db.query(`SHOW COLUMNS FROM tasks LIKE 'created_at'`, (err2, rows) => {
      if (!err2 && rows.length === 0) {
        db.query(`ALTER TABLE tasks ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP`, () => { });
      }
    });
  }
});

// Get all projects for dropdown (Auto-syncs live projects from Colovo Workspace)
router.get('/', async (req, res) => {
  try {
    const colovoDb = require('../config/db').colovoPromise;
    
    // 1. Fetch live projects from Colovo Workspace
    const [colovoProjects] = await colovoDb.query('SELECT title, description FROM projects');
    
    // 2. Fetch ERP projects
    const [erpProjects] = await db.promise.query('SELECT id, name, description FROM projects ORDER BY created_at DESC');
    
    const erpProjectMap = new Map(erpProjects.map(p => [p.name, p]));
    const results = [];
    
    // 3. Auto-sync missing Colovo projects into ERP
    for (const cp of colovoProjects) {
      const erpRecord = erpProjectMap.get(cp.title);
      if (erpRecord) {
        results.push({
          id: erpRecord.id,
          name: `${cp.title} (Colovo Workspace)`,
          description: erpRecord.description
        });
        erpProjectMap.delete(cp.title); // prevent duplicates
      } else {
        // Auto-sync into ERP
        try {
          const [insertRes] = await db.promise.query(
            'INSERT INTO projects (name, description, status, company_name) VALUES (?, ?, ?, ?)',
            [cp.title, cp.description, 'Ongoing', 'Colovo Workspace']
          );
          results.push({
            id: insertRes.insertId,
            name: `${cp.title} (Colovo Workspace)`,
            description: cp.description
          });
        } catch(syncErr) {
          console.error('Failed to auto-sync Colovo project:', cp.title, syncErr);
        }
      }
    }
    
    // 4. Add the remaining ERP projects
    for (const [name, erpRecord] of erpProjectMap.entries()) {
      results.push({
        id: erpRecord.id,
        name: erpRecord.name,
        description: erpRecord.description
      });
    }

    results.sort((a, b) => a.name.localeCompare(b.name));
    res.json({ success: true, data: results });

  } catch (err) {
    console.error('Error fetching Colovo projects:', err);
    res.status(500).json({ success: false, message: 'Error fetching projects' });
  }
});

// Get unified list: projects + tasks as one flat table
router.get('/all', (req, res) => {
  console.log('GET /api/projects/all hit');
  // First check if tasks.created_at exists
  db.query(`SHOW COLUMNS FROM tasks LIKE 'created_at'`, (err, cols) => {
    const hasCreatedAt = !err && cols && cols.length > 0;
    const taskCreatedAt = hasCreatedAt ? 't.created_at' : 'NULL';
    const query = `
      SELECT
        CONCAT('p_', p.id) as uid,
        'project' as type,
        p.id as source_id,
        p.name as title,
        p.description,
        p.deadline,
        p.status,
        p.assigned_to,
        COALESCE(e1.name, a1.name, d1.name) as assigned_name,
        NULL as project_name,
        p.created_at
      FROM projects p
      LEFT JOIN employees e1 ON p.assigned_to = e1.user_id
      LEFT JOIN admins a1 ON p.assigned_to = a1.user_id
      LEFT JOIN developers d1 ON p.assigned_to = d1.user_id
      UNION ALL
      SELECT
        CONCAT('t_', t.id) as uid,
        'task' as type,
        t.id as source_id,
        t.title,
        t.description,
        t.deadline,
        t.status,
        t.assigned_to,
        COALESCE(e2.name, a2.name, d2.name) as assigned_name,
        p2.name as project_name,
        ${taskCreatedAt} as created_at
      FROM tasks t
      LEFT JOIN employees e2 ON t.assigned_to = e2.user_id
      LEFT JOIN admins a2 ON t.assigned_to = a2.user_id
      LEFT JOIN developers d2 ON t.assigned_to = d2.user_id
      LEFT JOIN projects p2 ON t.project_id = p2.id
      ORDER BY created_at DESC
    `;
    db.query(query, (err, results) => {
      if (err) {
        console.error('Error in GET /api/projects/all:', err);
        return res.status(500).json({ success: false, message: 'Error fetching data', error: err.message });
      }
      res.json({ success: true, data: results });
    });
  });
});

// Create new project and sync to Colovo Workspace
router.post('/', verifyToken, async (req, res) => {
  const { name, description, deadline, assigned_to } = req.body;
  try {
    // 1. Create in ERP
    const [result] = await db.promise.query(
      'INSERT INTO projects (name, description, deadline, assigned_to) VALUES (?, ?, ?, ?)', 
      [name, description, deadline, assigned_to || null]
    );

    // 2. Send Project Assignment Notification
    if (assigned_to) {
      await sendProjectAssignmentNotification(assigned_to, name, req.user ? req.user.id : null, req.company_name);
    }

    // 3. Auto-sync to Colovo Workspace via API
    try {
      const axios = require('axios');
      let assigned_email = null;
      
      if (assigned_to) {
        // Find user email in ERP to send to Colovo API
        const [erpUser] = await db.promise.query('SELECT email FROM user_identities WHERE id = ?', [assigned_to]);
        if (erpUser.length > 0) {
          assigned_email = erpUser[0].email;
        }
      }

      await axios.post('http://127.0.0.1:8000/api/sync-project', {
        title: name,
        description: description,
        assigned_email: assigned_email
      }, {
        headers: { 'X-ERP-SECRET': process.env.ERP_SHARED_SECRET || 'default-erp-secret-12345' }
      });
    } catch (syncErr) {
      console.error('Failed to sync new ERP project to Colovo API:', syncErr.message);
    }

    res.json({ success: true, message: 'Project created and synced to Colovo', id: result.insertId });
  } catch (err) {
    console.error('Error creating project:', err);
    res.status(500).json({ success: false, message: 'Error creating project' });
  }
});

// Get tasks for a project or user
// NOTE: Must be defined BEFORE /:id routes to avoid Express matching 'tasks' as an :id
router.get('/tasks', (req, res) => {
  const { project_id, user_id } = req.query;
  let query = 'SELECT tasks.*, users.name as assigned_name FROM tasks JOIN users ON tasks.assigned_to = users.id';
  let params = [];

  if (project_id) {
    query += ' WHERE project_id = ?';
    params.push(project_id);
  } else if (user_id) {
    query += ' WHERE assigned_to = ?';
    params.push(user_id);
  }

  db.query(query, params, (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Error fetching tasks' });
    res.json({ success: true, data: results });
  });
});

// Create task and sync to Colovo Workspace
router.post('/tasks', verifyToken, async (req, res) => {
  const { project_id, assigned_to, title, description, deadline } = req.body;
  try {
    // 1. Insert into ERP
    const [result] = await db.promise.query(
      'INSERT INTO tasks (project_id, assigned_to, title, description, deadline) VALUES (?, ?, ?, ?, ?)',
      [project_id, assigned_to, title, description, deadline]
    );

    // Get Project Title
    let project_title = '';
    const [erpProj] = await db.promise.query('SELECT name FROM projects WHERE id = ?', [project_id]);
    if (erpProj.length > 0) {
      project_title = erpProj[0].name.replace(' (Colovo Workspace)', '');
    }

    // 2. Send Task Assignment Notification
    if (assigned_to) {
      await sendTaskAssignmentNotification(assigned_to, title, project_title, req.user ? req.user.id : null, req.company_name);
    }

    // 3. Auto-sync to Colovo Workspace via API
    try {
      const axios = require('axios');
      let assigned_email = null;
      
      // Get Assigned Email
      if (assigned_to) {
        const [erpUser] = await db.promise.query('SELECT email FROM user_identities WHERE id = ?', [assigned_to]);
        if (erpUser.length > 0) {
          assigned_email = erpUser[0].email;
        }
      }

      if (project_title && assigned_email) {
        await axios.post('http://127.0.0.1:8000/api/sync-task', {
          project_title: project_title,
          assigned_email: assigned_email,
          title: title,
          description: description,
          due_date: deadline
        }, {
          headers: { 'X-ERP-SECRET': process.env.ERP_SHARED_SECRET || 'default-erp-secret-12345' }
        });
      }
    } catch (syncErr) {
      console.error('Task sync error to Colovo API:', syncErr.message);
    }
    
    res.json({ success: true, message: 'Task assigned and synced', id: result.insertId });
  } catch (err) {
    console.error('Error creating task:', err);
    res.status(500).json({ success: false, message: 'Error creating task: ' + err.message });
  }
});

// Update task
// NOTE: Must be defined BEFORE PUT /:id to avoid 'tasks' being matched as :id
router.put('/tasks/:id', verifyToken, async (req, res) => {
  const { title, description, deadline, status, assigned_to } = req.body;
  try {
    // Check if assignment has changed
    const [existing] = await db.promise.query('SELECT title, project_id, assigned_to FROM tasks WHERE id = ?', [req.params.id]);
    const existingAssignee = existing.length > 0 ? existing[0].assigned_to : null;
    const taskTitle = title || (existing.length > 0 ? existing[0].title : '');
    const projectId = existing.length > 0 ? existing[0].project_id : null;

    const query = 'UPDATE tasks SET title=?, description=?, deadline=?, status=?, assigned_to=? WHERE id=?';
    await db.promise.query(query, [title, description, deadline, status, assigned_to || null, req.params.id]);

    if (assigned_to && String(assigned_to) !== String(existingAssignee)) {
      let project_title = '';
      if (projectId) {
        const [erpProj] = await db.promise.query('SELECT name FROM projects WHERE id = ?', [projectId]);
        if (erpProj.length > 0) {
          project_title = erpProj[0].name.replace(' (Colovo Workspace)', '');
        }
      }
      await sendTaskAssignmentNotification(assigned_to, taskTitle, project_title, req.user ? req.user.id : null, req.company_name);
    }

    res.json({ success: true, message: 'Task updated' });
  } catch (err) {
    console.error('Error updating task:', err);
    res.status(500).json({ success: false, message: 'Error updating task' });
  }
});

// Delete task
// NOTE: Must be defined BEFORE DELETE /:id to avoid 'tasks' being matched as :id
router.delete('/tasks/:id', verifyToken, (req, res) => {
  db.query('DELETE FROM tasks WHERE id = ?', [req.params.id], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: 'Error deleting task' });
    res.json({ success: true, message: 'Task deleted' });
  });
});

// Update project
router.put('/:id', verifyToken, async (req, res) => {
  const { name, description, deadline, status, assigned_to } = req.body;
  try {
    // Check if assignment has changed
    const [existing] = await db.promise.query('SELECT name, assigned_to FROM projects WHERE id = ?', [req.params.id]);
    const existingAssignee = existing.length > 0 ? existing[0].assigned_to : null;
    const projectName = name || (existing.length > 0 ? existing[0].name : '');

    const query = 'UPDATE projects SET name=?, description=?, deadline=?, status=?, assigned_to=? WHERE id=?';
    await db.promise.query(query, [name, description, deadline, status, assigned_to || null, req.params.id]);

    if (assigned_to && String(assigned_to) !== String(existingAssignee)) {
      await sendProjectAssignmentNotification(assigned_to, projectName, req.user ? req.user.id : null, req.company_name);
    }

    res.json({ success: true, message: 'Project updated' });
  } catch (err) {
    console.error('Error updating project:', err);
    res.status(500).json({ success: false, message: 'Error updating project' });
  }
});

// Delete project
router.delete('/:id', verifyToken, (req, res) => {
  db.query('DELETE FROM projects WHERE id = ?', [req.params.id], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: 'Error deleting project' });
    res.json({ success: true, message: 'Project deleted' });
  });
});

module.exports = router;
