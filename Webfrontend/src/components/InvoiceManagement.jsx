import React, { useState, useEffect } from 'react';
import axios from '../axiosConfig';
import { Search, Plus, FileText, Download, Printer, Filter, Trash2, Mail } from 'lucide-react';
import { toast } from 'sonner';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import logoImg from '../assets/logo_transparent.png';

const InvoiceManagement = ({ initialTab = 'history' }) => {
  const [view, setView] = useState(initialTab === 'generate' ? 'generate' : 'list');
  const [invoices, setInvoices] = useState([]);
  const [loading, setLoading] = useState(true);

  const [client, setClient] = useState('');
  const [clientEmail, setClientEmail] = useState('');
  const [date, setDate] = useState(new Date().toISOString().split('T')[0]);
  const [currency, setCurrency] = useState('INR');
  const [isRecurring, setIsRecurring] = useState(false);
  const [items, setItems] = useState([{ description: '', qty: 1, rate: 0 }]);

  useEffect(() => {
    fetchInvoices();
  }, []);

  const fetchInvoices = async () => {
    try {
      const res = await axios.get('/api/invoices');
      if (res.data.success) {
        setInvoices(res.data.data);
      }
    } catch (err) {
      toast.error('Error fetching invoice history');
    } finally {
      setLoading(false);
    }
  };

  const subtotal = items.reduce((acc, item) => acc + ((parseFloat(item.qty) || 0) * (parseFloat(item.rate) || 0)), 0);
  const tax = subtotal * 0.18;
  const total = subtotal + tax;

  const handleAddItem = () => {
    setItems([...items, { description: '', qty: 1, rate: 0 }]);
  };

  const handleRemoveItem = (index) => {
    if (items.length > 1) {
      setItems(items.filter((_, i) => i !== index));
    }
  };

  const handleItemChange = (index, field, value) => {
    const newItems = [...items];
    newItems[index][field] = value;
    setItems(newItems);
  };

  const handleGenerateInvoice = async () => {
    if (!client) return toast.error('Please select a client');

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
    const invId = `INV-${Math.floor(1000 + Math.random() * 9000)}`;

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
    doc.text('TAX INVOICE', 195, 25, { align: 'right' });
    doc.setFontSize(11);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(204, 214, 246);
    doc.text(`ID: ${invId}`, 195, 32, { align: 'right' });

    // Client Details Section (Elegant floating card look)
    doc.setFillColor(245, 247, 250); // Very light grey blue
    doc.roundedRect(15, 60, 180, 35, 4, 4, 'F');
    doc.setDrawColor(226, 232, 240);
    doc.setLineWidth(0.5);
    doc.roundedRect(15, 60, 180, 35, 4, 4, 'S');

    doc.setTextColor(15, 23, 42); 
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.text('BILLED TO', 20, 72);
    
    doc.setFontSize(14);
    doc.setTextColor(10, 25, 47); // Dark navy
    doc.text(client, 20, 82);

    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(15, 23, 42);
    doc.text('INVOICE DATE', 140, 72);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(10, 25, 47);
    doc.text(date, 140, 82);

    autoTable(doc, {
      startY: 105,
      head: [['Description', 'Qty', 'Rate', 'Amount']],
      body: items.map(item => {
        const q = parseFloat(item.qty) || 0;
        const r = parseFloat(item.rate) || 0;
        return [
          item.description,
          q,
          `₹${r.toFixed(2)}`,
          `₹${(q * r).toFixed(2)}`
        ];
      }),
      theme: 'grid',
      headStyles: { fillColor: [10, 25, 47], textColor: 255, fontSize: 11, fontStyle: 'bold' },
      bodyStyles: { textColor: 50, fontSize: 10 },
      alternateRowStyles: { fillColor: [248, 250, 252] },
      styles: { cellPadding: 6 }
    });

    const finalY = doc.lastAutoTable.finalY + 10;
    doc.setFontSize(11);
    doc.setTextColor(100, 116, 139);
    doc.text(`Subtotal: ₹${subtotal.toFixed(2)}`, 140, finalY);
    doc.text(`Tax (18%): ₹${tax.toFixed(2)}`, 140, finalY + 7);
    
    // Total section
    doc.setFillColor(241, 245, 249);
    doc.roundedRect(125, finalY + 12, 70, 12, 2, 2, 'F');
    doc.setFontSize(13);
    doc.setTextColor(10, 25, 47);
    doc.setFont("helvetica", "bold");
    doc.text(`TOTAL: ₹${total.toFixed(2)}`, 130, finalY + 20);

    // Provide immediate feedback to user via download
    doc.save(`${invId}_${client}.pdf`);
    
    const pdfBlob = doc.output('blob');

    const formData = new FormData();
    formData.append('id', invId);
    formData.append('client_name', client);
    formData.append('client_email', clientEmail);
    formData.append('total_amount', total);
    formData.append('invoice_date', date);
    formData.append('currency', currency);
    formData.append('is_recurring', isRecurring);
    formData.append('items', JSON.stringify(items));
    formData.append('invoice_pdf', pdfBlob, `Invoice_${invId}.pdf`);

    try {
      const res = await axios.post('/api/invoices', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      
      if (res.data.success) {
        toast.success('Invoice generated and saved');
        fetchInvoices();
        setView('list');
        setItems([{ description: '', qty: 1, rate: 0 }]);
        setClient('');
        setClientEmail('');
      } else {
        toast.error(res.data.message || 'Error generating invoice');
      }
    } catch (err) {
      console.error('Invoice save error:', err);
      toast.error(err.response?.data?.message || 'Error saving invoice to database');
    }
  };

  const handleSendEmail = async (id) => {
    const loadingToast = toast.loading(`Sending email for invoice ${id}...`);
    try {
      const res = await axios.post(`/api/invoices/send-email/${id}`);
      if (res.data.success) {
        toast.success('Email sent successfully', { id: loadingToast });
      } else {
        toast.error(res.data.message || 'Failed to send email', { id: loadingToast });
      }
    } catch (err) {
      toast.error(err.response?.data?.message || 'Technical error sending email', { id: loadingToast });
    }
  };

  return (
    <div className="space-y-8">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h3 className="text-2xl font-bold text-blue-950">Invoice Management</h3>
          <p className="text-slate-500 font-medium">Generate professional invoices and track client payments</p>
        </div>
        <div className="flex bg-gray-100 p-1 rounded-xl">
          <button
            onClick={() => setView('list')}
            className={`px-4 py-2 rounded-lg text-sm font-bold transition-all ${view === 'list' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-gray-700'}`}
          >
            Invoice History
          </button>
          <button
            onClick={() => setView('generate')}
            className={`px-4 py-2 rounded-lg text-sm font-bold transition-all ${view === 'generate' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-gray-700'}`}
          >
            Generate Invoice
          </button>
        </div>
      </div>

      {view === 'generate' ? (
        <div className="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div className="space-y-4">
              <label className="block text-sm font-bold text-gray-700">Client Name</label>
              <input
                type="text"
                placeholder="Acme Corp"
                className="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none"
                value={client}
                onChange={(e) => setClient(e.target.value)}
              />
            </div>
            <div className="space-y-4">
              <label className="block text-sm font-bold text-gray-700">Client Email (Optional)</label>
              <input
                type="email"
                placeholder="client@example.com"
                className="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none"
                value={clientEmail}
                onChange={(e) => setClientEmail(e.target.value)}
              />
            </div>
            <div className="space-y-4">
              <label className="block text-sm font-bold text-gray-700">Invoice Date</label>
              <input
                type="date"
                className="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none"
                value={date}
                onChange={(e) => setDate(e.target.value)}
              />
            </div>
            <div className="space-y-4">
              <label className="block text-sm font-bold text-gray-700">Currency</label>
              <select
                className="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none"
                value={currency}
                onChange={(e) => setCurrency(e.target.value)}
              >
                <option value="INR">INR</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
              </select>
            </div>
          </div>
          <div className="mb-8">
            <label className="flex items-center gap-2 cursor-pointer w-max">
              <input 
                type="checkbox" 
                className="w-4 h-4 text-orange-600 rounded border-gray-300 focus:ring-orange-500"
                checked={isRecurring}
                onChange={e => setIsRecurring(e.target.checked)}
              />
              <span className="text-sm font-bold text-gray-700">Set as Recurring Invoice (Monthly)</span>
            </label>
          </div>

          <div className="border border-gray-100 rounded-xl overflow-hidden mb-8">
            <table className="w-full text-left">
              <thead className="bg-gray-50 border-b border-gray-100">
                <tr>
                  <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Description</th>
                  <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Quantity</th>
                  <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Rate / Price</th>
                  <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-50">
                {items.map((item, index) => (
                  <tr key={index}>
                    <td className="px-6 py-4">
                      <input
                        placeholder="Development Services"
                        className="w-full bg-transparent outline-none text-sm"
                        value={item.description}
                        onChange={(e) => handleItemChange(index, 'description', e.target.value)}
                      />
                    </td>
                    <td className="px-6 py-4">
                      <input
                        type="number"
                        className="w-20 bg-transparent outline-none text-sm"
                        value={item.qty}
                        onChange={(e) => handleItemChange(index, 'qty', e.target.value)}
                      />
                    </td>
                    <td className="px-6 py-4">
                      <input
                        type="number"
                        placeholder="0.00"
                        className="w-24 bg-transparent outline-none text-sm"
                        value={item.rate}
                        onChange={(e) => handleItemChange(index, 'rate', e.target.value)}
                      />
                    </td>
                    <td className="px-6 py-4 text-sm font-bold text-blue-950 flex items-center justify-between">
                      {`₹${((parseFloat(item.qty) || 0) * (parseFloat(item.rate) || 0)).toFixed(2)}`}
                      <button
                        onClick={() => handleRemoveItem(index)}
                        className="text-gray-300 hover:text-red-500 transition-colors"
                      >
                        <Trash2 size={14} />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            <button
              onClick={handleAddItem}
              className="w-full py-4 text-sm font-bold text-orange-600 hover:bg-orange-50 transition-colors flex items-center justify-center gap-2"
            >
              <Plus size={16} /> Add Item
            </button>
          </div>

          <div className="flex justify-between items-end">
            <div className="space-y-2">
              <p className="text-sm text-slate-500">Note: This will generate a professional PDF invoice and save it to the server.</p>
            </div>
            <div className="text-right space-y-4">
              <div className="space-y-1">
                <p className="text-sm font-medium text-slate-500">Subtotal: ₹{subtotal.toFixed(2)}</p>
                <p className="text-sm font-medium text-slate-500">Tax (18%): ₹{tax.toFixed(2)}</p>
                <p className="text-2xl font-extrabold text-orange-600">Total: ₹{total.toFixed(2)}</p>
              </div>
              <button
                onClick={handleGenerateInvoice}
                className="bg-orange-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-orange-700 transition-all shadow-lg shadow-blue-200"
              >
                Generate & Save
              </button>
            </div>
          </div>
        </div>
      ) : (
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div className="p-6 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
            <h3 className="text-xl font-bold text-blue-950">Invoice History</h3>
            <div className="flex flex-wrap gap-4">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
                <input placeholder="Search invoices..." className="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 outline-none" />
              </div>
              <button className="p-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-slate-500">
                <Filter size={20} />
              </button>
            </div>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead className="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Invoice ID</th>
                  <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Client</th>
                  <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                  <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                  <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {loading ? (
                  <tr><td colSpan="5" className="px-6 py-8 text-center text-slate-500">Loading history...</td></tr>
                ) : invoices.length === 0 ? (
                  <tr><td colSpan="5" className="px-6 py-8 text-center text-slate-500">No records found.</td></tr>
                ) : invoices.map((inv) => (
                  <tr key={inv.id} className="hover:bg-gray-50 transition-colors">
                    <td className="px-6 py-4 text-sm font-bold text-orange-600">{inv.id}</td>
                    <td className="px-6 py-4 text-sm font-semibold text-blue-950">
                      {inv.client_name || inv.client}
                      {inv.client_email && <div className="text-xs text-gray-400">{inv.client_email}</div>}
                    </td>
                    <td className="px-6 py-4 text-sm font-bold text-blue-950">₹{inv.total_amount || inv.amount}</td>
                    <td className="px-6 py-4 text-sm text-slate-600">{new Date(inv.invoice_date).toLocaleDateString()}</td>
                    <td className="px-6 py-4 text-right">
                      <div className="flex flex-wrap items-center justify-end gap-2 text-gray-400">
                        <button onClick={() => handleSendEmail(inv.id)} title="Email Client" className="p-2 hover:text-blue-600 hover:bg-blue-50 rounded-lg"><Mail size={16} /></button>
                        <button className="p-2 hover:text-orange-600 hover:bg-orange-50 rounded-lg"><Download size={16} /></button>
                        <button className="p-2 hover:text-slate-600 hover:bg-gray-50 rounded-lg"><Printer size={16} /></button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
};

export default InvoiceManagement;
