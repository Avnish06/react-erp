const mongoose = require('mongoose');

const profileSchema = new mongoose.Schema({
  userIdentity: { type: mongoose.Schema.Types.ObjectId, ref: 'UserIdentity', required: true, unique: true },
  name: { type: String, required: true },
  employee_id: { type: String, unique: true, sparse: true },
  vendor_id: { type: String, unique: true, sparse: true },
  status: { type: String, enum: ['Pending', 'Pending Super Admin', 'Active', 'Rejected', 'Suspended'], default: 'Pending' },
  company_name: String,
  profile_image: String,
  department_id: mongoose.Schema.Types.ObjectId,
  phone: String,
}, { timestamps: true });

module.exports = mongoose.model('Profile', profileSchema);
