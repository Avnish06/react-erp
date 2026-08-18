import React, { useState, useEffect } from 'react';
import axios from '../axiosConfig';
import { toast } from 'sonner';
import { X, Bell, UserCheck, Calendar, Briefcase, RefreshCw, AlertCircle } from 'lucide-react';
import moment from 'moment';

const ColovoWorkspaceDataModal = ({ isOpen, onClose, employee }) => {
  const [activeTab, setActiveTab] = useState('attendance');
  const [loading, setLoading] = useState(false);
  
  const [details, setDetails] = useState(null);
  const [attendance, setAttendance] = useState([]);
  const [leaves, setLeaves] = useState([]);
  
  const [notifyTitle, setNotifyTitle] = useState('');
  const [notifyMessage, setNotifyMessage] = useState('');
  const [sendingNotify, setSendingNotify] = useState(false);

  useEffect(() => {
    if (isOpen && employee?.email) {
      fetchData();
    }
  }, [isOpen, employee]);

  const fetchData = async () => {
    if (!employee?.email) return;
    setLoading(true);
    try {
      // Fetch all three endpoints in parallel
      const [detRes, attRes, leaveRes] = await Promise.all([
        axios.get(`/api/workspace-sync/employee/${employee.email}/details`),
        axios.get(`/api/workspace-sync/employee/${employee.email}/attendance`),
        axios.get(`/api/workspace-sync/employee/${employee.email}/leaves`)
      ]);
      
      if (detRes.data.success) setDetails(detRes.data.data);
      if (attRes.data.success) setAttendance(attRes.data.data);
      if (leaveRes.data.success) setLeaves(leaveRes.data.data);
    } catch (err) {
      toast.error('Failed to sync data from Colovo Workspace');
    } finally {
      setLoading(false);
    }
  };

  const handleSendNotification = async (e) => {
    e.preventDefault();
    if (!notifyTitle || !notifyMessage) return toast.error('Fill in all fields');
    
    setSendingNotify(true);
    try {
      const res = await axios.post(`/api/workspace-sync/employee/${employee.email}/notify`, {
        title: notifyTitle,
        message: notifyMessage
      });
      if (res.data.success) {
        toast.success('Notification pushed to Workspace successfully!');
        setNotifyTitle('');
        setNotifyMessage('');
      }
    } catch (err) {
      toast.error('Failed to send notification');
    } finally {
      setSendingNotify(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
      <div className="bg-white w-full max-w-4xl max-h-[90vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        {/* Header */}
        <div className="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 text-white">
          <div className="flex items-center gap-3">
            <div className="bg-white/20 p-2 rounded-lg">
              <Briefcase size={20} className="text-blue-100" />
            </div>
            <div>
              <h2 className="text-lg font-bold">Colovo Workspace Sync</h2>
              <p className="text-xs text-blue-200">Viewing real-time data for: {employee?.name} ({employee?.email})</p>
            </div>
          </div>
          <button onClick={onClose} className="p-2 bg-white/10 hover:bg-white/20 rounded-full transition-colors">
            <X size={18} />
          </button>
        </div>

        {/* Loading Overlay */}
        {loading ? (
          <div className="flex-1 flex flex-col items-center justify-center p-12 text-slate-500">
            <RefreshCw className="animate-spin mb-4" size={32} />
            <p>Syncing data from Colovo Workspace...</p>
          </div>
        ) : !details ? (
          <div className="flex-1 flex flex-col items-center justify-center p-12 text-amber-600">
            <AlertCircle size={48} className="mb-4 opacity-50" />
            <h3 className="text-lg font-bold mb-1">Employee Not Found in Workspace</h3>
            <p className="text-sm text-center">This employee has not been synced to the Colovo Workspace yet, or their email address does not match.</p>
          </div>
        ) : (
          <div className="flex-1 flex flex-col md:flex-row overflow-hidden">
            
            {/* Sidebar Tabs */}
            <div className="w-full md:w-64 bg-slate-50 border-r border-gray-200 p-4 flex flex-col gap-2">
              <button 
                onClick={() => setActiveTab('attendance')}
                className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all ${activeTab === 'attendance' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-blue-50'}`}
              >
                <UserCheck size={18} /> Attendance Log
              </button>
              <button 
                onClick={() => setActiveTab('leaves')}
                className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all ${activeTab === 'leaves' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-blue-50'}`}
              >
                <Calendar size={18} /> Leaves History
              </button>
              <button 
                onClick={() => setActiveTab('notify')}
                className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all ${activeTab === 'notify' ? 'bg-orange-500 text-white shadow-md' : 'text-slate-600 hover:bg-orange-50'}`}
              >
                <Bell size={18} /> Push Notification
              </button>

              <div className="mt-auto pt-6 border-t border-gray-200">
                <p className="text-xs text-slate-500 font-semibold mb-2 uppercase tracking-wider">Workspace Profile</p>
                <div className="bg-white p-3 rounded-lg border border-gray-200 text-sm">
                  <p><span className="text-slate-400">ID:</span> {details.id}</p>
                  <p><span className="text-slate-400">Role:</span> {details.role}</p>
                  <p><span className="text-slate-400">Salary:</span> {details.salary || 'N/A'}</p>
                </div>
              </div>
            </div>

            {/* Content Area */}
            <div className="flex-1 overflow-y-auto p-6 bg-white">
              
              {/* Attendance Tab */}
              {activeTab === 'attendance' && (
                <div className="animate-in fade-in slide-in-from-right-4 duration-300">
                  <h3 className="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <UserCheck className="text-blue-600" /> Recent Attendance
                  </h3>
                  {attendance.length === 0 ? (
                    <div className="p-8 text-center text-slate-500 bg-slate-50 rounded-xl border border-dashed border-slate-300">No attendance records found.</div>
                  ) : (
                    <div className="overflow-x-auto border border-gray-200 rounded-xl">
                      <table className="w-full text-left text-sm">
                        <thead className="bg-slate-50 border-b border-gray-200">
                          <tr>
                            <th className="px-4 py-3 font-semibold text-slate-600">Date</th>
                            <th className="px-4 py-3 font-semibold text-slate-600">Clock In</th>
                            <th className="px-4 py-3 font-semibold text-slate-600">Clock Out</th>
                            <th className="px-4 py-3 font-semibold text-slate-600">Status</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                          {attendance.map((record) => (
                            <tr key={record.id} className="hover:bg-slate-50 transition-colors">
                              <td className="px-4 py-3 font-medium">{moment(record.date).format('MMM DD, YYYY')}</td>
                              <td className="px-4 py-3">{record.clock_in ? moment(record.clock_in, 'HH:mm:ss').format('hh:mm A') : '-'}</td>
                              <td className="px-4 py-3">{record.clock_out ? moment(record.clock_out, 'HH:mm:ss').format('hh:mm A') : '-'}</td>
                              <td className="px-4 py-3">
                                <span className={`px-2 py-1 rounded-full text-[10px] font-bold uppercase ${record.status === 'present' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}`}>
                                  {record.status}
                                </span>
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  )}
                </div>
              )}

              {/* Leaves Tab */}
              {activeTab === 'leaves' && (
                <div className="animate-in fade-in slide-in-from-right-4 duration-300">
                  <h3 className="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <Calendar className="text-blue-600" /> Leave History
                  </h3>
                  {leaves.length === 0 ? (
                    <div className="p-8 text-center text-slate-500 bg-slate-50 rounded-xl border border-dashed border-slate-300">No leave records found.</div>
                  ) : (
                    <div className="space-y-3">
                      {leaves.map((leave) => (
                        <div key={leave.id} className="p-4 border border-gray-200 rounded-xl flex items-center justify-between hover:shadow-md transition-shadow bg-white">
                          <div>
                            <p className="font-bold text-slate-800 capitalize">{leave.type} Leave</p>
                            <p className="text-xs text-slate-500 mt-1">
                              {moment(leave.start_date).format('MMM DD')} - {moment(leave.end_date).format('MMM DD, YYYY')}
                            </p>
                            {leave.reason && <p className="text-sm text-slate-600 mt-2 italic">"{leave.reason}"</p>}
                          </div>
                          <span className={`px-3 py-1 rounded-full text-xs font-bold uppercase ${
                            leave.status === 'approved' ? 'bg-green-100 text-green-700' : 
                            leave.status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'
                          }`}>
                            {leave.status}
                          </span>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              )}

              {/* Notify Tab */}
              {activeTab === 'notify' && (
                <div className="animate-in fade-in slide-in-from-right-4 duration-300 max-w-xl mx-auto">
                  <div className="text-center mb-6">
                    <div className="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
                      <Bell className="text-orange-500" size={32} />
                    </div>
                    <h3 className="text-xl font-bold text-slate-800">Push Notification</h3>
                    <p className="text-sm text-slate-500">Send an instant alert directly to {employee?.name}'s Workspace dashboard.</p>
                  </div>

                  <form onSubmit={handleSendNotification} className="space-y-4">
                    <div>
                      <label className="block text-sm font-semibold text-slate-700 mb-1">Notification Title</label>
                      <input 
                        type="text" 
                        required
                        className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        placeholder="e.g., Mandatory Meeting at 3 PM"
                        value={notifyTitle}
                        onChange={e => setNotifyTitle(e.target.value)}
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-semibold text-slate-700 mb-1">Message Body</label>
                      <textarea 
                        required
                        rows="4"
                        className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
                        placeholder="Enter the details..."
                        value={notifyMessage}
                        onChange={e => setNotifyMessage(e.target.value)}
                      ></textarea>
                    </div>
                    <button 
                      type="submit" 
                      disabled={sendingNotify}
                      className="w-full py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold rounded-xl hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg shadow-orange-200 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                      {sendingNotify ? <RefreshCw className="animate-spin" size={18} /> : <Bell size={18} />}
                      {sendingNotify ? 'Pushing to Workspace...' : 'Send Notification'}
                    </button>
                  </form>
                </div>
              )}
              
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default ColovoWorkspaceDataModal;
