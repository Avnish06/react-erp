@props(['employees', 'selected' => null, 'name' => 'employee_id', 'id' => 'employeeSelectId', 'onchange' => null, 'placeholder' => 'All Employees', 'padding' => '8px 14px', 'width' => '100%', 'required' => false])

<div class="custom-select-wrapper" id="{{ $id }}_wrapper" style="width: {{ $width }};">
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $selected }}" {{ $required ? 'required' : '' }}>
    <div class="custom-select" style="padding: {{ $padding }};" onclick="document.getElementById('{{ $id }}_options').classList.toggle('show')">
        <div style="display: flex; align-items: center; font-weight: 500;" id="{{ $id }}_label">
            @php
                $selectedEmp = $employees->firstWhere('id', $selected);
            @endphp
            @if($selectedEmp)
                <div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px; background: var(--secondary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 10px;">
                    {{ substr($selectedEmp->name, 0, 2) }}
                </div>
                <span>{{ $selectedEmp->name }}</span>
            @else
                <div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;">
                    <i class='bx bx-user' style="font-size: 12px;"></i>
                </div>
                <span>{{ $placeholder }}</span>
            @endif
        </div>
        <i class='bx bx-chevron-down' style="font-size: 16px; color: var(--text-muted); margin-left: 8px;"></i>
    </div>
    <div class="custom-select-options" id="{{ $id }}_options">
        <div class="custom-option {{ !$selected ? 'selected' : '' }}" onclick="selectEmployeeComponent_{{ $id }}('', '{{ addslashes($placeholder) }}', null)">
            <div class="custom-select-img" style="width:20px; height:20px; margin-right: 8px; border-radius:4px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;">
                <i class='bx bx-user' style="font-size: 14px;"></i>
            </div>
            <span>{{ $placeholder }}</span>
        </div>
        @foreach($employees as $e)
            @php
                $initial = substr($e->name, 0, 2);
            @endphp
            <div class="custom-option {{ $selected == $e->id ? 'selected' : '' }}" 
                 onclick="selectEmployeeComponent_{{ $id }}('{{ $e->id }}', '{{ addslashes($e->name) }}', '{{ $initial }}')"
                 data-company="{{ $e->company_id ?? '' }}"
                 data-dept="{{ $e->department ?? '' }}"
                 data-pos="{{ $e->position ?? '' }}">
                <div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px; background: var(--secondary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px;">
                    {{ $initial }}
                </div>
                <span>{{ $e->name }}</span>
            </div>
        @endforeach
    </div>
</div>

<script>
if (typeof window['selectEmployeeComponent_{{ $id }}'] === 'undefined') {
    window['selectEmployeeComponent_{{ $id }}'] = function(id, name, initial) {
        let inputEl = document.getElementById('{{ $id }}');
        inputEl.value = id;
        
        let html = '';
        if(id === '') {
            html = `<div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;"><i class='bx bx-user' style="font-size: 12px;"></i></div><span>${name}</span>`;
        } else {
            html = `<div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px; background: var(--secondary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 10px;">${initial}</div><span>${name}</span>`;
        }
        document.getElementById('{{ $id }}_label').innerHTML = html;
        document.getElementById('{{ $id }}_options').classList.remove('show');
        
        // Trigger standard change event on the hidden input so other scripts can listen to it
        inputEl.dispatchEvent(new Event('change', { bubbles: true }));

        @if($onchange)
            let onChangeFunc = {!! json_encode($onchange) !!};
            if (onChangeFunc.includes('this.form.submit()')) {
                if (inputEl.form) inputEl.form.submit();
            } else if (onChangeFunc.includes('window.location.href')) {
                window.location.href = '?employee_id=' + id;
            } else {
                eval(onChangeFunc.replace('this.value', "'" + id + "'"));
            }
        @endif
    }

    document.addEventListener('click', function(e) {
        let wrap = document.getElementById('{{ $id }}_wrapper');
        if (wrap && !wrap.contains(e.target)) {
            let opts = document.getElementById('{{ $id }}_options');
            if (opts) opts.classList.remove('show');
        }
    });
}
</script>
