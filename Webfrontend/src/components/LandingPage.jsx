import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import {
  Building2,
  Users,
  LineChart,
  ShieldCheck,
  ArrowRight,
  CheckCircle2,
  Globe2,
  Zap,
  Target,
  BarChart4,
  Clock,
  FileText
} from 'lucide-react';

const fadeIn = {
  hidden: { opacity: 0, y: 30 },
  visible: { 
    opacity: 1, 
    y: 0,
    transition: { duration: 0.6, ease: "easeOut" }
  }
};

const staggerContainer = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.2
    }
  }
};

const LandingPage = () => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [dashboardRoute, setDashboardRoute] = useState('/dashboard');
  const navigate = useNavigate();

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      setIsAuthenticated(true);
      try {
        const user = JSON.parse(localStorage.getItem('user'));
        let targetRoute = '/dashboard';
        if (user && user.role === 'Vendor') {
          targetRoute = '/vendor-dashboard';
        }
        setDashboardRoute(targetRoute);
      } catch (e) {
        setDashboardRoute('/dashboard');
      }
    }
  }, []);

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    localStorage.removeItem('activeTab');
    setIsAuthenticated(false);
    navigate('/');
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-orange-50 to-slate-100 font-['Inter'] selection:bg-orange-50 selection:text-orange-900 overflow-hidden relative">
      
      {/* Global Background Gradient */}
      <div className="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div className="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-gradient-to-br from-orange-50/30 to-orange-50/10 blur-[120px] rounded-full" />
        <div className="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-gradient-to-tl from-slate-300/30 to-slate-200/10 blur-[120px] rounded-full" />
        <div className="absolute top-[40%] right-[10%] w-[30%] h-[30%] bg-gradient-to-tl from-orange-100/20 to-transparent blur-[100px] rounded-full" />
      </div>

      {/* Navbar */}
      <nav className="fixed w-full bg-gradient-to-br from-orange-50/80 to-slate-100/80 backdrop-blur-xl z-50 border-b border-slate-200/50 shadow-sm">
        <div className="max-w-7xl mx-auto px-6 lg:px-8">
          <div className="flex justify-between h-20 items-center">
            <div className="flex items-center gap-3">
              <img src="/image.png" alt="Colvo Logo" className="h-10 object-contain" />
            </div>
            
            <div className="flex items-center gap-4">
              {isAuthenticated ? (
                <>
                  <Link
                    to={dashboardRoute}
                    className="px-6 py-2.5 font-semibold text-orange-400 transition-colors hover:text-orange-500"
                  >
                    Go to Dashboard
                  </Link>
                  <button
                    onClick={handleLogout}
                    className="px-6 py-2.5 font-bold text-white transition-all bg-slate-800 rounded-xl hover:bg-slate-900 shadow-lg shadow-slate-200"
                  >
                    Logout
                  </button>
                </>
              ) : (
                <>
                  <Link
                    to="/login"
                    className="px-6 py-2.5 font-semibold text-slate-600 transition-colors hover:text-slate-900"
                  >
                    Log In
                  </Link>
                  <Link
                    to="/register"
                    className="px-6 py-2.5 font-bold text-white transition-all bg-orange-400 rounded-xl hover:bg-orange-500 shadow-lg shadow-orange-50"
                  >
                    Get Started
                  </Link>
                </>
              )}
            </div>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        {/* Background Accents */}
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full max-w-7xl pointer-events-none">
          <div className="absolute top-20 left-10 w-72 h-72 bg-orange-100/20 rounded-full blur-[80px]" />
          <div className="absolute top-40 right-10 w-96 h-96 bg-blue-300/20 rounded-full blur-[100px]" />
        </div>

        <div className="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 text-center">
          <motion.div 
            initial="hidden"
            animate="visible"
            variants={staggerContainer}
            className="max-w-4xl mx-auto"
          >
            <motion.div variants={fadeIn} className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-br from-orange-50 to-slate-100 border border-slate-200 text-slate-600 font-semibold text-sm mb-8 shadow-sm">
              <span className="flex h-2 w-2 rounded-full bg-orange-300 animate-pulse"></span>
              The Next Generation ERP
            </motion.div>
            
            <motion.h1 variants={fadeIn} className="text-5xl md:text-7xl font-black font-['Outfit'] text-slate-900 tracking-tight leading-[1.1] mb-8">
              Manage Your Enterprise With Absolute <span className="text-orange-400">Precision.</span>
            </motion.h1>
            
            <motion.p variants={fadeIn} className="text-lg md:text-xl text-slate-600 font-medium leading-relaxed mb-10 max-w-2xl mx-auto">
              Unify your human resources, customer relations, and business operations under one lightning-fast, incredibly intuitive platform.
            </motion.p>
            
            <motion.div variants={fadeIn} className="flex flex-col sm:flex-row gap-4 justify-center items-center">
              <Link
                to={isAuthenticated ? dashboardRoute : "/register"}
                className="w-full sm:w-auto px-8 py-4 bg-orange-400 text-white font-bold rounded-2xl hover:bg-orange-500 transition-all shadow-xl shadow-orange-400/20 flex items-center justify-center gap-2 text-lg group"
              >
                {isAuthenticated ? "Enter Workspace" : "Start For Free"}
                <ArrowRight className="w-5 h-5 group-hover:translate-x-1 transition-transform" />
              </Link>
              {!isAuthenticated && (
                <Link
                  to="/login"
                  className="w-full sm:w-auto px-8 py-4 bg-gradient-to-br from-orange-50 to-slate-100 text-slate-700 font-bold border border-slate-200 rounded-2xl hover:bg-gradient-to-br from-orange-50 to-slate-100 transition-all shadow-sm text-lg"
                >
                  Sign In to Account
                </Link>
              )}
            </motion.div>
          </motion.div>

          {/* Hero Dashboard Image */}
          <motion.div 
            initial={{ opacity: 0, y: 100 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 1, delay: 0.4 }}
            className="mt-20 relative max-w-5xl mx-auto"
          >
            <motion.div
              animate={{ y: [0, -15, 0] }}
              transition={{ duration: 6, repeat: Infinity, ease: "easeInOut" }}
              className="relative rounded-3xl bg-gradient-to-br from-orange-50 to-slate-100 p-2 shadow-2xl shadow-slate-300/50 border border-slate-200 overflow-hidden"
            >
              <img 
                src="/erp_hero.png" 
                alt="Colvo ERP Dashboard Mockup" 
                className="w-full h-auto rounded-2xl border border-slate-100 object-cover"
              />
              
              {/* Decorative Floating Badges */}
              <motion.div 
                animate={{ y: [0, -10, 0] }}
                transition={{ duration: 4, repeat: Infinity, ease: "easeInOut", delay: 1 }}
                className="absolute -right-6 top-1/4 bg-gradient-to-br from-orange-50 to-slate-100 p-4 rounded-2xl shadow-xl flex items-center gap-3 border border-slate-100 hidden md:flex"
              >
                <div className="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                  <CheckCircle2 className="text-green-600 w-5 h-5" />
                </div>
                <div>
                  <div className="text-xs font-bold text-slate-800 font-['Outfit']">System Optimal</div>
                </div>
              </motion.div>
              
              <motion.div 
                animate={{ y: [0, 10, 0] }}
                transition={{ duration: 5, repeat: Infinity, ease: "easeInOut", delay: 2 }}
                className="absolute -left-6 bottom-1/4 bg-gradient-to-br from-orange-50 to-slate-100 p-4 rounded-2xl shadow-xl flex items-center gap-3 border border-slate-100 hidden md:flex"
              >
                <div className="w-10 h-10 bg-orange-50 rounded-full flex items-center justify-center">
                  <Globe2 className="text-orange-400 w-5 h-5" />
                </div>
                <div>
                  <div className="text-xs font-bold text-slate-800 font-['Outfit']">Multi-Workspace</div>
                </div>
              </motion.div>
            </motion.div>
          </motion.div>
        </div>
      </section>

      {/* Social Proof */}
      <section className="py-12 border-y border-slate-200/50 bg-gradient-to-br from-orange-50/80 to-slate-100/80 backdrop-blur-sm relative z-10">
        <div className="max-w-7xl mx-auto px-6 lg:px-8 text-center">
          <motion.p 
            initial={{ opacity: 0 }}
            whileInView={{ opacity: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 1 }}
            className="text-sm font-bold text-slate-400 uppercase tracking-widest mb-8 font-['Outfit']"
          >
            Trusted by innovative companies worldwide
          </motion.p>
          <motion.div 
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, amount: 0.8 }}
            variants={staggerContainer}
            className="flex flex-wrap justify-center items-center gap-12 opacity-50 grayscale hover:grayscale-0 transition-all duration-700"
          >
            {['Acme Corp', 'Globex', 'Soylent', 'Initech', 'Umbrella'].map((name, i) => (
              <motion.span variants={fadeIn} key={i} className="text-2xl font-black font-['Outfit'] text-slate-800">{name}</motion.span>
            ))}
          </motion.div>
        </div>
      </section>

      {/* How it Works Section */}
      <section className="py-24 lg:py-32 relative z-10">
        <div className="max-w-7xl mx-auto px-6 lg:px-8">
          <motion.div 
            initial={{ opacity: 0, y: 50 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.8, ease: "easeOut" }}
            className="text-center max-w-3xl mx-auto mb-20"
          >
            <h2 className="text-4xl md:text-5xl font-black font-['Outfit'] text-slate-900 mb-6">How Colvo Works</h2>
            <p className="text-lg text-slate-600">A simple, streamlined process to get your entire enterprise up and running in minutes, not months.</p>
          </motion.div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
            {/* Connecting Line */}
            <div className="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-gradient-to-r from-orange-50 via-slate-300 to-orange-50 -z-10 -translate-y-1/2" />
            
            {[
              { step: "01", title: "Create Workspace", desc: "Set up your master administrative account and define your company's global profile." },
              { step: "02", title: "Invite Team", desc: "Add your employees, assign granular roles, and manage their document verifications." },
              { step: "03", title: "Automate", desc: "Let Colvo handle leads, generate proposals, and track your business analytics automatically." }
            ].map((item, i) => (
              <motion.div
                key={i}
                initial={{ opacity: 0, y: 50, scale: 0.9 }}
                whileInView={{ opacity: 1, y: 0, scale: 1 }}
                viewport={{ once: true, margin: "-50px" }}
                transition={{ duration: 0.6, delay: i * 0.2 }}
                className="bg-gradient-to-br from-orange-50/80 to-slate-100/80 backdrop-blur-md p-8 rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 text-center relative"
              >
                <div className="w-16 h-16 mx-auto bg-orange-400 text-white text-xl font-black font-['Outfit'] rounded-full flex items-center justify-center mb-6 shadow-lg shadow-orange-50 border-4 border-white">
                  {item.step}
                </div>
                <h3 className="text-2xl font-bold font-['Outfit'] text-slate-900 mb-4">{item.title}</h3>
                <p className="text-slate-600 leading-relaxed">{item.desc}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Features Grid */}
      <section className="py-24 lg:py-32 bg-gradient-to-br from-orange-50/80 to-slate-100/80 backdrop-blur-lg border-y border-slate-200/50 relative z-10">
        <div className="max-w-7xl mx-auto px-6 lg:px-8">
          <motion.div 
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: "-100px" }}
            variants={fadeIn}
            className="text-center max-w-3xl mx-auto mb-20"
          >
            <h2 className="text-4xl md:text-5xl font-black font-['Outfit'] text-slate-900 mb-6">Everything you need to scale</h2>
            <p className="text-lg text-slate-600">Replace your disjointed software stack with a single, highly optimized platform designed for modern workflows.</p>
          </motion.div>

          <motion.div 
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: "-50px" }}
            variants={staggerContainer}
            className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
          >
            {[
              {
                icon: <Users className="w-8 h-8 text-blue-600" />,
                title: "Human Resources",
                desc: "Onboard employees, manage documents, track attendance, and handle payroll seamlessly.",
                color: "bg-blue-50"
              },
              {
                icon: <Target className="w-8 h-8 text-orange-400" />,
                title: "Client CRM",
                desc: "Manage leads, track deals, and maintain a complete history of every customer interaction.",
                color: "bg-orange-50"
              },
              {
                icon: <FileText className="w-8 h-8 text-emerald-600" />,
                title: "Smart Proposals",
                desc: "Generate, edit, and send professional PDF proposals directly to clients with one click.",
                color: "bg-emerald-50"
              },
              {
                icon: <ShieldCheck className="w-8 h-8 text-purple-600" />,
                title: "Role-Based Access",
                desc: "Granular permission controls ensure data is only visible to the right people in your organization.",
                color: "bg-purple-50"
              },
              {
                icon: <BarChart4 className="w-8 h-8 text-rose-600" />,
                title: "Real-time Analytics",
                desc: "Visualize your business health with live dashboards, custom reports, and financial tracking.",
                color: "bg-rose-50"
              },
              {
                icon: <Globe2 className="w-8 h-8 text-cyan-600" />,
                title: "Multi-Workspace",
                desc: "Manage multiple companies or subsidiaries from a single master administrative account.",
                color: "bg-cyan-50"
              }
            ].map((feature, idx) => (
              <motion.div
                key={idx}
                variants={fadeIn}
                className="bg-gradient-to-br from-orange-50 to-slate-100 p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group"
              >
                <div className={`w-16 h-16 ${feature.color} rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300`}>
                  {feature.icon}
                </div>
                <h3 className="text-2xl font-bold font-['Outfit'] text-slate-800 mb-3">{feature.title}</h3>
                <p className="text-slate-600 leading-relaxed">
                  {feature.desc}
                </p>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </section>

      {/* Deep Dive Section */}
      <section className="py-24 lg:py-40 relative z-10">
        <div className="max-w-7xl mx-auto px-6 lg:px-8">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <motion.div 
              initial={{ opacity: 0, x: -80, rotate: -5 }}
              whileInView={{ opacity: 1, x: 0, rotate: 0 }}
              viewport={{ once: true, margin: "-100px" }}
              transition={{ duration: 1, type: "spring", bounce: 0.4 }}
              className="order-2 lg:order-1"
            >
              <div className="aspect-square rounded-[3rem] bg-slate-900 p-8 relative overflow-hidden">
                <div className="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]" />
                
                {/* Floating Elements */}
                <motion.div 
                  animate={{ y: [0, -20, 0] }}
                  transition={{ duration: 4, repeat: Infinity, ease: "easeInOut" }}
                  className="absolute top-10 left-10 bg-gradient-to-br from-orange-50 to-slate-100 p-4 rounded-2xl shadow-xl flex items-center gap-4"
                >
                  <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <CheckCircle2 className="text-green-600 w-6 h-6" />
                  </div>
                  <div>
                    <div className="text-sm font-bold text-slate-800 font-['Outfit']">Proposal Sent</div>
                    <div className="text-xs text-slate-500">Just now</div>
                  </div>
                </motion.div>

                <motion.div 
                  animate={{ y: [0, 20, 0] }}
                  transition={{ duration: 5, repeat: Infinity, ease: "easeInOut", delay: 1 }}
                  className="absolute bottom-20 right-10 bg-gradient-to-br from-orange-50 to-slate-100 p-4 rounded-2xl shadow-xl flex items-center gap-4"
                >
                  <div className="w-12 h-12 bg-orange-50 rounded-full flex items-center justify-center">
                    <Zap className="text-orange-400 w-6 h-6" />
                  </div>
                  <div>
                    <div className="text-sm font-bold text-slate-800 font-['Outfit']">New Lead Added</div>
                    <div className="text-xs text-slate-500">2 mins ago</div>
                  </div>
                </motion.div>
              </div>
            </motion.div>

            <motion.div 
              initial={{ opacity: 0, x: 80 }}
              whileInView={{ opacity: 1, x: 0 }}
              viewport={{ once: true, margin: "-100px" }}
              transition={{ duration: 1, type: "spring", bounce: 0.4 }}
              className="order-1 lg:order-2"
            >
              <h2 className="text-4xl md:text-5xl font-black font-['Outfit'] text-slate-900 mb-6 leading-tight">
                Work faster, <br/>
                <span className="text-orange-400">Not harder.</span>
              </h2>
              <p className="text-lg text-slate-600 mb-8 leading-relaxed">
                Our platform automates the busywork. From one-click client onboarding to automated document verification, Colvo saves your team hours of manual data entry every single week.
              </p>
              
              <ul className="space-y-4">
                {[
                  "Eliminate data silos across departments",
                  "Secure document vault for employee records",
                  "Dynamic role-based dashboards",
                  "Export reports in PDF and Excel instantly"
                ].map((item, i) => (
                  <li key={i} className="flex items-center gap-3 text-slate-700 font-medium">
                    <div className="flex-shrink-0 w-6 h-6 rounded-full bg-orange-50 flex items-center justify-center">
                      <CheckCircle2 className="w-4 h-4 text-orange-400" />
                    </div>
                    {item}
                  </li>
                ))}
              </ul>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Metrics Section */}
      <section className="py-24 lg:py-32 bg-slate-900 relative z-10 overflow-hidden">
        <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:48px_48px]" />
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-orange-300/10 rounded-full blur-[120px]" />
        
        <div className="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
          <motion.div 
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: "-100px" }}
            variants={staggerContainer}
            className="grid grid-cols-1 md:grid-cols-4 gap-8 text-center"
          >
            {[
              { value: "98%", label: "Client Retention" },
              { value: "2.5M+", label: "Proposals Sent" },
              { value: "15hrs", label: "Saved Weekly" },
              { value: "24/7", label: "System Uptime" }
            ].map((stat, i) => (
              <motion.div 
                key={i} 
                variants={fadeIn}
                whileHover={{ scale: 1.05 }}
                className="bg-slate-800/50 backdrop-blur-md border border-slate-700/50 p-8 rounded-[2rem]"
              >
                <div className="text-5xl font-black font-['Outfit'] text-white mb-2">{stat.value}</div>
                <div className="text-slate-400 font-medium tracking-wide">{stat.label}</div>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-32 relative overflow-hidden z-10 bg-gradient-to-br from-orange-50/80 to-slate-100/80 backdrop-blur-md border-b border-slate-200">
        <div className="absolute top-0 right-0 w-[800px] h-[800px] bg-orange-400/10 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3" />
        
        <div className="max-w-4xl mx-auto px-6 relative z-10 text-center">
          <motion.div
            initial={{ opacity: 0, scale: 0.8, y: 50 }}
            whileInView={{ opacity: 1, scale: 1, y: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.8, type: "spring", bounce: 0.5 }}
          >
            <h2 className="text-5xl md:text-7xl font-black font-['Outfit'] text-slate-900 mb-6 tracking-tight">
              Ready to transform <br/>your business?
            </h2>
            <p className="text-xl text-slate-600 mb-10 max-w-2xl mx-auto font-medium">
              Join hundreds of forward-thinking companies running their entire operations on Colvo.
            </p>
            <Link
              to="/register"
              className="inline-flex items-center justify-center px-12 py-6 text-xl font-bold text-white bg-slate-900 rounded-2xl hover:bg-orange-400 transition-colors shadow-2xl hover:shadow-orange-400/30 group"
            >
              Start Your Free Trial Now
              <ArrowRight className="w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform" />
            </Link>
          </motion.div>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-slate-950 pt-20 pb-8 border-t border-slate-800 relative z-10">
        <div className="max-w-7xl mx-auto px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            <div className="col-span-1 md:col-span-2">
              <div className="flex items-center gap-3 mb-6">
                <div className="w-8 h-8 bg-orange-400 rounded-lg flex items-center justify-center">
                  <Building2 className="w-5 h-5 text-white" />
                </div>
                <span className="text-2xl font-black font-['Outfit'] tracking-tight text-white">
                  Colvo<span className="text-orange-400">.</span>
                </span>
              </div>
              <p className="text-slate-400 max-w-sm">
                The modern operating system for your entire enterprise. Build better, manage smarter, grow faster.
              </p>
            </div>
            
            <div>
              <h4 className="text-white font-bold font-['Outfit'] mb-4">Product</h4>
              <ul className="space-y-3">
                <li><a href="#" className="text-slate-400 hover:text-white transition-colors">Features</a></li>
                <li><a href="#" className="text-slate-400 hover:text-white transition-colors">Pricing</a></li>
                <li><a href="#" className="text-slate-400 hover:text-white transition-colors">Security</a></li>
              </ul>
            </div>
            
            <div>
              <h4 className="text-white font-bold font-['Outfit'] mb-4">Company</h4>
              <ul className="space-y-3">
                <li><a href="#" className="text-slate-400 hover:text-white transition-colors">About</a></li>
                <li><a href="#" className="text-slate-400 hover:text-white transition-colors">Contact</a></li>
                <li><a href="#" className="text-slate-400 hover:text-white transition-colors">Legal</a></li>
              </ul>
            </div>
          </div>
          
          <div className="pt-8 border-t border-slate-800 text-center text-slate-500 text-sm flex flex-col md:flex-row justify-between items-center">
            <p>&copy; {new Date().getFullYear()} Colvo Corporation. All rights reserved.</p>
            <div className="flex gap-4 mt-4 md:mt-0">
              <a href="#" className="hover:text-white transition-colors">Privacy Policy</a>
              <a href="#" className="hover:text-white transition-colors">Terms of Service</a>
            </div>
          </div>
        </div>
      </footer>

    </div>
  );
};

export default LandingPage;
