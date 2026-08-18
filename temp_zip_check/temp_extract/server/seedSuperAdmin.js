const mongoose = require('mongoose');
const bcrypt = require('bcryptjs');
const dotenv = require('dotenv');
const path = require('path');
const UserIdentity = require('./models/UserIdentity');
const Profile = require('./models/Profile');
const Role = require('./models/Role');

dotenv.config({ path: path.join(__dirname, '.env') });

const seedSuperAdmin = async () => {
  try {
    const mongoUri = 'mongodb://avnishkrmbd_db_user:kDgdlAw1MuuTbTFV@ac-grebxwa-shard-00-00.p6t4yze.mongodb.net:27017,ac-grebxwa-shard-00-01.p6t4yze.mongodb.net:27017,ac-grebxwa-shard-00-02.p6t4yze.mongodb.net:27017/erpmaster?ssl=true&replicaSet=atlas-13o890-shard-0&authSource=admin&retryWrites=true&w=majority';
    console.log(`Connecting to MongoDB...`); // Mask password
    await mongoose.connect(mongoUri, { family: 4, serverSelectionTimeoutMS: 5000 });
    console.log('✅ MongoDB connected successfully!');

    // Create Super Admin Role
    let superAdminRole = await Role.findOne({ name: 'Super Admin' });
    if (!superAdminRole) {
      superAdminRole = await Role.create({ name: 'Super Admin', description: 'System Administrator' });
      console.log('✅ Super Admin role created');
    }

    const email = 'aashusharma1198@gmail.com';
    const password = '112233';

    // Hash password
    const salt = await bcrypt.genSalt(10);
    const hashedPassword = await bcrypt.hash(password, salt);

    // Create UserIdentity
    let user = await UserIdentity.findOne({ email });
    if (!user) {
      user = await UserIdentity.create({
        email,
        password: hashedPassword,
        role: superAdminRole._id
      });
      console.log('✅ Super Admin UserIdentity created');
      
      // Create Profile
      await Profile.create({
        userIdentity: user._id,
        name: 'Aashu Sharma',
        employee_id: 'SA-0001',
        status: 'Active',
        company_name: 'Hatbaliya'
      });
      console.log('✅ Super Admin Profile created');
    } else {
      console.log('⚠️ Super Admin already exists in the database.');
    }

    console.log('🎉 Seeding complete. You can now log in.');
    process.exit(0);
  } catch (error) {
    console.error('❌ Error during seeding:', error);
    process.exit(1);
  }
};

seedSuperAdmin();
