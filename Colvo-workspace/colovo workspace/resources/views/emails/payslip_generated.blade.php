<x-mail::message>
# Hello {{ $user->name }},

Your payslip for the month of **{{ $payroll->month }}** has been successfully generated.

### Salary Details:
- **Base Salary:** ₹{{ number_format($payroll->salary, 2) }}
- **Net Salary:** ₹{{ number_format($payroll->net_salary, 2) }}

Your net salary was calculated based on your present days during the working days of the month (excluding Sundays).

<x-mail::button :url="route('login')">
Login to View Payslip
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} HR
</x-mail::message>
