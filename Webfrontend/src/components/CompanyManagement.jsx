import React, { useState, useEffect } from 'react';
import axios from '../axiosConfig';
import { toast } from 'sonner';
import { Building2, Plus, Edit, Trash2 } from 'lucide-react';

const CompanyManagement = () => {
  const [companies, setCompanies] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    address: ''
  });
  const [logoFile, setLogoFile] = useState(null);

  useEffect(() => {
    fetchCompanies();
  }, []);

  const fetchCompanies = async () => {
    try {
      const res = await axios.get('/api/companies');
      if (res.data.success) {
        setCompanies(res.data.data);
      }
    } catch (err) {
      toast.error('Failed to load workspaces');
    } finally {
      setLoading(false);
    }
  };

  const handleCreateCompany = async (e) => {
    e.preventDefault();
    if (!formData.name.trim()) return toast.error('Workspace name is required');
    
    try {
      const submitData = new FormData();
      submitData.append('name', formData.name);
      submitData.append('email', formData.email);
      submitData.append('phone', formData.phone);
      submitData.append('address', formData.address);
      if (logoFile) {
        submitData.append('logo', logoFile);
      }

      if (editingId) {
        await axios.put(`/api/companies/${editingId}`, submitData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('Workspace updated successfully!');
      } else {
        await axios.post('/api/companies', submitData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('Workspace created successfully!');
      }
      
      setFormData({ name: '', email: '', phone: '', address: '' });
      setLogoFile(null);
      setEditingId(null);
      setIsModalOpen(false);
      fetchCompanies();
      // Dispatch custom event to tell Dashboard to reload companies
      window.dispatchEvent(new Event('companiesUpdated'));
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to save workspace');
    }
  };

  const handleDeleteCompany = async (id) => {
    if (!window.confirm('Are you sure you want to delete this workspace? This action cannot be undone.')) return;
    try {
      await axios.delete(`/api/companies/${id}`);
      toast.success('Workspace deleted successfully!');
      fetchCompanies();
      window.dispatchEvent(new Event('companiesUpdated'));
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to delete workspace');
    }
  };

  if (loading) return <div className="p-8 text-center text-gray-500">Loading workspaces...</div>;

  return (
    <div className="p-8 max-w-6xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div className="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div className="flex items-center gap-4">
          <div className="p-3 bg-blue-50 text-blue-600 rounded-xl">
            <Building2 size={24} />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-slate-800">Manage Workspaces</h1>
            <p className="text-slate-500 text-sm">View and manage all registered workspaces.</p>
          </div>
        </div>
        <button
          onClick={() => {
            setEditingId(null);
            setFormData({ name: '', email: '', phone: '', address: '' });
            setLogoFile(null);
            setIsModalOpen(true);
          }}
          className="flex items-center gap-2 px-6 py-3 bg-orange-600 text-white font-bold rounded-xl shadow-lg shadow-orange-200 hover:bg-orange-700 transition-all active:scale-95"
        >
          <Plus size={18} />
          <span>Add Workspace</span>
        </button>
      </div>

      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="bg-slate-50 border-b border-gray-100">
              <th className="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Workspace ID</th>
              <th className="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Workspace Name</th>
              <th className="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {companies.length === 0 ? (
              <tr>
                <td colSpan="3" className="px-6 py-8 text-center text-gray-400">No workspaces found.</td>
              </tr>
            ) : (
              companies.map(comp => (
                <tr key={comp.id} className="hover:bg-slate-50/50 transition-colors">
                  <td className="px-6 py-4 text-sm font-semibold text-gray-500">#{comp.id}</td>
                  <td className="px-6 py-4 font-bold text-slate-700">{comp.name}</td>
                  <td className="px-6 py-4 text-right">
                    <div className="flex justify-end gap-2">
                      <button 
                        onClick={() => {
                          setEditingId(comp.id);
                          setFormData({ 
                            name: comp.name || '', 
                            email: comp.email || '', 
                            phone: comp.phone || '', 
                            address: comp.address || '' 
                          });
                          setLogoFile(null);
                          setIsModalOpen(true);
                        }}
                        className="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                      >
                        <Edit size={16} />
                      </button>
                      <button 
                        onClick={() => handleDeleteCompany(comp.id)}
                        className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                      >
                        <Trash2 size={16} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {isModalOpen && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-md flex flex-col overflow-hidden animate-in zoom-in-95 duration-200">
            <div className="p-6 border-b border-gray-100 bg-slate-50">
              <h3 className="text-lg font-bold text-slate-800">{editingId ? 'Edit Workspace' : 'Add New Workspace'}</h3>
            </div>
            <form onSubmit={handleCreateCompany} className="p-6 space-y-4 overflow-y-auto max-h-[80vh]">
              <div className="space-y-2">
                <label className="text-sm font-bold text-gray-700">Workspace Name</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Acme Corp"
                  className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none"
                  value={formData.name}
                  onChange={(e) => setFormData({...formData, name: e.target.value})}
                />
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <label className="text-sm font-bold text-gray-700">Email Address</label>
                  <input
                    type="email"
                    placeholder="contact@acme.com"
                    className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none"
                    value={formData.email}
                    onChange={(e) => setFormData({...formData, email: e.target.value})}
                  />
                </div>
                <div className="space-y-2">
                  <label className="text-sm font-bold text-gray-700">Phone Number</label>
                  <input
                    type="text"
                    placeholder="+1 (555) 000-0000"
                    className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none"
                    value={formData.phone}
                    onChange={(e) => setFormData({...formData, phone: e.target.value})}
                  />
                </div>
              </div>
              <div className="space-y-2">
                <label className="text-sm font-bold text-gray-700">Physical Address</label>
                <input
                  type="text"
                  placeholder="123 Business St, Suite 100"
                  className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none"
                  value={formData.address}
                  onChange={(e) => setFormData({...formData, address: e.target.value})}
                />
              </div>
              <div className="space-y-2">
                <label className="text-sm font-bold text-gray-700">Workspace Logo</label>
                <input
                  type="file"
                  accept="image/*"
                  className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"
                  onChange={(e) => setLogoFile(e.target.files[0])}
                />
                <p className="text-xs text-gray-500 mt-1">Upload a PNG or JPG (max 2MB). Ideal ratio 1:1.</p>
              </div>
              <div className="flex justify-end gap-3 pt-4">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-5 py-2.5 text-slate-500 font-semibold hover:bg-slate-100 rounded-xl transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-6 py-2.5 bg-orange-600 text-white font-bold rounded-xl hover:bg-orange-700 transition-colors"
                >
                  {editingId ? 'Save Changes' : 'Create'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default CompanyManagement;
