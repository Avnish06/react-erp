const mongoose = require('mongoose');

const leadSchema = new mongoose.Schema({
  name: { type: String, required: true },
  email: { type: String, required: true },
  phone: String,
  status: { type: String, enum: ['New', 'Contacted', 'Qualified', 'Lost', 'Converted'], default: 'New' },
  assigned_to: { type: mongoose.Schema.Types.ObjectId, ref: 'Profile' },
  source: String,
  company_name: String,
  notes: String
}, { timestamps: true });

module.exports = mongoose.model('Lead', leadSchema);
