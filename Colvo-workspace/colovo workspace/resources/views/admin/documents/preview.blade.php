@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Generated Documents</h1>
    </div>
    <div class="topbar-right" style="gap: 10px;">
        <a href="{{ route('admin.documents.index') }}" class="btn btn-outline">
            <i class='bx bx-arrow-back'></i> Generate New
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class='bx bx-printer'></i> Print / Save as PDF
        </button>
    </div>
</div>

<!-- Print-only info -->
<div class="no-print" style="background: linear-gradient(135deg, #fff7f0, #fff3ea); border: 1px solid #FFD4B0; border-radius: 14px; padding: 16px 22px; margin-bottom: 25px; display:flex; align-items:center; gap: 12px;">
    <i class='bx bx-info-circle' style="font-size:22px; color:var(--primary);"></i>
    <div>
        <strong style="color:var(--text-main);">{{ count($documents) }} document(s) generated for {{ $data['employee_name'] }}</strong><br>
        <span style="font-size:13px; color:var(--text-muted);">
            Review all <span style="color:var(--warning); font-weight:600;">@{{placeholder}}</span> fields and fill them in before issuing.
            Use <strong>Print / Save as PDF</strong> to export — each document will print on its own page.
        </span>
    </div>
</div>

<!-- Document Tabs (screen only) -->
<div class="no-print" style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
    @foreach($documents as $i => $doc)
        <button onclick="showDoc({{ $i }})" id="tab-{{ $i }}"
            class="doc-tab {{ $i === 0 ? 'active' : '' }}"
            style="padding: 8px 16px; border-radius: 30px; border: 2px solid {{ $i === 0 ? 'var(--primary)' : 'var(--border-color)' }}; background: {{ $i === 0 ? 'var(--primary-glow)' : '#fff' }}; color: {{ $i === 0 ? 'var(--primary)' : 'var(--text-main)' }}; font-size:13px; font-weight:600; cursor:pointer; transition: all 0.2s;">
            <i class='bx bx-file-blank'></i> {{ $doc['title'] }}
        </button>
    @endforeach
</div>

<!-- Documents -->
@foreach($documents as $i => $doc)
<div id="doc-panel-{{ $i }}" class="doc-panel {{ $i === 0 ? '' : 'hidden-doc' }}" style="{{ $i === 0 ? '' : 'display:none;' }}">

    <!-- Screen view -->
    <div class="content-panel no-print" style="margin-bottom: 25px;">
        <div class="panel-header" style="justify-content: space-between;">
            <h3 class="panel-title">
                <i class='bx bx-file-blank' style="color:var(--primary);"></i>
                {{ $doc['title'] }}
                <span style="font-size:11px; font-weight:400; color:var(--text-muted); margin-left:8px;">{{ $doc['version'] }} &bull; {{ $doc['generated_date'] }}</span>
            </h3>
        </div>

        <!-- Editable Fields Notice -->
        @if(!empty($doc['editable_fields']))
        <div style="background: #fffbf0; border: 1px solid #F59E0B; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; display:flex; gap:10px;">
            <i class='bx bx-edit' style="font-size:18px; color:#F59E0B; flex-shrink:0; margin-top:2px;"></i>
            <div>
                <strong style="color:#92400e; font-size:13px;">Editable Fields (Admin Review Required)</strong><br>
                <span style="font-size:12px; color:#92400e;">{{ implode(', ', array_map(fn($f) => '{' . '{' . $f . '}' . '}', $doc['editable_fields'])) }}</span>
            </div>
        </div>
        @endif

        <!-- Rendered Document Content -->
        <div class="doc-content" style="font-family: 'Georgia', serif; line-height: 1.8; color: #1a1a1a; padding: 20px 0;">
            {!! nl2br_markdown($doc['content']) !!}
        </div>
    </div>

    <!-- Print View (always rendered, shown via print CSS) -->
    <div class="print-only doc-print-page">
        {!! nl2br_markdown($doc['content']) !!}
    </div>
</div>
@endforeach

