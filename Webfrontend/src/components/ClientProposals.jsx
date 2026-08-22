import React, { useState, useEffect } from 'react';
import axios from '../axiosConfig';
import { toast } from 'sonner';
import { FileText, CheckCircle, Clock, Plus, X, Mail, Download, Trash2 } from 'lucide-react';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
const logoImg = '/erp_logo.png';

const defaultTerms = "1. Standard Validity: This proposal is valid for 30 days.\n2. Payment Terms: 50% upfront, 50% upon completion.\n3. Confidentiality: Both parties agree to maintain strict confidentiality.\n4. Scope Adjustments: Any changes to the scope of work may require a revised proposal.";

const ClientProposals = () => {
  const [proposals, setProposals] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [signModalOpen, setSignModalOpen] = useState(false);
  const [activeProposalId, setActiveProposalId] = useState(null);
  const [signatureText, setSignatureText] = useState('');
  
  const user = JSON.parse(localStorage.getItem('user')) || {};
  const isClient = user.role === 'Client';
  const isAdmin = user.role === 'Admin' || user.role === 'Super Admin' || user.role === 'Developer';
  
  const [formData, setFormData] = useState({
    client_name: '',
    client_email: '',
    project_name: '',
    value: '',
    details: '',
    terms: defaultTerms
  });

  useEffect(() => {
    fetchProposals();
  }, []);

  const fetchProposals = async () => {
    try {
      setLoading(true);
      const res = await axios.get('/api/client-management/proposals');
      if (res.data.success) {
        setProposals(res.data.proposals);
      }
    } catch (err) {
      toast.error('Failed to load proposals');
    } finally {
      setLoading(false);
    }
  };

  const approveProposal = async (id) => {
    try {
      const res = await axios.put(`/api/client-management/proposals/${id}/approve`);
      if (res.data.success) {
        toast.success('Proposal approved successfully');
        fetchProposals();
      }
    } catch (err) {
      toast.error('Failed to approve proposal');
    }
  };

  const handleDeleteProposal = async (id) => {
    if (!window.confirm('Are you sure you want to delete this proposal?')) return;
    try {
      const res = await axios.delete(`/api/client-management/proposals/${id}`);
      if (res.data.success) {
        toast.success('Proposal deleted successfully');
        fetchProposals();
      }
    } catch (err) {
      toast.error('Failed to delete proposal');
    }
  };

  const handleSignProposal = async () => {
    if (!signatureText.trim()) return toast.error('Please type your signature');
    
    try {
      const endpoint = isClient ? 'client-sign' : 'admin-sign';
      const res = await axios.put(`/api/client-management/proposals/${activeProposalId}/${endpoint}`, {
        signature: signatureText
      });
      if (res.data.success) {
        toast.success(res.data.message);
        setSignModalOpen(false);
        setSignatureText('');
        fetchProposals();
      }
    } catch (err) {
      toast.error('Failed to sign proposal');
    }
  };

  const openSignModal = (id) => {
    setActiveProposalId(id);
    setSignModalOpen(true);
  };

  const handleConvertToInvoice = async (p) => {
    const invId = `INV-${Math.floor(1000 + Math.random() * 9000)}`;
    const items = [{ description: `Proposal: ${p.project_name}`, qty: 1, rate: p.value }];
    const total = parseFloat(p.value) * 1.18; // adding 18% tax
    
    // Create dummy pdf blob for the invoice
    const doc = new jsPDF();
    doc.text(`Invoice for ${p.project_name}`, 20, 20);
    const pdfBlob = doc.output('blob');

    const formData = new FormData();
    formData.append('id', invId);
    formData.append('client_name', p.client_name);
    formData.append('client_email', p.client_email);
    formData.append('total_amount', total);
    formData.append('invoice_date', new Date().toISOString().split('T')[0]);
    formData.append('currency', 'INR');
    formData.append('is_recurring', false);
    formData.append('items', JSON.stringify(items));
    formData.append('invoice_pdf', pdfBlob, `Invoice_${invId}.pdf`);

    const loadingToast = toast.loading('Converting proposal to Invoice...');
    try {
      const res = await axios.post('/api/invoices', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      if (res.data.success) {
        toast.success(`Converted to Invoice #${invId}! You can view it in the Invoices tab.`, { id: loadingToast });
      } else {
        toast.error('Error creating invoice', { id: loadingToast });
      }
    } catch (err) {
      toast.error('Failed to convert to invoice', { id: loadingToast });
    }
  };

  const handleCreateProposal = async (e) => {
    e.preventDefault();
    try {
      const res = await axios.post('/api/client-management/proposals', formData);
      if (res.data.success) {
        toast.success('Proposal created successfully');
        setIsModalOpen(false);
        setFormData({ client_name: '', client_email: '', project_name: '', value: '', details: '', terms: defaultTerms });
        fetchProposals();
      }
    } catch (err) {
      toast.error('Failed to create proposal');
    }
  };

  const generatePDF = async (p, action = 'download') => {
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
    // Deep rich blue gradient-like header (solid fill)
    doc.setFillColor(10, 25, 47); // Dark navy blue
    doc.rect(0, 0, 210, 60, 'F');
    
    // Accent line at the bottom of the header
    doc.setFillColor(0, 212, 255); // Cyan/Bright Blue accent
    doc.rect(0, 60, 210, 2, 'F');

    // Accent shape in header (subtle polygon for modern look)
    doc.setFillColor(17, 34, 64);
    doc.triangle(150, 0, 210, 0, 210, 60, 'F');

    // Logo / Branding Text
    if (logo) {
      // Much larger, stunning logo display
      doc.addImage(logo, 'PNG', 15, 12, 45, 32);
      doc.setFontSize(26);
      doc.setTextColor(255, 255, 255);
      doc.setFont('helvetica', 'bold');
      doc.text('HATBALIYA', 65, 30);
      doc.setFontSize(14);
      doc.setTextColor(136, 146, 176); // Light slate
      doc.setFont('helvetica', 'normal');
      doc.text('TECHNOLOGIES', 65, 38);
    } else {
      doc.setFontSize(32);
      doc.setTextColor(255, 255, 255);
      doc.setFont('helvetica', 'bold');
      doc.text('HATBALIYA', 20, 32);
      doc.setFontSize(16);
      doc.setTextColor(136, 146, 176); 
      doc.setFont('helvetica', 'normal');
      doc.text('TECHNOLOGIES', 20, 42);
    }

    // Proposal Tag - aligned to the right
    doc.setFontSize(14);
    doc.setTextColor(0, 212, 255); // Cyan accent
    doc.setFont('helvetica', 'bold');
    doc.text('PROJECT PROPOSAL', 195, 30, { align: 'right' });
    doc.setFontSize(11);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(204, 214, 246);
    doc.text(`Date: ${new Date(p.created_at).toLocaleDateString()}`, 195, 38, { align: 'right' });

    // Client Details Section (Elegant floating card look)
    doc.setFillColor(245, 247, 250); // Very light grey blue
    doc.roundedRect(15, 75, 180, 45, 4, 4, 'F');
    // Subtle border for card
    doc.setDrawColor(226, 232, 240);
    doc.setLineWidth(0.5);
    doc.roundedRect(15, 75, 180, 45, 4, 4, 'S');
    
    doc.setTextColor(15, 23, 42); 
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text('PREPARED FOR', 25, 90);
    
    doc.setFontSize(16);
    doc.setTextColor(10, 25, 47); // Dark navy
    doc.text(p.client_name, 25, 102);
    
    if (p.client_email) {
      doc.setFontSize(11);
      doc.setFont('helvetica', 'normal');
      doc.setTextColor(100, 116, 139); 
      doc.text(p.client_email, 25, 110);
    }

    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(15, 23, 42);
    doc.text('PROPOSAL ID', 140, 90);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(10, 25, 47);
    doc.text(`PR-${p.id}`, 140, 102);

    // Project Details - Section Header
    doc.setFontSize(22);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(10, 25, 47);
    doc.text(p.project_name.toUpperCase(), 15, 140);
    
    // Underline accent
    doc.setDrawColor(0, 212, 255); // Cyan
    doc.setLineWidth(2);
    doc.line(15, 144, 45, 144);

    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(30, 41, 59);
    doc.text('Executive Summary & Scope of Work', 15, 158);
    
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(12);
    doc.setTextColor(71, 85, 105);
    const splitDetails = doc.splitTextToSize(p.details || 'Standard project scope applies. No additional details provided.', 180);
    doc.text(splitDetails, 15, 168);

    let finalY = 168 + (splitDetails.length * 6) + 15;

    // Pricing / Investment Table - Stunning Design
    autoTable(doc, {
      startY: finalY,
      head: [['Description', 'Estimated Investment']],
      body: [
        ['Core Project Development & Implementation', `Rs. ${Number(p.value).toLocaleString()}`]
      ],
      theme: 'grid',
      headStyles: { 
        fillColor: [10, 25, 47], 
        textColor: 255, 
        fontStyle: 'bold', 
        halign: 'left',
        fontSize: 12
      },
      bodyStyles: { 
        fontSize: 12, 
        textColor: [30, 41, 59],
        fillColor: [250, 251, 252] 
      },
      alternateRowStyles: {
        fillColor: [255, 255, 255]
      },
      columnStyles: {
        0: { cellWidth: 120 },
        1: { halign: 'right', fontStyle: 'bold', textColor: [10, 25, 47], fontSize: 13 } 
      },
      styles: { cellPadding: 10, lineColor: [226, 232, 240], lineWidth: 0.5 },
      margin: { left: 15, right: 15 }
    });

    finalY = doc.lastAutoTable.finalY + 25;

    // Terms and Conditions
    if (p.terms) {
      if (finalY > 230) {
        doc.addPage();
        finalY = 30;
      }
      
      doc.setFontSize(16);
      doc.setFont('helvetica', 'bold');
      doc.setTextColor(10, 25, 47);
      doc.text('Terms & Conditions', 15, finalY);
      
      // Underline accent
      doc.setDrawColor(0, 212, 255);
      doc.setLineWidth(1.5);
      doc.line(15, finalY + 3, 35, finalY + 3);

      doc.setFontSize(11);
      doc.setFont('helvetica', 'normal');
      doc.setTextColor(100, 116, 139);
      const splitTerms = doc.splitTextToSize(p.terms, 180);
      doc.text(splitTerms, 15, finalY + 12);
      
      finalY = finalY + 12 + (splitTerms.length * 6) + 30;
    }

    // Footer / Signoff on the bottom of every page
    const pageCount = doc.internal.getNumberOfPages();
    for(let i = 1; i <= pageCount; i++) {
      doc.setPage(i);
      const pageHeight = doc.internal.pageSize.height;
      
      // Add a footer graphic at the bottom
      doc.setFillColor(10, 25, 47);
      doc.rect(0, pageHeight - 20, 210, 20, 'F');
      doc.setFillColor(0, 212, 255);
      doc.rect(0, pageHeight - 20, 210, 1, 'F');

      doc.setFontSize(10);
      doc.setFont('helvetica', 'italic');
      doc.setTextColor(204, 214, 246);
      doc.text('Transforming your vision into reality. | Hatbaliya Technologies', 105, pageHeight - 12, { align: 'center' });
      doc.setFontSize(9);
      doc.setFont('helvetica', 'normal');
      doc.setTextColor(136, 146, 176);
      doc.text('www.hatbaliya.com', 105, pageHeight - 7, { align: 'center' });
    }

    if (action === 'download') {
      doc.save(`Proposal_${p.client_name.replace(/\s+/g, '_')}_PR${p.id}.pdf`);
      toast.success('Professional Proposal PDF downloaded!');
    } else if (action === 'email') {
      if (!p.client_email) {
        return toast.error('This proposal has no client email associated with it.');
      }
      const loadingToast = toast.loading('Generating and sending proposal email...');
      try {
        const pdfBlob = doc.output('blob');
        const formData = new FormData();
        formData.append('client_name', p.client_name);
        formData.append('client_email', p.client_email);
        formData.append('project_name', p.project_name);
        formData.append('proposal_pdf', pdfBlob, `Proposal_${p.client_name}_PR${p.id}.pdf`);

        const res = await axios.post(`/api/client-management/proposals/${p.id}/send-email`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (res.data.success) {
          toast.success(`Professional Proposal successfully emailed to ${p.client_email}!`, { id: loadingToast });
        } else {
          toast.error(res.data.message || 'Failed to send email', { id: loadingToast });
        }
      } catch (err) {
        console.error(err);
        toast.error(err.response?.data?.message || 'Technical error sending email', { id: loadingToast });
      }
    }
  };

  const handleGenerateContract = async (p) => {
    try {
      const payload = {
        proposal_id: p.id,
        client_name: p.client_name,
        document_content: `Contract Agreement for ${p.project_name}\n\nValue: ₹${p.value}\nDetails: ${p.details}`
      };
      const res = await axios.post('/api/client-management/contracts', payload);
      if (res.data.success) {
        toast.success('Contract generated and sent to Client Contracts module');
      }
    } catch (err) {
      toast.error('Failed to generate contract');
    }
  };

  return (
    <div className="p-4 md:p-6 relative">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
          <h2 className="text-2xl font-bold flex items-center gap-2 text-slate-800"><FileText /> Proposals & Approvals</h2>
          <p className="text-slate-500 text-sm mt-1">Generate professional PDF proposals, sign them, and convert to invoices.</p>
        </div>
        {!isClient && (
          <button onClick={() => setIsModalOpen(true)} className="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors shadow-sm">
            <Plus size={16} /> New Proposal
          </button>
        )}
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden overflow-x-auto">
        <div className="p-4 border-b border-slate-200 bg-slate-50 font-bold grid grid-cols-6 text-sm text-slate-700 min-w-[800px]">
          <div>Client Name</div>
          <div>Project Name</div>
          <div>Value</div>
          <div>Status</div>
          <div className="text-right col-span-2">Actions</div>
        </div>
        
        {loading ? (
          <div className="p-8 text-center text-slate-500">Loading...</div>
        ) : proposals.length === 0 ? (
          <div className="p-8 text-center text-slate-500">No proposals found</div>
        ) : (
          proposals.map(p => (
            <div key={p.id} className="p-4 border-b border-slate-100 flex items-center justify-between hover:bg-slate-50 transition-colors min-w-[800px]">
              <div className="grid grid-cols-6 w-full items-center text-sm gap-2">
                <div className="font-medium text-slate-800">
                  {p.client_name}
                  {p.client_email && <div className="text-xs text-slate-400 font-normal">{p.client_email}</div>}
                </div>
                <div className="text-slate-600">{p.project_name}</div>
                <div className="font-mono text-slate-800 font-bold">₹{Number(p.value).toLocaleString()}</div>
                <div>
                  <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold ${p.status === 'Fully Executed' || p.status === 'Approved' ? 'bg-green-100 text-green-700 border border-green-200' : p.status === 'Client Signed' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'bg-orange-100 text-orange-700 border border-orange-200'}`}>
                    {p.status === 'Fully Executed' || p.status === 'Approved' ? <CheckCircle size={12}/> : <Clock size={12}/>}
                    {p.status || 'Draft'}
                  </span>
                </div>
                <div className="text-right col-span-2 flex justify-end gap-2 items-center flex-wrap">
                  
                  {/* PDF Actions */}
                  <button 
                    onClick={() => generatePDF(p, 'download')}
                    className="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded text-xs font-bold flex items-center gap-1 transition-colors border border-slate-300"
                    title="Download Professional PDF"
                  >
                    <Download size={14} /> PDF
                  </button>
                  
                  {!isClient && (
                    <button 
                      onClick={() => generatePDF(p, 'email')}
                      className="bg-orange-50 hover:bg-orange-100 text-orange-600 px-3 py-1.5 rounded text-xs font-bold flex items-center gap-1 transition-colors border border-orange-200"
                      title="Send Proposal via Mail"
                    >
                      <Mail size={14} /> Email
                    </button>
                  )}

                  {/* Workflow Actions */}
                  {/* Client signs */}
                  {isClient && (p.status === 'Draft' || !p.status) && (
                    <button 
                      onClick={() => openSignModal(p.id)}
                      className="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded text-xs font-bold whitespace-nowrap transition-colors shadow-sm ml-2"
                    >
                      Sign Proposal
                    </button>
                  )}
                  
                  {/* Admin Countersigns */}
                  {isAdmin && p.status === 'Client Signed' && (
                    <button 
                      onClick={() => openSignModal(p.id)}
                      className="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded text-xs font-bold whitespace-nowrap transition-colors shadow-sm ml-2"
                    >
                      Counter Sign
                    </button>
                  )}

                  {/* Convert to Invoice */}
                  {isAdmin && p.status === 'Fully Executed' && (
                    <button 
                      onClick={() => handleConvertToInvoice(p)}
                      className="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-bold whitespace-nowrap transition-colors shadow-sm ml-2"
                    >
                      Convert to Bill
                    </button>
                  )}

                  {/* Legacy buttons */}
                  {!p.admin_approved && isAdmin && p.status !== 'Client Signed' && p.status !== 'Fully Executed' && (
                    <button 
                      onClick={() => approveProposal(p.id)}
                      className="bg-slate-500 hover:bg-slate-600 text-white px-3 py-1.5 rounded text-xs font-bold whitespace-nowrap transition-colors shadow-sm ml-2"
                    >
                      CEO Approve
                    </button>
                  )}

                  {isAdmin && (
                    <button 
                      onClick={() => handleDeleteProposal(p.id)}
                      className="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded text-xs font-bold flex items-center gap-1 transition-colors border border-red-200 ml-2"
                      title="Delete Proposal"
                    >
                      <Trash2 size={14} /> Delete
                    </button>
                  )}
                </div>
              </div>
            </div>
          ))
        )}
      </div>

      {isModalOpen && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-200 border border-slate-200">
            <div className="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
              <h3 className="font-bold text-slate-800 flex items-center gap-2 text-lg"><FileText size={20} className="text-orange-600"/> Create New Proposal</h3>
              <button onClick={() => setIsModalOpen(false)} className="text-slate-400 hover:text-slate-600 bg-white p-1 rounded-full shadow-sm border border-slate-200 transition-colors"><X size={18}/></button>
            </div>
            <form onSubmit={handleCreateProposal} className="p-6 space-y-5 overflow-y-auto flex-1">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-bold text-slate-700 mb-1.5">Client Name</label>
                  <input required type="text" value={formData.client_name} onChange={(e)=>setFormData({...formData, client_name: e.target.value})} className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none transition-all" placeholder="e.g. Acme Corporation" />
                </div>
                <div>
                  <label className="block text-sm font-bold text-slate-700 mb-1.5">Client Email</label>
                  <input required type="email" value={formData.client_email} onChange={(e)=>setFormData({...formData, client_email: e.target.value})} className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none transition-all" placeholder="client@example.com" />
                </div>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-bold text-slate-700 mb-1.5">Project Name</label>
                  <input required type="text" value={formData.project_name} onChange={(e)=>setFormData({...formData, project_name: e.target.value})} className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none transition-all" placeholder="CRM Integration" />
                </div>
                <div>
                  <label className="block text-sm font-bold text-slate-700 mb-1.5">Proposal Value (₹)</label>
                  <input required type="number" value={formData.value} onChange={(e)=>setFormData({...formData, value: e.target.value})} className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none transition-all font-mono" placeholder="25000" />
                </div>
              </div>
              <div>
                <label className="block text-sm font-bold text-slate-700 mb-1.5">Scope of Work / Details</label>
                <textarea required rows="4" value={formData.details} onChange={(e)=>setFormData({...formData, details: e.target.value})} className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none transition-all resize-none" placeholder="Provide a detailed executive summary and scope of work for the PDF..."></textarea>
              </div>
              <div>
                <label className="block text-sm font-bold text-slate-700 mb-1.5">Terms & Conditions</label>
                <textarea required rows="4" value={formData.terms} onChange={(e)=>setFormData({...formData, terms: e.target.value})} className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 outline-none transition-all resize-none text-sm text-slate-600"></textarea>
              </div>
              <div className="pt-2 flex justify-end gap-3">
                <button type="button" onClick={() => setIsModalOpen(false)} className="px-5 py-2.5 font-bold text-slate-500 hover:text-slate-800 transition-colors">Cancel</button>
                <button type="submit" className="px-6 py-2.5 bg-orange-600 text-white rounded-xl font-bold hover:bg-orange-700 shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                  Generate Proposal
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Signature Modal */}
      {signModalOpen && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200 border border-slate-200">
            <div className="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
              <h3 className="font-bold text-slate-800 flex items-center gap-2"><FileText className="text-emerald-600" size={18}/> E-Sign Proposal</h3>
              <button onClick={() => setSignModalOpen(false)} className="text-slate-400 hover:text-slate-600 transition-colors"><X size={18}/></button>
            </div>
            <div className="p-6">
              <label className="block text-sm font-bold text-slate-700 mb-2">Type your full name to sign electronically:</label>
              <input 
                type="text" 
                value={signatureText}
                onChange={(e) => setSignatureText(e.target.value)}
                className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none transition-all font-serif italic text-lg"
                placeholder="John Doe"
              />
              <p className="text-xs text-slate-500 mt-3 flex items-center gap-1">
                <CheckCircle size={12} className="text-emerald-500"/> By signing, I agree to the terms and conditions outlined in the proposal.
              </p>
              
              <div className="mt-6 flex justify-end gap-3">
                <button onClick={() => setSignModalOpen(false)} className="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800">Cancel</button>
                <button onClick={handleSignProposal} className="px-6 py-2.5 text-sm bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 shadow-md">Submit Signature</button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ClientProposals;
