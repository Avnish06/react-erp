const express = require('express');
const router = express.Router();
const db = require('../config/db');
const axios = require('axios');

const COLOVO_URL    = process.env.COLOVO_WORKSPACE_URL || 'http://127.0.0.1:8000';
const ERP_SECRET    = process.env.ERP_SHARED_SECRET    || 'default-erp-secret-12345';

// Get payroll history for a user
router.get('/:user_id', (req, res) => {
  db.query('SELECT * FROM payroll WHERE user_id = ? ORDER BY month_year DESC', [req.params.user_id], (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Error fetching payroll' });
    res.json({ success: true, data: results });
  });
});

// Get all payroll (Admin)
router.get('/', (req, res) => {
  const query = `
        SELECT payroll.*, users.name as employee_name 
        FROM payroll 
        JOIN users ON payroll.user_id = users.id 
        ORDER BY payroll.month_year DESC
    `;
  db.query(query, (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Error fetching payroll' });
    res.json({ success: true, data: results });
  });
});

// Generate/Run Payroll for a user
router.post('/generate', (req, res) => {
  const { user_id, basic_salary, hra, da, bonus, deductions, month_year } = req.body;
  const net_salary = parseFloat(basic_salary) + parseFloat(hra) + parseFloat(da) + parseFloat(bonus) - parseFloat(deductions);
  const payment_date = new Date().toISOString().split('T')[0];

  const query = 'INSERT INTO payroll (user_id, basic_salary, hra, da, bonus, deductions, net_salary, month_year, payment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
  db.query(query, [user_id, basic_salary, hra, da, bonus, deductions, net_salary, month_year, payment_date], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: 'Error generating payroll' });
    
    // Connect to Finance Module
    const financeQuery = 'INSERT INTO finance_transactions (type, category, amount, currency, exchange_rate, amount_base, date, description, is_recurring) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
    db.query(financeQuery, ['expense', 'Salary', net_salary, 'INR', 1, net_salary, payment_date, `Payroll for ${month_year} (User ${user_id})`, false], (fErr) => {
      if (fErr) console.error('Error syncing payroll to finance:', fErr);
    });

    // --- Sync payslip to Colovo Workspace ---
    // Look up the employee email from ERP then push to Colovo
    db.query(
      `SELECT ui.email FROM user_identities ui WHERE ui.id = ? LIMIT 1`,
      [user_id],
      (emailErr, emailRows) => {
        if (!emailErr && emailRows.length > 0) {
          const employeeEmail = emailRows[0].email;
          axios.post(`${COLOVO_URL}/api/sync-payroll`, {
            employee_email : employeeEmail,
            month          : month_year,
            salary         : basic_salary,
            bonus          : bonus,
            deductions     : deductions,
            net_salary     : net_salary,
            status         : 'paid'
          }, {
            headers: { 'X-ERP-SECRET': ERP_SECRET }
          }).then(() => {
            console.log(`[Payroll Sync] Payslip for ${employeeEmail} (${month_year}) pushed to Colovo Workspace.`);
          }).catch(syncErr => {
            console.error('[Payroll Sync] Failed to push payslip to Colovo Workspace:', syncErr.message);
          });
        }
      }
    );
    // -----------------------------------------

    res.json({ success: true, message: 'Payroll generated and synced to Finance & Colovo Workspace', id: result.insertId });
  });
});

// Update Payroll Record
router.put('/:id', (req, res) => {
  const { basic_salary, hra, da, bonus, deductions, month_year, user_id } = req.body;
  const net_salary = parseFloat(basic_salary) + parseFloat(hra) + parseFloat(da) + parseFloat(bonus) - parseFloat(deductions);

  db.query('SELECT user_id, month_year FROM payroll WHERE id = ?', [req.params.id], (err, rows) => {
    if (err || rows.length === 0) return res.status(500).json({ success: false, message: 'Error finding payroll' });
    
    const old_user_id = rows[0].user_id;
    const old_month_year = rows[0].month_year;
    
    const query = 'UPDATE payroll SET basic_salary=?, hra=?, da=?, bonus=?, deductions=?, net_salary=?, month_year=? WHERE id=?';
    db.query(query, [basic_salary, hra, da, bonus, deductions, net_salary, month_year, req.params.id], (err, result) => {
      if (err) return res.status(500).json({ success: false, message: 'Error updating payroll' });
      
      // Update the corresponding finance transaction
      const oldDesc = `Payroll for ${old_month_year} (User ${old_user_id})`;
      const newDesc = `Payroll for ${month_year} (User ${user_id || old_user_id})`;
      db.query('UPDATE finance_transactions SET amount=?, amount_base=?, description=? WHERE description=?', [net_salary, net_salary, newDesc, oldDesc]);
      
      // --- Sync update to Colovo Workspace ---
      db.query(`SELECT ui.email FROM user_identities ui WHERE ui.id = ? LIMIT 1`, [user_id || old_user_id], async (emailErr, emailRows) => {
        if (!emailErr && emailRows.length > 0) {
          try {
            const employeeEmail = emailRows[0].email;
            const colovoDb = require('../config/db').colovoPromise;
            const [colovoUsers] = await colovoDb.query('SELECT id FROM users WHERE email = ?', [employeeEmail]);
            if (colovoUsers.length > 0) {
              const colovoUserId = colovoUsers[0].id;
              await colovoDb.query(
                'UPDATE payrolls SET salary = ?, bonus = ?, deductions = ?, net_salary = ?, month = ?, updated_at = NOW() WHERE user_id = ? AND month = ?',
                [basic_salary, bonus, deductions, net_salary, month_year, colovoUserId, old_month_year]
              ).catch(async () => {
                // fallback if table is named payroll
                await colovoDb.query(
                  'UPDATE payroll SET salary = ?, bonus = ?, deductions = ?, net_salary = ?, month = ?, updated_at = NOW() WHERE user_id = ? AND month = ?',
                  [basic_salary, bonus, deductions, net_salary, month_year, colovoUserId, old_month_year]
                );
              });
            }
          } catch (syncErr) {
            console.error('[Payroll Sync Error] Failed to update payslip in Colovo:', syncErr.message);
          }
        }
      });
      // -----------------------------------------

      res.json({ success: true, message: 'Payroll updated successfully' });
    });
  });
});

