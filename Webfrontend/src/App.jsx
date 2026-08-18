import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { Toaster } from 'sonner';

import LandingPage from './components/LandingPage';
import Login from './components/Login';
import Register from './components/Register';
import Dashboard from './components/Dashboard';
import VendorRegistration from './components/VendorRegistration';
import VendorDashboard from './components/VendorDashboard';
import ResetPassword from './components/ResetPassword';

function App() {
  return (
    <Router>
      <div className="h-full">
        <Routes>
          <Route path="/" element={<LandingPage />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/vendor-register" element={<VendorRegistration />} />
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/vendor-dashboard" element={<VendorDashboard />} />
          <Route path="/reset-password/:token" element={<ResetPassword />} />
        </Routes>
        <Toaster position="top-center" toastOptions={{
          style: {
            background: 'rgba(255, 255, 255, 0.8)',
            backdropFilter: 'blur(16px)',
            border: '1px solid rgba(255, 255, 255, 0.4)',
            boxShadow: '0 4px 30px rgba(0, 0, 0, 0.1)',
            borderRadius: '16px',
            color: '#1e293b'
          }
        }} />
      </div>
    </Router>
  );
}

export default App;
