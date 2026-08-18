@props(['companies', 'selected' => null, 'name' => 'company_id', 'id' => 'companySelectId', 'onchange' => null, 'placeholder' => 'All Companies', 'padding' => '8px 14px', 'width' => '100%'])

<div class="custom-select-wrapper" id="{{ $id }}_wrapper" style="width: {{ $width }};">
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $selected }}">
    <div class="custom-select" style="padding: {{ $padding }};" onclick="document.getElementById('{{ $id }}_options').classList.toggle('show')">
        <div style="display: flex; align-items: center; font-weight: 500;" id="{{ $id }}_label">
            @php
                $selectedComp = $companies->firstWhere('id', $selected);
            @endphp
            @if($selectedComp)
                @if($selectedComp->logo)
                    <img src="{{ asset('storage/'.$selectedComp->logo) }}" class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px;" alt="logo">
                @else
                    <div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 10px;">
                        {{ substr($selectedComp->name, 0, 1) }}
                    </div>
                @endif
                <span>{{ $selectedComp->name }}</span>
            @else
                <div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;">
                    <i class='bx bx-buildings' style="font-size: 12px;"></i>
                </div>
                <span>{{ $placeholder }}</span>
            @endif
        </div>
        <i class='bx bx-chevron-down' style="font-size: 16px; color: var(--text-muted); margin-left: 8px;"></i>
    </div>
    <div class="custom-select-options" id="{{ $id }}_options">
        <div class="custom-option {{ !$selected ? 'selected' : '' }}" onclick="selectCompanyComponent_{{ $id }}('', '{{ addslashes($placeholder) }}', null, null)">
            <div class="custom-select-img" style="width:20px; height:20px; margin-right: 8px; border-radius:4px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;">
                <i class='bx bx-buildings' style="font-size: 14px;"></i>
            </div>
            <span>{{ $placeholder }}</span>
        </div>
        @foreach($companies as $company)
            @php
                $logoUrl = $company->logo ? asset('storage/'.$company->logo) : null;
                $initial = substr($company->name, 0, 1);
            @endphp
            <div class="custom-option {{ $selected == $company->id ? 'selected' : '' }}" 
                 onclick="selectCompanyComponent_{{ $id }}('{{ $company->id }}', '{{ addslashes($company->name) }}', '{{ $logoUrl }}', '{{ $initial }}')">
                @if($company->logo)
                    <img src="{{ $logoUrl }}" class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px;" alt="logo">
                @else
                    <div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px;">
                        {{ $initial }}
                    </div>
                @endif
                <span>{{ $company->name }}</span>
            </div>
        @endforeach
    </div>
</div>

<script>
if (typeof window['selectCompanyComponent_{{ $id }}'] === 'undefined') {
    window['selectCompanyComponent_{{ $id }}'] = function(id, name, logoUrl, initial) {
        let inputEl = document.getElementById('{{ $id }}');
        inputEl.value = id;
        
        let html = '';
        if(id === '') {
            html = `<div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;"><i class='bx bx-buildings' style="font-size: 12px;"></i></div><span>${name}</span>`;
        } else {
            if(logoUrl && logoUrl !== 'null' && logoUrl !== '') {
                html = `<img src="${logoUrl}" class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px;" alt="logo"><span>${name}</span>`;
            } else {
                html = `<div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 10px;">${initial}</div><span>${name}</span>`;
            }
        }
        document.getElementById('{{ $id }}_label').innerHTML = html;
        document.getElementById('{{ $id }}_options').classList.remove('show');
        
        @if($onchange)
            let onChangeFunc = {!! json_encode($onchange) !!};
            if (onChangeFunc.includes('this.form.submit()')) {
                if (inputEl.form) inputEl.form.submit();
            } else if (onChangeFunc.includes('window.location.href')) {
                window.location.href = '?company_id=' + id;
            } else if (onChangeFunc.includes('filterEmployeesByCompany')) {
                filterEmployeesByCompany(id);
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