// Delete Payroll Record
router.delete('/:id', (req, res) => {
  db.query('SELECT user_id, month_year FROM payroll WHERE id = ?', [req.params.id], (err, rows) => {
    if (err || rows.length === 0) return res.status(500).json({ success: false, message: 'Error finding payroll' });
    
    const { user_id, month_year } = rows[0];
    
    db.query('DELETE FROM payroll WHERE id = ?', [req.params.id], (err, result) => {
      if (err) return res.status(500).json({ success: false, message: 'Error deleting payroll' });
      
      // Delete the corresponding finance transaction
      const desc = `Payroll for ${month_year} (User ${user_id})`;
      db.query('DELETE FROM finance_transactions WHERE description=?', [desc]);
      
      // --- Sync delete to Colovo Workspace ---
      db.query(`SELECT ui.email FROM user_identities ui WHERE ui.id = ? LIMIT 1`, [user_id], async (emailErr, emailRows) => {
        if (!emailErr && emailRows.length > 0) {
          try {
            const employeeEmail = emailRows[0].email;
            const colovoDb = require('../config/db').colovoPromise;
            const [colovoUsers] = await colovoDb.query('SELECT id FROM users WHERE email = ?', [employeeEmail]);
            if (colovoUsers.length > 0) {
              const colovoUserId = colovoUsers[0].id;
              await colovoDb.query(
                'DELETE FROM payrolls WHERE user_id = ? AND month = ?',
                [colovoUserId, month_year]
              ).catch(async () => {
                await colovoDb.query(
                  'DELETE FROM payroll WHERE user_id = ? AND month = ?',
                  [colovoUserId, month_year]
                );
              });
            }
          } catch (syncErr) {
            console.error('[Payroll Sync Error] Failed to delete payslip in Colovo:', syncErr.message);
          }
        }
      });
      // -----------------------------------------

      res.json({ success: true, message: 'Payroll deleted successfully' });
    });
  });
});

// Get salary structure for a user
router.get('/structure/:user_id', (req, res) => {
  db.query('SELECT * FROM salary_structures WHERE user_id = ?', [req.params.user_id], (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Error fetching salary structure' });
    res.json({ success: true, data: results[0] || null });
  });
});

// Calculate salary and automatic deductions based on attendance/absents
router.get('/calculate/:user_id/:month_year', (req, res) => {
  const { user_id, month_year } = req.params;
  
  // 1. Fetch salary structure
  db.query('SELECT * FROM salary_structures WHERE user_id = ?', [user_id], (err, structResults) => {
    if (err) return res.status(500).json({ success: false, message: 'Error fetching salary structure' });
    
    const structure = structResults[0] || { basic_salary: 0, hra: 0, da: 0, bonus: 0, deductions: 0 };
    const basic = parseFloat(structure.basic_salary || 0);
    const defaultDeductions = parseFloat(structure.deductions || 0);
    
    // 2. Fetch absent count for the specific month (month_year: YYYY-MM)
    const attendanceQuery = `
      SELECT COUNT(*) as absent_days 
      FROM attendance 
      WHERE user_id = ? 
        AND status = 'Absent' 
        AND date LIKE ?
    `;
    const datePattern = `${month_year}%`;
    
    db.query(attendanceQuery, [user_id, datePattern], (attErr, attResults) => {
      if (attErr) return res.status(500).json({ success: false, message: 'Error fetching attendance' });
      
      const absentDays = attResults[0] ? attResults[0].absent_days : 0;
      let calculatedDeduction = defaultDeductions;
      
      // If absent more than 2 days, deduct salary for the extra days (assuming 30 days in a month)
      if (absentDays > 2) {
        const extraAbsents = absentDays - 2;
        const dailyRate = basic / 30;
        calculatedDeduction = defaultDeductions + (extraAbsents * dailyRate);
      }
      
      res.json({
        success: true,
        data: {
          basic_salary: structure.basic_salary || 0,
          hra: structure.hra || 0,
          da: structure.da || 0,
          bonus: structure.bonus || 0,
          deductions: parseFloat(calculatedDeduction.toFixed(2)),
          absent_days: absentDays
        }
      });
    });
  });
});

// Save/Update salary structure for a user
router.post('/structure', (req, res) => {
  const { user_id, basic_salary, hra, da, bonus, deductions } = req.body;
  const query = `
    INSERT INTO salary_structures (user_id, basic_salary, hra, da, bonus, deductions)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
      basic_salary = VALUES(basic_salary),
      hra = VALUES(hra),
      da = VALUES(da),
      bonus = VALUES(bonus),
      deductions = VALUES(deductions)
  `;
  db.query(query, [user_id, basic_salary, hra, da, bonus, deductions], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: 'Error saving salary structure' });
    res.json({ success: true, message: 'Salary structure saved successfully' });
  });
});

module.exports = router;