<style>
    /* Print styles */
    @media print {
        .no-print { display: none !important; }
        .app-sidebar, .app-main > .app-header, .sidebar-toggle, .floating-helpdesk, #chatbot-container, #chatbot-toggle { display: none !important; }
        .app-main { margin-left: 0 !important; padding: 0 !important; }
        .app-container { display: block !important; }
        .print-only { display: block !important; }
        .doc-print-page {
            page-break-after: always;
            font-family: 'Georgia', serif;
            font-size: 12pt;
            line-height: 1.8;
            color: #000;
            padding: 40px 50px;
            max-width: 800px;
            margin: 0 auto;
        }
        .doc-print-page:last-child { page-break-after: auto; }
        /* Show ALL docs when printing */
        .hidden-doc { display: block !important; }
        .doc-panel { display: block !important; }
    }

    @media screen {
        .print-only { display: none !important; }
    }

    /* Markdown-like styling for doc content */
    .doc-content h1 { font-size: 22px; font-weight: 700; border-bottom: 2px solid var(--primary); padding-bottom: 8px; margin-bottom: 20px; color: #111; }
    .doc-content h2 { font-size: 16px; font-weight: 700; margin: 24px 0 10px; color: #111; border-left: 3px solid var(--primary); padding-left: 10px; }
    .doc-content h3 { font-size: 14px; font-weight: 700; margin: 18px 0 8px; color: var(--primary); }
    .doc-content p  { margin: 8px 0; font-size: 14px; }
    .doc-content strong { font-weight: 700; }
    .doc-content em { font-style: italic; color: var(--text-muted); }
    .doc-content table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    .doc-content table th { background: var(--primary-glow); color: var(--primary); font-weight: 700; padding: 10px 14px; border: 1px solid #ddd; font-size:13px; text-align: left; }
    .doc-content table td { padding: 9px 14px; border: 1px solid #ddd; font-size: 13px; vertical-align: top; }
    .doc-content table tr:nth-child(even) { background: #fafafa; }
    .doc-content hr { border: none; border-top: 1px solid #e2e8f0; margin: 20px 0; }
    .doc-content ul, .doc-content ol { margin: 8px 0 8px 20px; }
    .doc-content li { font-size: 14px; margin-bottom: 4px; }
    .doc-content blockquote { border-left: 3px solid var(--warning); padding-left: 12px; color: var(--text-muted); font-style: italic; margin: 12px 0; }

    /* Highlight placeholders */
    .doc-content .placeholder { background: #fef9c3; border: 1px dashed #d97706; border-radius: 4px; padding: 1px 5px; color: #92400e; font-weight: 600; font-size: 13px; }

    .doc-print-page h1 { font-size: 20pt; font-weight: 700; border-bottom: 2pt solid #FF6B00; padding-bottom: 6pt; margin-bottom: 18pt; }
    .doc-print-page h2 { font-size: 14pt; font-weight: 700; margin: 18pt 0 8pt; }
    .doc-print-page h3 { font-size: 12pt; font-weight: 700; margin: 14pt 0 6pt; color: #FF6B00; }
    .doc-print-page table { width: 100%; border-collapse: collapse; margin: 12pt 0; }
    .doc-print-page table th { background: #FFF3EA; font-weight: 700; padding: 6pt 10pt; border: 1pt solid #ccc; text-align: left; }
    .doc-print-page table td { padding: 6pt 10pt; border: 1pt solid #ccc; }
    .doc-print-page hr { border: none; border-top: 1pt solid #ccc; margin: 14pt 0; }
</style>

<script>
    function showDoc(index) {
        // Hide all panels
        document.querySelectorAll('.doc-panel').forEach(p => p.style.display = 'none');
        document.querySelectorAll('[id^="tab-"]').forEach(t => {
            t.style.borderColor = 'var(--border-color)';
            t.style.background  = '#fff';
            t.style.color       = 'var(--text-main)';
        });

        // Show selected
        document.getElementById('doc-panel-' + index).style.display = 'block';
        const tab = document.getElementById('tab-' + index);
        tab.style.borderColor = 'var(--primary)';
        tab.style.background  = 'var(--primary-glow)';
        tab.style.color       = 'var(--primary)';
    }
</script>
@endsection
