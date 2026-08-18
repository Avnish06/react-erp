const mongoose = require('mongoose');

const customerSchema = new mongoose.Schema({
  name: { type: String, required: true },
  email: { type: String, required: true, unique: true },
  phone: String,
  company_name: String,
  address: String,
  status: { type: String, enum: ['Active', 'Inactive'], default: 'Active' },
  assigned_to: { type: mongoose.Schema.Types.ObjectId, ref: 'Profile' },
  portal_access_enabled: { type: Boolean, default: false },
  password: { type: String }, // For client portal
}, { timestamps: true });

module.exports = mongoose.model('Customer', customerSchema);
