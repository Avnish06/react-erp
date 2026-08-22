import React, { useState, useEffect } from 'react';
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';
import { Download, Edit, RefreshCw, Printer, Info, Save } from 'lucide-react';
import { toast } from 'sonner';
const logoImg = '/erp_logo.png';

export default function SalarySlipGenerator({ payroll = null, onClose }) {
  const [slip, setSlip] = useState({
    companyName: 'HATBALIYA TECHNOLOGIES',
    tagline: 'Your Trusted Technology Partner',
    address: 'Plot 93, Rajendra Park, Sector 105, Gurugram – 122001',
    phone: '+91 98765 43210',
    email: 'info@hatbaliyatechnologies.com',
    website: 'www.hatbaliyatechnologies.com',
    gstin: '09ABCDE1234F1Z5',
    monthYear: '',
    employeeId: '',
    joiningDate: '',
    department: '',
    designation: '',
    payMode: 'Bank Transfer',
    workingDays: 26,
    paymentDate: '',
    employeeName: '',
    fatherName: '',
    employeeCode: '',
    location: '',
    bankName: '',
    bankAcc: '',
    ifscCode: '',
    panNo: '',
    uanNo: '',
    
    // Earnings
    basicSalary: 0,
    hra: 0,
    conveyance: 0,
    specialAllowance: 0,
    medicalAllowance: 0,
    lta: 0,
    otherAllowance: 0,

    // Deductions
    pf: 0,
    esi: 0,
    profTax: 0,
    tds: 0,
    leaveDeduction: 0,
    otherDeduction: 0,
  });

  useEffect(() => {
    if (payroll) {
      setSlip(prev => ({
        ...prev,
        employeeName: payroll.employee_name || prev.employeeName,
        monthYear: payroll.month_year || prev.monthYear,
        basicSalary: Number(payroll.basic_salary) || 0,
        hra: Number(payroll.hra) || 0,
        conveyance: Number(payroll.da) || 0, // da mapped to conveyance
        specialAllowance: Number(payroll.bonus) || 0, // bonus mapped to special
        otherDeduction: Number(payroll.deductions) || 0,
      }));
    }
  }, [payroll]);

  const [isEditing, setIsEditing] = useState(false);

  const handleFieldChange = (e) => {
    const { name, value } = e.target;
    setSlip(prev => ({
      ...prev,
      [name]: ['basicSalary', 'hra', 'conveyance', 'specialAllowance', 'medicalAllowance', 'lta', 'otherAllowance', 'pf', 'esi', 'profTax', 'tds', 'leaveDeduction', 'otherDeduction', 'workingDays'].includes(name)
        ? Number(value) || 0
        : value
    }));
  };

  const totalEarnings = 
    slip.basicSalary + 
    slip.hra + 
    slip.conveyance + 
    slip.specialAllowance + 
    slip.medicalAllowance + 
    slip.lta + 
    slip.otherAllowance;

  const totalDeductions = 
    slip.pf + 
    slip.esi + 
    slip.profTax + 
    slip.tds + 
    slip.leaveDeduction + 
    slip.otherDeduction;

  const netPay = totalEarnings - totalDeductions;

  // Convert numbers to words (Rupees)
  const numberToWords = (num) => {
    const a = ['', 'One ', 'Two ', 'Three ', 'Four ', 'Five ', 'Six ', 'Seven ', 'Eight ', 'Nine ', 'Ten ', 'Eleven ', 'Twelve ', 'Thirteen ', 'Fourteen ', 'Fifteen ', 'Sixteen ', 'Seventeen ', 'Eighteen ', 'Nineteen '];
    const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    
    if ((num = num.toString()).length > 9) return 'overflow';
    let n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
    if (!n) return ''; 
    let str = '';
    str += (Number(n[1]) !== 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'Crore ' : '';
    str += (Number(n[2]) !== 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'Lakh ' : '';
    str += (Number(n[3]) !== 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'Thousand ' : '';
    str += (Number(n[4]) !== 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'Hundred ' : '';
    str += (Number(n[5]) !== 0) ? ((str !== '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) : '';
    return str ? str + 'Rupees Only' : 'Zero Rupees';
  };

  const handleDownloadPDF = async () => {
    const element = document.getElementById('salary-slip-content');
    if (!element) return;
    toast.info('Generating PDF quality slip...');
    
    try {
      const canvas = await html2canvas(element, {
        scale: 2,
        useCORS: true,
        logging: false,
        scrollX: 0,
        scrollY: 0
      });
      const imgData = canvas.toDataURL('image/png', 1.0);
      const pdf = new jsPDF('p', 'mm', 'a4');
      const imgWidth = 210;
      const imgHeight = (canvas.height * imgWidth) / canvas.width;
      
      pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
      pdf.save(`Salary_Slip_${slip.employeeName.replace(/\s+/g, '_')}_${slip.monthYear}.pdf`);
      toast.success('Salary Slip PDF Downloaded!');
    } catch (e) {
      console.error("PDF Generation Error:", e);
      toast.error('Failed to generate PDF download.');
    }
  };

  return (
    <div className="bg-slate-900/60 backdrop-blur-sm fixed inset-0 z-50 overflow-y-auto p-4 md:p-8 flex justify-center items-start">
      <div className="bg-white rounded-3xl w-full max-w-4xl shadow-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-300">
        
        {/* Controls strip */}
        <div className="bg-slate-50 border-b border-gray-100 p-4 px-6 flex items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <span className="text-xl">🖨️</span>
            <div>
              <h3 className="font-bold text-blue-950 text-sm">Interactive Salary Slip</h3>
              <p className="text-[11px] text-slate-500">Edit fields directly below to recalculate Net Pay instantly</p>
            </div>
          </div>
          <div className="flex gap-2">
            <button 
              onClick={() => setIsEditing(!isEditing)}
              className={`px-4 py-2 text-xs font-bold rounded-xl transition-all ${isEditing ? 'bg-orange-600 text-white' : 'bg-white border border-gray-200 text-slate-600 hover:bg-gray-50'}`}
            >
              {isEditing ? <><Save size={13} className="inline mr-1" /> Save Changes</> : <><Edit size={13} className="inline mr-1" /> Edit Slip</>}
            </button>
            <button 
              onClick={handleDownloadPDF}
              className="px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-md transition-all flex items-center gap-1.5"
            >
              <Download size={13} /> Download PDF
            </button>
            <button onClick={onClose} className="px-4 py-2 text-xs font-bold bg-gray-200 text-slate-600 rounded-xl hover:bg-gray-300 transition-all">
              Close
            </button>
          </div>
        </div>

        {/* Print Content Area */}
        <div className="flex-1 overflow-y-auto w-full bg-slate-100 p-8">
          <div id="salary-slip-content" className="bg-white font-sans text-slate-800 text-[11px] leading-relaxed relative shadow-2xl overflow-hidden" style={{ width: '794px', minHeight: '1123px', margin: '0 auto', borderTop: '8px solid #0f172a' }}>
          
          {/* Header */}
          <div className="flex justify-between items-start p-10 pb-6 mb-6">
            <div className="space-y-2 flex-1">
              <div className="flex items-center gap-4">
                <img src={logoImg} alt="Company Logo" className="h-12 object-contain" />
                <div>
                  <span className="text-2xl font-black tracking-tight text-slate-900">HATBALIYA <span className="text-blue-700 font-bold">TECHNOLOGIES</span></span>
                  <p className="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">{slip.tagline}</p>
                </div>
              </div>
              
              <div className="text-[10px] text-slate-500 space-y-1 pt-4 font-medium border-l-2 border-slate-200 pl-4">
                <p>{slip.address}</p>
                <p>📞 {slip.phone} &nbsp;·&nbsp; ✉️ {slip.email}</p>
                <p>🌐 {slip.website} &nbsp;·&nbsp; <span className="font-bold text-slate-700">GSTIN: {slip.gstin}</span></p>
              </div>
            </div>

            <div className="text-right space-y-2 flex-1 flex flex-col items-end">
              <div className="bg-slate-900 text-white px-6 py-2.5 rounded-l-full font-black text-lg tracking-[0.2em] shadow-lg mb-4 -mr-10">
                SALARY SLIP
              </div>
              <div>
                <p className="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">For the Month of</p>
                {isEditing ? (
                  <input type="text" name="monthYear" value={slip.monthYear} onChange={handleFieldChange} className="border-b-2 border-slate-900 bg-slate-50 px-2 py-1 outline-none text-right w-40 text-sm font-bold text-slate-900" placeholder="e.g. May 2024" />
                ) : (
                  <p className="text-lg font-black text-slate-900 border-b-2 border-slate-900 inline-block pb-1">{slip.monthYear || 'N/A'}</p>
                )}
              </div>
            </div>
          </div>

          <div className="px-10">
            {/* Metadata Tables */}
            <div className="grid grid-cols-2 gap-6 mb-8">
              
              {/* Employee info */}
              <div className="bg-slate-50 p-5 rounded-lg border border-slate-200 grid grid-cols-[110px_1fr] gap-y-3 shadow-sm">
                <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">Employee Name</span>
                <span className="font-bold text-slate-900 text-sm">{isEditing ? <input name="employeeName" value={slip.employeeName} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="Full Name" /> : (slip.employeeName || '-')}</span>
                
                <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">Father's Name</span>
                <span className="font-medium text-slate-700">{isEditing ? <input name="fatherName" value={slip.fatherName} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="Father's Name" /> : (slip.fatherName || '-')}</span>
                
                <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">Employee Code</span>
                <span className="font-mono text-slate-700 font-semibold">{isEditing ? <input name="employeeCode" value={slip.employeeCode} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="EMP-000" /> : (slip.employeeCode || '-')}</span>
                
                <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">Location</span>
                <span className="text-slate-700 font-medium">{isEditing ? <input name="location" value={slip.location} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="City/Branch" /> : (slip.location || '-')}</span>
              </div>

              {/* General Work metadata */}
              <div className="bg-slate-50 p-5 rounded-lg border border-slate-200 grid grid-cols-[100px_1fr] gap-y-2.5 shadow-sm">
                <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">Employee ID</span>
                <span className="font-mono text-slate-700 font-semibold">{isEditing ? <input name="employeeId" value={slip.employeeId} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="HT/EMP/000" /> : (slip.employeeId || '-')}</span>
                
                <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">Date of Joining</span>
                <span className="text-slate-700 font-medium">{isEditing ? <input name="joiningDate" value={slip.joiningDate} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="DD-MMM-YYYY" /> : (slip.joiningDate || '-')}</span>
                
                <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">Department</span>
                <span className="text-slate-700 font-medium">{isEditing ? <input name="department" value={slip.department} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="Department" /> : (slip.department || '-')}</span>
                
                <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">Designation</span>
                <span className="text-slate-700 font-medium">{isEditing ? <input name="designation" value={slip.designation} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="Designation" /> : (slip.designation || '-')}</span>
                
                <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">Working Days</span>
                <div className="flex gap-4">
                  <span className="text-slate-700 font-medium flex-1">{isEditing ? <input type="number" name="workingDays" value={slip.workingDays} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" /> : (slip.workingDays || '0')}</span>
                  <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">Pay Date:</span>
                  <span className="text-slate-700 font-medium flex-1">{isEditing ? <input name="paymentDate" value={slip.paymentDate} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="DD-MMM-YYYY" /> : (slip.paymentDate || '-')}</span>
                </div>
              </div>
            </div>

            {/* Bank metadata table block */}
            <div className="bg-white p-4 rounded-lg border border-slate-200 grid grid-cols-4 gap-y-3 mb-8 shadow-sm">
              <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">Bank Name</span>
              <span className="font-bold text-slate-800">{isEditing ? <input name="bankName" value={slip.bankName} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="Bank Name" /> : (slip.bankName || '-')}</span>

              <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center pl-4 border-l border-slate-100">Bank A/C No.</span>
              <span className="font-mono font-semibold text-slate-800">{isEditing ? <input name="bankAcc" value={slip.bankAcc} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="Account Number" /> : (slip.bankAcc || '-')}</span>

              <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">IFSC Code</span>
              <span className="font-mono font-semibold text-slate-800">{isEditing ? <input name="ifscCode" value={slip.ifscCode} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="IFSC Code" /> : (slip.ifscCode || '-')}</span>

              <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center pl-4 border-l border-slate-100">PAN No.</span>
              <span className="font-mono font-semibold text-slate-800">{isEditing ? <input name="panNo" value={slip.panNo} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-full" placeholder="PAN Number" /> : (slip.panNo || '-')}</span>

              <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider self-center">UAN No.</span>
              <span className="font-mono font-semibold text-slate-800" colSpan={3}>{isEditing ? <input name="uanNo" value={slip.uanNo} onChange={handleFieldChange} className="border-b border-slate-300 bg-transparent px-1 py-0.5 outline-none w-64" placeholder="UAN Number" /> : (slip.uanNo || '-')}</span>
            </div>

            {/* Earning & Deduction Side-by-side Table */}
            <div className="grid grid-cols-2 rounded-lg overflow-hidden mb-6 shadow-md border border-slate-200 bg-white">
              
              {/* Earnings Column */}
              <div className="border-r border-slate-200 flex flex-col">
                <div className="bg-slate-900 text-white p-3 px-5 flex justify-between uppercase tracking-widest text-[10px]">
                  <span className="font-black">Earnings Description</span>
                  <span className="font-black">Amount (₹)</span>
                </div>
                
                <div className="divide-y divide-slate-100 flex-1">
                  {[
                    { label: 'Basic Salary', name: 'basicSalary' },
                    { label: 'House Rent Allowance (HRA)', name: 'hra' },
                    { label: 'Conveyance Allowance', name: 'conveyance' },
                    { label: 'Special Allowance', name: 'specialAllowance' },
                    { label: 'Medical Allowance', name: 'medicalAllowance' },
                    { label: 'Leave Travel Allowance (LTA)', name: 'lta' },
                    { label: 'Other Allowance', name: 'otherAllowance' },
                  ].map((item, idx) => (
                    <div key={idx} className="flex justify-between items-center p-3 px-5 hover:bg-slate-50 transition-colors">
                      <span className="text-slate-700 font-medium">{item.label}</span>
                      <span className="font-bold text-slate-900 font-mono">
                        {isEditing ? (
                          <input type="number" name={item.name} value={slip[item.name]} onChange={handleFieldChange} className="border border-slate-300 bg-white px-2 py-1 rounded w-24 text-right shadow-inner outline-none focus:border-blue-500" />
                        ) : (
                          Number(slip[item.name]).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits:2})
                        )}
                      </span>
                    </div>
                  ))}
                </div>
                <div className="bg-slate-50 p-4 px-5 flex justify-between uppercase border-t border-slate-200">
                  <span className="font-bold text-slate-500 text-[10px] tracking-wider">Total Earnings</span>
                  <span className="font-black text-slate-900 text-sm">₹ {totalEarnings.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits:2})}</span>
                </div>
              </div>

              {/* Deductions Column */}
              <div className="flex flex-col">
                <div className="bg-slate-900 text-white p-3 px-5 flex justify-between uppercase tracking-widest text-[10px]">
                  <span className="font-black">Deductions Description</span>
                  <span className="font-black">Amount (₹)</span>
                </div>

                <div className="divide-y divide-slate-100 flex-1">
                  {[
                    { label: 'Provident Fund (PF)', name: 'pf' },
                    { label: 'Employee ESI', name: 'esi' },
                    { label: 'Professional Tax', name: 'profTax' },
                    { label: 'Income Tax (TDS)', name: 'tds' },
                    { label: 'Leave Deduction', name: 'leaveDeduction' },
                    { label: 'Other Deduction', name: 'otherDeduction' },
                  ].map((item, idx) => (
                    <div key={idx} className="flex justify-between items-center p-3 px-5 hover:bg-slate-50 transition-colors">
                      <span className="text-slate-700 font-medium">{item.label}</span>
                      <span className="font-bold text-slate-900 font-mono">
                        {isEditing ? (
                          <input type="number" name={item.name} value={slip[item.name]} onChange={handleFieldChange} className="border border-slate-300 bg-white px-2 py-1 rounded w-24 text-right shadow-inner outline-none focus:border-blue-500" />
                        ) : (
                          Number(slip[item.name]).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits:2})
                        )}
                      </span>
                    </div>
                  ))}
                </div>
                <div className="bg-slate-50 p-4 px-5 flex justify-between uppercase border-t border-slate-200">
                  <span className="font-bold text-slate-500 text-[10px] tracking-wider">Total Deductions</span>
                  <span className="font-black text-slate-900 text-sm">₹ {totalDeductions.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits:2})}</span>
                </div>
              </div>
            </div>

            {/* Net pay segment */}
            <div className="bg-slate-900 rounded-xl overflow-hidden grid grid-cols-[1fr_2fr] mb-12 shadow-xl border border-slate-800 text-white">
              
              {/* Highlighted Net Pay Card */}
              <div className="bg-emerald-600 p-6 flex flex-col justify-center relative overflow-hidden">
                <div className="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                <span className="text-emerald-100 font-bold uppercase tracking-widest text-[10px] z-10">Net Pay Payable</span>
                <span className="text-2xl font-black text-white mt-1 z-10 tracking-tight">₹ {netPay.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits:2})}</span>
              </div>

              {/* Calculations summaries */}
              <div className="p-6 flex flex-col justify-center relative">
                <p className="font-bold text-slate-400 uppercase tracking-widest text-[9px] mb-1">Amount in Words</p>
                <p className="text-sm font-semibold text-white tracking-wide">Rupees {numberToWords(netPay)}</p>
                <div className="absolute top-0 right-0 h-full w-32 bg-gradient-to-l from-slate-800/50 to-transparent pointer-events-none"></div>
              </div>
            </div>

            {/* Footer Signature segment */}
            <div className="flex justify-between items-end pb-8">
              <div className="text-[9px] text-slate-400 space-y-1">
                <p className="font-bold text-slate-600 uppercase tracking-wider">Note:</p>
                <p>• All amounts are in Indian Rupees (INR).</p>
                <p>• This is a system generated salary slip and does not require a physical signature.</p>
                <p>• For any discrepancies, please contact the HR / Payroll department within 7 days.</p>
              </div>

              <div className="text-center space-y-2">
                <div className="border-b border-slate-300 pb-2 w-40 mx-auto">
                  {/* Signature graphic/space */}
                  <div className="font-serif text-lg italic text-slate-800 opacity-80 h-8 flex items-end justify-center">System Generated</div>
                </div>
                <p className="font-bold text-[9px] uppercase tracking-wider text-slate-500">Authorized Signatory</p>
              </div>
            </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

