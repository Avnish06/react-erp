const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');
const dotenv = require('dotenv');
const path = require('path');

dotenv.config({ path: path.join(__dirname, '.env') });

const authRoutes = require('./routes/auth');
const financeRoutes = require('./routes/finance');
const employeeRoutes = require('./routes/employees');
const departmentRoutes = require('./routes/departments');
const attendanceRoutes = require('./routes/attendance');
const leaveRoutes = require('./routes/leaves');
const payrollRoutes = require('./routes/payroll');
const projectRoutes = require('./routes/projects');
const settingsRoutes = require('./routes/settings');
const auditRoutes = require('./routes/audit');
const usersRoutes = require('./routes/users');
const vendorRoutes = require('./routes/vendors');
const reportsRoutes = require('./routes/reports');
const chatbotRoutes = require('./routes/chatbot');
const db = require('./config/db');

const app = express();
app.use(cors());
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ limit: '50mb', extended: true }));


// Global Company Middleware
app.use((req, res, next) => {
  req.company_name = req.headers['x-company-name'] || 'Hatbaliya';
  next();
});

app.use('/api/auth', authRoutes);
app.use('/api/finance', financeRoutes);
app.use('/api/employees', employeeRoutes);
app.use('/api/departments', departmentRoutes);
app.use('/api/settings', settingsRoutes);
app.use('/api/audit', auditRoutes);
app.use('/api/users', usersRoutes);
app.use('/api/vendors', vendorRoutes);
app.use('/api/reports', reportsRoutes);
app.use('/api/chatbot', chatbotRoutes);
app.use('/api/tickets', require('./routes/tickets'));
app.use('/api/notifications', require('./routes/notifications'));
app.use('/api/policies', require('./routes/policies'));
app.use('/api/backup', require('./routes/backup'));
app.use('/api/roles', require('./routes/roles'));
app.use('/api/dashboard', require('./routes/dashboard'));
app.use('/api/invoices', require('./routes/invoices'));
app.use('/api/deductions', require('./routes/deductions'));
app.use('/api/reports', require('./routes/reports'));
app.use('/api/expenditures', require('./routes/expenditures'));
app.use('/api/employee-stats', require('./routes/employeeStats'));
app.use('/api/daily-reports', require('./routes/daily_reports'));
app.use('/api/onboarding', require('./routes/onboarding'));
app.use('/api/offboarding', require('./routes/offboarding'));
app.use('/api/search', require('./routes/search'));
app.use('/api/leads', require('./routes/leads'));
app.use('/api/customers', require('./routes/customers'));
app.use('/api/deals', require('./routes/deals'));
app.use('/api/communication', require('./routes/communication'));
app.use('/api/crm-dashboard', require('./routes/crmDashboard'));
app.use('/api/crm-reports', require('./routes/crmReports'));
app.use('/api/task-reports', require('./routes/taskReports'));
app.use('/api/client-management', require('./routes/clientManagement'));
app.use('/api/exams', require('./routes/exams'));
app.use('/api/assets', require('./routes/assets'));
app.use('/api/exam-schedules', require('./routes/exam-schedules'));
app.use('/api/results', require('./routes/results'));
app.use('/api/grades', require('./routes/grades'));
app.use('/api/face', require('./routes/face'));
app.use('/api/wfh', require('./routes/wfh'));
app.use('/api/attendance', require('./routes/attendance'));
app.use('/api/leaves', require('./routes/leaves'));
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Conflicting explicit string removed to allow React's dist/index.html to render on the root domain

const connectDB = require('./config/db');

// Connect to MongoDB
connectDB();

// Automated Background Job: Clean up unresponded pending registrations older than 7 days
// TODO: Refactor cron jobs to use Mongoose Models instead of SQL
setInterval(() => {
  console.log('[Cron] Running daily cleanup of stale pending registrations (Pending Migration to MongoDB)...');
}, 24 * 60 * 60 * 1000); // 24 hours

// Serve Frontend
app.use(express.static(path.join(__dirname, 'public')));

app.get(/^(.*)$/, (req, res) => {
  const indexPath = path.resolve(__dirname, 'public', 'index.html');
  const fs = require('fs');
  if (fs.existsSync(indexPath)) {
    res.sendFile(indexPath);
  } else {
    res.status(200).send(`
      <div style="font-family: sans-serif; padding: 40px; text-align: center;">
        <h1 style="color: #e63946;">Frontend Build Missing!</h1>
        <p>The Express backend is <strong>running successfully</strong>, but it cannot find the built React frontend files.</p>
        <p>It is looking for: <code>${indexPath}</code></p>
        <div style="background: #f1faee; padding: 20px; border-radius: 8px; max-width: 600px; margin: 20px auto; text-align: left;">
          <h3 style="margin-top: 0;">How to fix this:</h3>
          <ol style="line-height: 1.6;">
            <li>On your local PC, open a terminal in the <strong>server</strong> folder.</li>
            <li>Run the command: <strong><code>npm run build</code></strong></li>
            <li>Make sure a <strong><code>public</code></strong> folder is created inside the <code>server</code> directory.</li>
            <li>Zip your project again (ensure the new <code>public</code> folder is included) and upload to Hostinger.</li>
          </ol>
        </div>
      </div>
    `);
  }
});

const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT} in ${process.env.NODE_ENV || 'development'} mode`);
});

module.exports = app;
