import React, { useState, useEffect } from 'react';
import axios from '../axiosConfig';
import { Search, Plus, FileText, Download, Printer, Filter, Trash2, Mail } from 'lucide-react';
import { toast } from 'sonner';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
const logoImg = '/erp_logo.png';

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
    doc.setFillColor(20, 25, 35); // Sleek dark aesthetic
    doc.rect(0, 0, 210, 65, 'F');
    // Subtle geometric pattern
    doc.setFillColor(25, 31, 44);
    doc.circle(210, 0, 45, 'F');
    doc.setFillColor(34, 197, 94); // Emerald accent
    doc.rect(0, 65, 210, 1.5, 'F');

    // Logo / Branding Text
    if (logo) {
      doc.addImage(logo, 'PNG', 15, 15, 35, 35);
      doc.setFontSize(26);
      doc.setTextColor(255, 255, 255);
      doc.setFont('helvetica', 'bold');
      doc.text('HATBALIYA', 55, 32);
      doc.setFontSize(13);
      doc.setTextColor(156, 163, 175); 
      doc.setFont('helvetica', 'normal');
      doc.text('TECHNOLOGIES', 55, 40);
    } else {
      doc.setFontSize(28);
      doc.setTextColor(255, 255, 255);
      doc.setFont('helvetica', 'bold');
      doc.text('HATBALIYA', 20, 32);
      doc.setFontSize(14);
      doc.setTextColor(156, 163, 175); 
      doc.setFont('helvetica', 'normal');
      doc.text('TECHNOLOGIES', 20, 40);
    }

    // Title Tag
    doc.setFontSize(18);
    doc.setTextColor(255, 255, 255);
    doc.setFont('helvetica', 'bold');
    doc.text('TAX INVOICE', 195, 32, { align: 'right' });
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(34, 197, 94); // Emerald text
    doc.text(`NO: ${invId}`, 195, 40, { align: 'right' });

    // Client Details Section (Modern floating card)
    doc.setFillColor(249, 250, 251); 
    doc.roundedRect(15, 80, 180, 40, 5, 5, 'F');
    
    doc.setTextColor(107, 114, 128); 
    doc.setFontSize(10);
    doc.setFont('helvetica', 'bold');
    doc.text('BILLED TO:', 22, 92);
    
    doc.setFontSize(18);
    doc.setTextColor(31, 41, 55); 
    doc.text(client, 22, 103);
    
    if (clientEmail) {
      doc.setFontSize(11);
      doc.setFont('helvetica', 'normal');
      doc.text(clientEmail, 22, 111);
    }

    doc.setFontSize(10);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(107, 114, 128);
    doc.text('ISSUE DATE:', 140, 92);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(31, 41, 55);
    doc.text(date, 140, 100);

    // Modern Pricing Table
    autoTable(doc, {
      startY: 135,
      head: [['ITEM DESCRIPTION', 'QTY', 'RATE', 'AMOUNT']],
      body: items.map(item => {
        const q = parseFloat(item.qty) || 0;
        const r = parseFloat(item.rate) || 0;
        return [
          item.description,
          q,
          `${currency === 'INR' ? 'Rs.' : currency} ${r.toFixed(2)}`,
          `${currency === 'INR' ? 'Rs.' : currency} ${(q * r).toFixed(2)}`
        ];
      }),
      theme: 'plain',
      headStyles: { fillColor: [243, 244, 246], textColor: [75, 85, 99], fontSize: 10, fontStyle: 'bold' },
      bodyStyles: { textColor: [55, 65, 81], fontSize: 11 },
      alternateRowStyles: { fillColor: [252, 253, 254] },
      styles: { cellPadding: 8, lineColor: [229, 231, 235], lineWidth: 0.1 },
      margin: { left: 15, right: 15 }
    });

    const finalY = doc.lastAutoTable.finalY + 15;
    
    // Totals Box
    doc.setFontSize(11);
    doc.setTextColor(107, 114, 128);
    doc.setFont('helvetica', 'normal');
    doc.text('Subtotal:', 140, finalY);
    doc.text(`Tax (18%):`, 140, finalY + 8);
    
    doc.setTextColor(31, 41, 55);
    doc.text(`${currency === 'INR' ? 'Rs.' : currency} ${subtotal.toFixed(2)}`, 195, finalY, { align: 'right' });
    doc.text(`${currency === 'INR' ? 'Rs.' : currency} ${tax.toFixed(2)}`, 195, finalY + 8, { align: 'right' });
    
    // Call-to-action total block
    doc.setFillColor(20, 25, 35);
    doc.roundedRect(130, finalY + 15, 65, 14, 3, 3, 'F');
    doc.setFontSize(12);
    doc.setTextColor(255, 255, 255);
    doc.setFont("helvetica", "bold");
    doc.text('TOTAL DUE', 135, finalY + 24);
    doc.text(`${currency === 'INR' ? 'Rs.' : currency} ${total.toFixed(2)}`, 190, finalY + 24, { align: 'right' });

    // Footer
    doc.setFontSize(9);
    doc.setTextColor(156, 163, 175);
    doc.setFont('helvetica', 'normal');
    doc.text('Thank you for your business. Please process payment within 15 days.', 105, 280, { align: 'center' });

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
