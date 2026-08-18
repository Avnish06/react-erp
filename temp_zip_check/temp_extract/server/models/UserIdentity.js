const mongoose = require('mongoose');

const userIdentitySchema = new mongoose.Schema({
  email: { type: String, required: true, unique: true },
  password: { type: String, required: true },
  role: { type: mongoose.Schema.Types.ObjectId, ref: 'Role', required: true }
}, { timestamps: true });

module.exports = mongoose.model('UserIdentity', userIdentitySchema);
