const jwt = require('jsonwebtoken');
require('dotenv').config();

const secret = process.env.JWT_SECRET || 'secret';
console.log('Using secret:', secret);

const token = jwt.sign({ id: 1, role: 'Admin', permissions: [] }, secret, { expiresIn: '30m' });
console.log('Generated token:', token);

try {
  const verified = jwt.verify(token, secret);
  console.log('Token is valid:', verified);
} catch (e) {
  console.error('Token validation failed:', e);
}
