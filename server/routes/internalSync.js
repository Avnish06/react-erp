const express = require("express");
const router = express.Router();
const db = require("../config/db");

const ERP_SECRET = process.env.ERP_SHARED_SECRET || "default-erp-secret-12345";

function verifyColovoSecret(req, res, next) {
  const secret = req.headers["x-erp-secret"];
  if (!secret || secret !== ERP_SECRET) {
    return res.status(401).json({ success: false, message: "Unauthorized" });
  }
  next();
}

// POST /api/internal/sync-colovo-profile
// Colovo Workspace pushes profile updates here
router.post("/sync-colovo-profile", verifyColovoSecret, async (req, res) => {
  const { employee_email, name, department, position, action, document_type } = req.body;
  if (!employee_email) return res.status(400).json({ success: false, message: "employee_email is required" });

  try {
    const [identityRows] = await db.promise.query("SELECT id FROM user_identities WHERE email = ? LIMIT 1", [employee_email]);
    if (identityRows.length === 0) return res.status(404).json({ success: false, message: "Employee not found in ERP" });
    const userId = identityRows[0].id;

    if (action === "document_generated" && document_type) {
      const stepMap = {
        offer_letter: { step_3_offer: true },
        appointment_letter: { step_3_offer: true },
        employment_contract: { step_3_offer: true },
        nda: { step_3_offer: true },
        leave_policy: { step_4_orientation: true },
      };
      const stepUpdate = stepMap[document_type];
      if (stepUpdate) {
        const [existing] = await db.promise.query("SELECT id FROM onboarding_status WHERE user_id = ? LIMIT 1", [userId]);
        const setClauses = Object.keys(stepUpdate).map(k => k + " = ?").join(", ");
        if (existing.length > 0) {
          await db.promise.query("UPDATE onboarding_status SET " + setClauses + " WHERE user_id = ?", [...Object.values(stepUpdate).map(v => v ? 1 : 0), userId]);
        } else {
          const defaultDocs = JSON.stringify({ aadhar: false, photo: false, parents: false });
          await db.promise.query("INSERT INTO onboarding_status (user_id, step_1_docs, step_3_offer) VALUES (?, ?, ?)", [userId, defaultDocs, stepUpdate.step_3_offer ? 1 : 0]);
        }
        console.log("[Internal Sync] Onboarding step updated for", employee_email, document_type);
      }
      return res.json({ success: true, message: "Onboarding step updated for " + document_type });
    }

    const updates = [];
    const params = [];
    if (name) { updates.push("name = ?"); params.push(name); }
    if (updates.length > 0) {
      params.push(userId);
      await db.promise.query("UPDATE employees SET " + updates.join(", ") + " WHERE user_id = ?", params);
      console.log("[Internal Sync] Updated ERP profile for", employee_email);
    }
    return res.json({ success: true, message: "ERP profile synced from Colovo Workspace" });

  } catch (err) {
    console.error("[Internal Sync] Error:", err.message);
    return res.status(500).json({ success: false, message: "Internal sync error: " + err.message });
  }
});

// POST /api/internal/sync-leave-status
// Colovo Workspace pushes leave approve/reject here
router.post("/sync-leave-status", verifyColovoSecret, async (req, res) => {
  const { employee_email, leave_id, status } = req.body;
  if (!employee_email || !status) return res.status(400).json({ success: false, message: "employee_email and status are required" });

  try {
    const [identityRows] = await db.promise.query("SELECT id FROM user_identities WHERE email = ? LIMIT 1", [employee_email]);
    if (identityRows.length === 0) return res.status(404).json({ success: false, message: "Employee not found in ERP" });
    const userId = identityRows[0].id;
    const erpStatus = status === "approved" ? "Approved" : "Rejected";

    await db.promise.query(
      "UPDATE leave_requests SET status = ? WHERE user_id = ? AND status = ? ORDER BY created_at DESC LIMIT 1",
      [erpStatus, userId, "Pending"]
    );

    console.log("[Internal Sync] Leave", status, "synced to ERP for", employee_email);
    return res.json({ success: true, message: "Leave " + status + " synced to ERP" });

  } catch (err) {
    console.error("[Internal Sync] Error:", err.message);
    return res.status(500).json({ success: false, message: "Internal sync error: " + err.message });
  }
});

module.exports = router;