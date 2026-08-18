import React, { useState, useEffect } from 'react';
import axios from '../axiosConfig';
import { DollarSign, Plus, Trash2, Calendar, FileText, PieChart, BarChart, Sparkles, TrendingUp, Edit2 } from 'lucide-react';
import { toast } from 'sonner';

export default function ExpenseTracker() {
  const [expenses, setExpenses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [editId, setEditId] = useState(null);

  const [form, setForm] = useState({ type: 'expense', category: 'Software', amount: '', date: new Date().toISOString().split('T')[0], description: '', currency: 'INR' });
  const [aiLoading, setAiLoading] = useState(false);

  useEffect(() => {
    fetchExpenses();
  }, []);

  const fetchExpenses = async () => {
    try {
      const res = await axios.get('/api/finance/transactions');
      if (res.data.success) {
        setExpenses(res.data.transactions);
      }
    } catch (err) {
      toast.error('Error fetching expenses');
    } finally {
      setLoading(false);
    }
  };

  const handleAiCategorize = async () => {
    if (!form.description) return toast.error('Please enter a description first to auto-categorize.');
    setAiLoading(true);
    try {
      const res = await axios.post('/api/finance/ai-categorize', { description: form.description });
      if (res.data.success) {
        setForm({ ...form, category: res.data.category });
        toast.success(`AI selected category: ${res.data.category}`);
      }
    } catch (err) {
      toast.error('Error categorizing expense');
    } finally {
      setAiLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!form.amount || !form.date) return;
    
    // Simulate exchange rate if currency is not INR (just for demo purposes)
    const rate = form.currency === 'USD' ? 83.5 : (form.currency === 'EUR' ? 90.2 : 1);
    const amount_base = parseFloat(form.amount) * rate;

    try {
      if (editId) {
        const res = await axios.put(`/api/finance/transactions/${editId}`, {
          ...form,
          exchange_rate: rate,
          amount_base: amount_base
        });
        if (res.data.success) {
          toast.success('Transaction updated successfully');
          setForm({ type: 'expense', category: 'Software', amount: '', date: new Date().toISOString().split('T')[0], description: '', currency: 'INR' });
          setEditId(null);
          fetchExpenses();
        }
      } else {
        const res = await axios.post('/api/finance/transactions', {
          ...form,
          exchange_rate: rate,
          amount_base: amount_base
        });
        if (res.data.success) {
          toast.success('Transaction logged successfully');
          setForm({ type: 'expense', category: 'Software', amount: '', date: new Date().toISOString().split('T')[0], description: '', currency: 'INR' });
          fetchExpenses();
        }
      }
    } catch (err) {
      toast.error(editId ? 'Error updating transaction' : 'Error logging transaction');
    }
  };

  const handleEdit = (transaction) => {
    setEditId(transaction.id);
    setForm({
      type: transaction.type,
      category: transaction.category,
      amount: transaction.amount,
      date: new Date(transaction.date).toISOString().split('T')[0],
      description: transaction.description,
      currency: transaction.currency || 'INR'
    });
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this transaction?')) return;
    try {
      const res = await axios.delete(`/api/finance/transactions/${id}`);
      if (res.data.success) {
        toast.success('Transaction deleted successfully');
        fetchExpenses();
      }
    } catch (err) {
      toast.error('Error deleting transaction');
    }
  };

  const total = expenses.filter(e => e.type === 'expense').reduce((sum, e) => sum + parseFloat(e.amount_base), 0);
  const totalIncome = expenses.filter(e => e.type === 'income').reduce((sum, e) => sum + parseFloat(e.amount_base), 0);

  return (
    <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-xl font-bold text-blue-950">Income & Expense Management</h2>
          <p className="text-sm text-slate-500">Track company operational expenditures with AI</p>
        </div>
        <div className="flex gap-4">
          <div className="bg-green-50 text-green-700 px-4 py-2 rounded-xl border border-green-100 text-right">
            <span className="text-xs uppercase font-bold tracking-wider block">Total Income</span>
            <span className="text-lg font-black">₹{totalIncome.toLocaleString('en-IN')}</span>
          </div>
          <div className="bg-red-50 text-red-700 px-4 py-2 rounded-xl border border-red-100 text-right">
            <span className="text-xs uppercase font-bold tracking-wider block">Total Expenditure</span>
            <span className="text-lg font-black">₹{total.toLocaleString('en-IN')}</span>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* Form */}
        <form onSubmit={handleSubmit} className="md:col-span-1 bg-gray-50 p-5 rounded-2xl border border-gray-100 space-y-4 overflow-y-auto flex-1">
          <h3 className="font-bold text-blue-900 text-sm">{editId ? 'Edit Transaction' : 'Log Transaction'}</h3>
          
          <div className="space-y-1">
            <label className="text-xs font-bold text-slate-500 uppercase">Type</label>
            <div className="flex gap-2">
              <button type="button" onClick={() => setForm({...form, type: 'income', category: 'Sales'})} className={`flex-1 py-1.5 text-sm rounded-lg border font-bold ${form.type === 'income' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-slate-600 border-gray-300'}`}>Income</button>
              <button type="button" onClick={() => setForm({...form, type: 'expense', category: 'Software'})} className={`flex-1 py-1.5 text-sm rounded-lg border font-bold ${form.type === 'expense' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-slate-600 border-gray-300'}`}>Expense</button>
            </div>
          </div>

          <div className="space-y-1">
            <label className="text-xs font-bold text-slate-500 uppercase">Description</label>
            <div className="flex gap-2">
              <input
                type="text"
                required
                value={form.description}
                onChange={e => setForm({ ...form, description: e.target.value })}
                placeholder="e.g. AWS Hosting"
                className="flex-1 bg-white border border-gray-200 px-3 py-2 rounded-xl text-sm"
              />
              <button 
                type="button" 
                onClick={handleAiCategorize} 
                disabled={aiLoading}
                className="bg-orange-100 hover:bg-orange-200 text-orange-700 px-3 py-2 rounded-xl text-sm font-bold flex items-center justify-center transition-colors"
                title="AI Expense Categorization"
              >
                <Sparkles size={16} className={aiLoading ? "animate-spin" : ""} />
              </button>
            </div>
          </div>

          <div className="space-y-1">
            <label className="text-xs font-bold text-slate-500 uppercase">Category</label>
            <select
              value={form.category}
              onChange={e => setForm({ ...form, category: e.target.value })}
              className="w-full bg-white border border-gray-200 px-3 py-2 rounded-xl text-sm"
            >
              {form.type === 'income' ? (
                <>
                  <option value="Sales">Sales</option>
                  <option value="Consulting">Consulting</option>
                  <option value="Other Income">Other Income</option>
                </>
              ) : (
                <>
                  <option value="Salary">Salary</option>
                  <option value="Software">Software & SaaS</option>
                  <option value="Office Rent">Office Rent / Rent</option>
                  <option value="Marketing">Marketing / Ads</option>
                  <option value="Travel">Travel & Lodging</option>
                  <option value="Other">Other Miscellaneous</option>
                </>
              )}
            </select>
          </div>

          <div className="grid grid-cols-3 gap-2">
            <div className="col-span-2 space-y-1">
              <label className="text-xs font-bold text-slate-500 uppercase">Amount</label>
              <input
                type="number"
                required
                value={form.amount}
                onChange={e => setForm({ ...form, amount: e.target.value })}
                placeholder="0.00"
                className="w-full bg-white border border-gray-200 px-3 py-2 rounded-xl text-sm"
              />
            </div>
            <div className="col-span-1 space-y-1">
              <label className="text-xs font-bold text-slate-500 uppercase">Currency</label>
              <select
                value={form.currency}
                onChange={e => setForm({ ...form, currency: e.target.value })}
                className="w-full bg-white border border-gray-200 px-3 py-2 rounded-xl text-sm"
              >
                <option value="INR">INR</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
              </select>
            </div>
          </div>

          <div className="space-y-1">
            <label className="text-xs font-bold text-slate-500 uppercase">Date</label>
            <input
              type="date"
              required
              value={form.date}
              onChange={e => setForm({ ...form, date: e.target.value })}
              className="w-full bg-white border border-gray-200 px-3 py-2 rounded-xl text-sm"
            />
          </div>
          
          <div className="flex gap-2">
            <button type="submit" className={`flex-1 py-2.5 text-white rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-1 ${form.type === 'income' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'}`}>
              <Plus size={16} /> {editId ? 'Update' : 'Log'} Transaction
            </button>
            {editId && (
              <button 
                type="button" 
                onClick={() => {
                  setEditId(null);
                  setForm({ type: 'expense', category: 'Software', amount: '', date: new Date().toISOString().split('T')[0], description: '', currency: 'INR' });
                }} 
                className="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-sm font-bold transition-colors"
              >
                Cancel
              </button>
            )}
          </div>
        </form>

        {/* List */}
        <div className="md:col-span-2 space-y-3">
          <h3 className="font-bold text-blue-900 text-sm">Recent Transactions</h3>
          <div className="overflow-y-auto max-h-[500px] border border-gray-100 rounded-xl divide-y divide-gray-100">
            {expenses.length === 0 ? (
              <div className="p-8 text-center text-slate-500">No transactions found.</div>
            ) : expenses.map(e => (
              <div key={e.id} className="group flex justify-between items-center p-4 bg-white hover:bg-gray-50 transition-colors">
                <div className="flex items-center gap-4">
                  <div className={`p-2 rounded-lg ${e.type === 'income' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'}`}>
                    {e.type === 'income' ? <TrendingUp size={20} /> : <DollarSign size={20} />}
                  </div>
                  <div>
                    <h4 className="font-bold text-blue-950 text-sm">{e.description}</h4>
                    <p className="text-xs text-slate-500 mt-0.5">{e.category} • {new Date(e.date).toLocaleDateString()}</p>
                  </div>
                </div>
                <div className="text-right flex flex-col items-end gap-2">
                  <div>
                    <span className={`font-black ${e.type === 'income' ? 'text-green-600' : 'text-blue-950'}`}>
                      {e.currency === 'USD' ? '$' : e.currency === 'EUR' ? '€' : '₹'}
                      {parseFloat(e.amount).toLocaleString('en-IN')}
                    </span>
                    {e.currency !== 'INR' && (
                      <p className="text-[10px] text-gray-400 mt-1">₹{parseFloat(e.amount_base).toLocaleString('en-IN')}</p>
                    )}
                  </div>
                  <div className="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button 
                      onClick={() => handleEdit(e)}
                      className="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                      title="Edit Transaction"
                    >
                      <Edit2 size={16} />
                    </button>
                    <button 
                      onClick={() => handleDelete(e.id)}
                      className="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                      title="Delete Transaction"
                    >
                      <Trash2 size={16} />
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
