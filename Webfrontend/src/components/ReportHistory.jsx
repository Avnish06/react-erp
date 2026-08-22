import React, { useState, useEffect } from 'react';
import axios from '../axiosConfig';
import { FileText, Download, Search, Filter, Calendar, Plus, BarChart3 } from 'lucide-react';
import { toast } from 'sonner';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
const logoImg = '/erp_logo.png';

const ReportHistory = () => {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showGenModal, setShowGenModal] = useState(false);
  const [genData, setGenData] = useState({
    type: 'Monthly',
    month: new Date().toLocaleString('en-US', { month: 'long' }),
    year: new Date().getFullYear(),
  });

  useEffect(() => {
    fetchReports();
  }, []);

  const fetchReports = async () => {
    try {
      const res = await axios.get('/api/reports');
      if (res.data.success) {
        setReports(res.data.data);
      }
    } catch (err) {
      console.error('Fetch Reports Error:', err);
      toast.error(`Error fetching reports: ${err.response?.data?.message || err.message}`);
    } finally {
      setLoading(false);
    }
  };

  const handleGenerate = async () => {
    try {
      const res = await axios.post('/api/reports/generate', genData);
      if (res.data.success) {
        toast.success('Report generated successfully');
        fetchReports();
        setShowGenModal(false);
      }
    } catch (err) {
      console.error('Generate Report Error:', err);
      toast.error(`Error generating report: ${err.response?.data?.message || err.message}`);
    }
  };

  const handleDownload = async (report) => {
    // Load image as promise
    const loadImage = (src) => {
      return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = () => resolve(img);
        img.onerror = (e) => reject(e);
        img.src = src;
      });
    };

    let logo = null;
    try {
      logo = await loadImage(logoImg);
    } catch (e) {
      console.error('Failed to load logo', e);
    }

    const doc = new jsPDF();
    
    // Create a stunning, professional cover-like header
    doc.setFillColor(10, 25, 47); // Dark navy blue
    doc.rect(0, 0, 210, 50, 'F');
    doc.setFillColor(0, 212, 255); // Cyan/Bright Blue accent
    doc.rect(0, 50, 210, 2, 'F');
    doc.setFillColor(17, 34, 64);
    doc.triangle(150, 0, 210, 0, 210, 50, 'F');

    // Logo / Branding Text
    if (logo) {
      doc.addImage(logo, 'PNG', 15, 10, 35, 25);
      doc.setFontSize(22);
      doc.setTextColor(255, 255, 255);
      doc.setFont('helvetica', 'bold');
      doc.text('HATBALIYA', 55, 22);
      doc.setFontSize(12);
      doc.setTextColor(136, 146, 176); 
      doc.setFont('helvetica', 'normal');
      doc.text('TECHNOLOGIES', 55, 28);
    } else {
      doc.setFontSize(26);
      doc.setTextColor(255, 255, 255);
      doc.setFont('helvetica', 'bold');
      doc.text('HATBALIYA', 20, 25);
      doc.setFontSize(14);
      doc.setTextColor(136, 146, 176); 
      doc.setFont('helvetica', 'normal');
      doc.text('TECHNOLOGIES', 20, 32);
    }

    // Title Tag
    doc.setFontSize(14);
    doc.setTextColor(0, 212, 255); // Cyan accent
    doc.setFont('helvetica', 'bold');
    doc.text('FINANCIAL REPORT', 195, 25, { align: 'right' });
    doc.setFontSize(11);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(204, 214, 246);
    doc.text(`Type: ${report.type}`, 195, 32, { align: 'right' });

    // Report Details Section (Elegant floating card look)
    doc.setFillColor(245, 247, 250); // Very light grey blue
    doc.roundedRect(15, 60, 180, 25, 4, 4, 'F');
    doc.setDrawColor(226, 232, 240);
    doc.setLineWidth(0.5);
    doc.roundedRect(15, 60, 180, 25, 4, 4, 'S');

    doc.setTextColor(15, 23, 42); 
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.text('REPORT TITLE', 20, 72);
    
    doc.setFontSize(14);
    doc.setTextColor(10, 25, 47); // Dark navy
    doc.text(report.title, 20, 80);

    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(15, 23, 42);
    doc.text('GENERATION DATE', 140, 72);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(10, 25, 47);
    doc.text(new Date(report.created_at).toLocaleDateString(), 140, 80);

    autoTable(doc, {
      startY: 95,
      head: [['Category', 'Details', 'Amount']],
      body: [
        ['Salary Expenditure', 'Total net salaries paid', `₹${Number(report.total_salary).toFixed(2)}`],
        ['General Expenditures', 'Miscellaneous business costs', `₹${Number(report.total_expenditure || 0).toFixed(2)}`],
        ['Client Revenue', 'Total billed to clients (from invoices)', `₹${Number(report.total_invoices).toFixed(2)}`],
        ['Net Balance', 'Revenue - (Salary + Expenditures)', `₹${(Number(report.total_invoices) - (Number(report.total_salary) + Number(report.total_expenditure || 0))).toFixed(2)}`],
      ],
      theme: 'grid',
      headStyles: { fillColor: [10, 25, 47], textColor: 255, fontSize: 11, fontStyle: 'bold' },
      bodyStyles: { textColor: 50, fontSize: 10 },
      alternateRowStyles: { fillColor: [248, 250, 252] },
      styles: { cellPadding: 6 }
    });

    doc.setFontSize(9);
    doc.setTextColor(148, 163, 184); // slate-400
    doc.text('Confidential Financial Report - FOR INTERNAL USE ONLY', 105, 280, { align: 'center' });

    doc.save(`${report.title.replace(/\s+/g, '_')}.pdf`);
    toast.success('Report downloaded');
  };

  const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex-wrap">
        <div>
          <h3 className="text-xl font-bold text-blue-950">Financial Reports</h3>
          <p className="text-slate-500 text-sm">Access and download generated monthly/annual summaries</p>
        </div>
        <button
          onClick={() => setShowGenModal(true)}
          className="bg-orange-600 text-white px-6 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-orange-700 transition-all shadow-lg shadow-blue-200"
        >
          <Plus size={18} /> Generate New Report
        </button>
      </div>

      {showGenModal && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 space-y-6 transform animate-in fade-in zoom-in duration-200">
            <div className="flex justify-between items-center">
              <h3 className="text-xl font-black text-blue-950">Generate Report</h3>
              <button onClick={() => setShowGenModal(false)} className="text-gray-400 hover:text-slate-600">×</button>
            </div>

            <div className="space-y-4">
              <div className="space-y-2">
                <label className="text-sm font-bold text-slate-500 uppercase tracking-wider">Report Type</label>
                <div className="flex flex-wrap gap-2">
                  {['Monthly', 'Annual'].map(t => (
                    <button
                      key={t}
                      onClick={() => setGenData({ ...genData, type: t })}
                      className={`flex-1 py-2 rounded-lg text-sm font-bold border transition-all ${genData.type === t ? 'bg-orange-50 border-orange-600 text-orange-600' : 'bg-gray-50 border-gray-100 text-gray-400'}`}
                    >
                      {t}
                    </button>
                  ))}
                </div>
              </div>

              {genData.type === 'Monthly' && (
                <div className="space-y-2">
                  <label className="text-sm font-bold text-slate-500 uppercase tracking-wider">Select Month</label>
                  <select
                    className="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none"
                    value={genData.month}
                    onChange={(e) => setGenData({ ...genData, month: e.target.value })}
                  >
                    {months.map(m => <option key={m} value={m}>{m}</option>)}
                  </select>
                </div>
              )}

              <div className="space-y-2">
                <label className="text-sm font-bold text-slate-500 uppercase tracking-wider">Select Year</label>
                <select
                  className="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none"
                  value={genData.year}
                  onChange={(e) => setGenData({ ...genData, year: e.target.value })}
                >
                  {[2024, 2025, 2026, 2027].map(y => <option key={y} value={y}>{y}</option>)}
                </select>
              </div>
            </div>

            <button
              onClick={handleGenerate}
              className="w-full bg-orange-600 text-white py-4 rounded-xl font-black hover:bg-orange-700 shadow-xl shadow-blue-100 transition-all flex items-center justify-center gap-2"
            >
              <BarChart3 size={20} /> Generate and Save
            </button>
          </div>
        </div>
      )}

      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left">
            <thead className="bg-gray-50 border-b border-gray-100">
              <tr>
                <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Report Title</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Type</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Generated Date</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-50">
              {loading ? (
                <tr><td colSpan="4" className="px-6 py-8 text-center text-slate-500">Loading history...</td></tr>
              ) : reports.length === 0 ? (
                <tr><td colSpan="4" className="px-6 py-8 text-center text-slate-500">No reports generated yet.</td></tr>
              ) : reports.map((report) => (
                <tr key={report.id} className="hover:bg-orange-50/30 transition-colors group">
                  <td className="px-6 py-4">
                    <div className="flex flex-wrap items-center gap-3">
                      <div className="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                        <FileText size={20} />
                      </div>
                      <span className="font-semibold text-blue-900">{report.title}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4">
                    <span className={`px-3 py-1 rounded-full text-[10px] font-bold uppercase ${report.type === 'Annual' ? 'bg-purple-50 text-purple-600' : 'bg-orange-50 text-orange-600'
                      }`}>
                      {report.type}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-sm text-slate-500 font-medium">{new Date(report.created_at).toLocaleDateString()}</td>
                  <td className="px-6 py-4 text-right">
                    <button
                      onClick={() => handleDownload(report)}
                      className="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-all"
                    >
                      <Download size={18} />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default ReportHistory;
