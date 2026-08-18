const express = require('express');
const router = express.Router();
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');
const { verifyToken } = require('../middleware/auth');
const UserIdentity = require('../models/UserIdentity');
const Profile = require('../models/Profile');
const Role = require('../models/Role');
const Permission = require('../models/Permission');

router.post('/login', async (req, res) => {
  try {
    const { email: rawEmail, password } = req.body || {};
    const email = typeof rawEmail === 'string' ? rawEmail.trim() : rawEmail;

    if (!email || !password) {
      return res.status(400).json({ success: false, message: 'Please provide email and password' });
    }

    const userIdentity = await UserIdentity.findOne({ email }).populate('role');
    
    if (!userIdentity) {
      return res.status(401).json({ success: false, message: 'Invalid credentials' });
    }

    const isMatch = await bcrypt.compare(password, userIdentity.password);
    if (!isMatch) {
      return res.status(401).json({ success: false, message: 'Invalid credentials' });
    }

    const profile = await Profile.findOne({ userIdentity: userIdentity._id });

    if (!profile) {
       return res.status(500).json({ success: false, message: 'User profile missing' });
    }

    if (profile.status === 'Pending' || profile.status === 'Pending Super Admin') {
      return res.status(403).json({ success: false, message: 'Your account is pending approval.' });
    }
    
    if (userIdentity.role.name !== 'Developer' && profile.status !== 'Active') {
      return res.status(403).json({ success: false, message: `Your account is currently ${profile.status}.` });
    }

    const token = jwt.sign(
      { id: userIdentity._id, role: userIdentity.role.name },
      process.env.JWT_SECRET || 'secret',
      { expiresIn: '24h' }
    );

    res.json({
      success: true,
      token,
      user: {
        id: userIdentity._id,
        employee_id: profile.employee_id,
        name: profile.name,
        email: userIdentity.email,
        role: userIdentity.role.name,
        profile_image: profile.profile_image || null,
        permissions: [] // TODO: Implement permissions fetch
      }
    });

  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({ success: false, message: 'Server error', error: error.message });
  }
});

router.get('/me', verifyToken, async (req, res) => {
  try {
    const userIdentity = await UserIdentity.findById(req.user.id).populate('role');
    if (!userIdentity) {
      return res.status(404).json({ success: false, message: 'User not found' });
    }

    const profile = await Profile.findOne({ userIdentity: userIdentity._id });

    res.json({
      success: true,
      user: {
        id: userIdentity._id,
        employee_id: profile.employee_id,
        name: profile.name,
        email: userIdentity.email,
        role: userIdentity.role.name,
        profile_image: profile.profile_image || null,
        permissions: []
      }
    });
  } catch (error) {
    res.status(500).json({ success: false, message: 'Server error' });
  }
});

// Basic register stub
router.post('/register', async (req, res) => {
  // TODO: implement full registration with dynamic role IDs
  res.status(400).json({ success: false, message: 'MongoDB Registration endpoint under construction.' });
});

module.exports = router;
