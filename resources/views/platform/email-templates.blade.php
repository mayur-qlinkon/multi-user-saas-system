@extends('layouts.platform')

@section('title', 'Email Templates - Qlinkon Super Admin')
@section('header', 'Email Templates')

@section('styles')
<style>
    [x-cloak] { display: none !important; }

    .form-label { display: block; font-size: 12px; font-weight: 700; color: #4b5563; margin-bottom: 6px; }
    .form-input {
        width: 100%; border: 1px solid #d1d5db; border-radius: 8px;
        padding: 8px 12px; font-size: 13px; transition: all 0.2s; outline: none;
        font-family: Poppins, sans-serif;
    }
    .form-input:focus { border-color: var(--brand-500); box-shadow: 0 0 0 3px rgba(15,118,110,0.12); }    

    /* Variable chip */
    .var-chip {
        display: inline-flex; align-items: center; gap: 4px;
        background: #f0fdf4; color: #166534;
        border: 1px solid #bbf7d0;
        border-radius: 6px; padding: 3px 8px;
        font-size: 11px; font-weight: 700; cursor: pointer;
        transition: background 120ms;
        user-select: none;
    }
    .var-chip:hover { background: #dcfce7; }

    /* Modal backdrop */
    .modal-backdrop {
        position: fixed; inset: 0; background: rgba(0,0,0,0.45);
        z-index: 9999; display: flex; align-items: flex-start;
        justify-content: center; padding: 24px 16px; overflow-y: auto;
    }
    .modal-box {
        background: #fff; border-radius: 16px; width: 100%;
        max-width: 760px; box-shadow: 0 24px 80px rgba(0,0,0,0.18);
        overflow: hidden; margin: auto;
    }
</style>
@endsection

@section('content')
<div x-data="templateManager()" x-init="init()" class="pb-10">

    {{-- ── HEADER ── --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Email Templates</h1>
            <p class="text-sm text-gray-500 mt-1">Configure transactional email content. Use <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">&#123;&#123;variable&#125;&#125;</code> placeholders in subject and body.</p>
        </div>
    </div>

    {{-- ── ALERTS ── --}}
    @if (session('success'))
        <div class="bg-green-50 text-green-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-green-100 mb-6 flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i> {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-600 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6">
            <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix the following errors:</div>
            <ul class="list-disc list-inside pl-7 text-xs font-medium space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── TEMPLATE CARDS ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        @foreach ($defined as $key => $meta)
        @php
            $tpl = $templates->get($key);
        @endphp

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">

            {{-- Card header --}}
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-black text-gray-800 truncate">{{ $meta['label'] }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $meta['description'] }}</p>
                </div>
                @if ($tpl)
                    <span class="shrink-0 inline-flex items-center gap-1 bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                        <i data-lucide="check" class="w-3 h-3"></i> Configured
                    </span>
                @else
                    <span class="shrink-0 inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i> Not Set
                    </span>
                @endif
            </div>

            {{-- Card body --}}
            <div class="px-5 py-4">

                {{-- Subject preview --}}
                @if ($tpl)
                <div class="mb-3">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Subject Line</p>
                    <p class="text-[13px] text-gray-700 font-medium">{{ $tpl->subject }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Last Updated</p>
                    <p class="text-[12px] text-gray-500">{{ $tpl->updated_at->format('d M Y, h:i A') }}</p>
                </div>
                @else
                <p class="text-[13px] text-gray-400 mb-4 italic">No template configured yet. Click below to set it up.</p>
                @endif

                {{-- Variables --}}
                <div class="mb-4">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Available Variables</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($meta['variables'] as $var)
                            <span class="text-[10px] font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded border border-gray-200">&#123;&#123;{{ $var }}&#125;&#125;</span>
                        @endforeach
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                    <button
                        type="button"
                        @click="openModal('{{ $key }}', @js($tpl), @js($meta['variables']), '{{ addslashes($meta['label']) }}')"
                        class="flex-1 text-center py-2 rounded-lg text-[13px] font-bold transition-colors
                            {{ $tpl ? 'bg-brand-600 hover:bg-brand-700 text-white' : 'bg-gray-800 hover:bg-gray-900 text-white' }}">
                        {{ $tpl ? '✏️ Edit Template' : '⚙️ Configure Template' }}
                    </button>

                    @if ($tpl)
                    <form method="POST" action="{{ route('platform.email-templates.destroy', $tpl->id) }}"
                        onsubmit="return confirm('Delete this template? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-3 py-2 rounded-lg text-[13px] font-bold text-red-600 border border-red-200 hover:bg-red-50 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                    @endif
                </div>

            </div>
        </div>
        @endforeach

    </div>

    {{-- ══════════════════════════════════════════════
         EDITOR MODAL
    ══════════════════════════════════════════════ --}}
    <div x-show="showModal" x-cloak class="modal-backdrop" @click.self="closeModal()">
        <div class="modal-box" @click.stop>

            {{-- Modal Header --}}
            <div class="flex items-start justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50 gap-4">
                <div>
                    <p class="text-[15px] font-black text-gray-900" x-text="currentLabel"></p>
                    <p class="text-[12px] text-gray-400 mt-0.5">Edit subject and body. Changes apply globally to all tenants without their own override.</p>
                </div>
                <button @click="closeModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex-shrink-0">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-5">

                {{-- Subject --}}
                <div>
                    <label class="form-label">Subject Line <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.subject" placeholder="e.g. Your inquiry about &#123;&#123;product_name&#125;&#125; has been received"
                        class="form-input" maxlength="255" required>
                    <p class="text-[11px] text-gray-400 mt-1">You can use <code class="bg-gray-100 px-1 rounded">&#123;&#123;variable&#125;&#125;</code> placeholders in the subject too.</p>
                </div>

                {{-- Variables reference --}}
                <div>
                    <label class="form-label">Click to insert variable into body</label>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="v in currentVars" :key="v">
                            <button type="button" class="var-chip" @click="insertVariable(v)" x-text="wrapVar(v)"></button>
                        </template>
                    </div>
                </div>

                {{-- Body editor --}}
                <div>
                    <label class="form-label">Email HTML Body <span class="text-red-500">*</span></label>
                    <textarea id="html-editor" x-model="form.body" rows="14" required
                        placeholder="Paste your raw HTML here..."
                        class="form-input font-mono text-[12px] leading-relaxed resize-y bg-gray-50/50"></textarea>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-3">
                <p class="text-[11px] text-gray-400">HTML is supported. Keep styles inline for best email compatibility.</p>
                <div class="flex items-center gap-2">
                    <button type="button" @click="closeModal()"
                        class="px-5 py-2 rounded-lg text-[13px] font-bold text-gray-600 border border-gray-200 hover:bg-gray-100 transition-colors">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()"
                        class="px-5 py-2 rounded-lg text-[13px] font-bold text-white bg-brand-600 hover:bg-brand-700 transition-colors flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Save Template
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Hidden forms for POST/PUT --}}
    <form id="store-form" method="POST" action="{{ route('platform.email-templates.store') }}" style="display:none">
        @csrf
        <input type="hidden" name="key" id="sf-key">
        <input type="hidden" name="subject" id="sf-subject">
        <input type="hidden" name="body" id="sf-body">
    </form>

    <form id="update-form" method="POST" style="display:none">
        @csrf
        @method('PUT')
        <input type="hidden" name="subject" id="uf-subject">
        <input type="hidden" name="body" id="uf-body">
    </form>

</div>
@endsection

@section('scripts')
<script>
function templateManager() {
    return {
        showModal: false,
        currentKey: '',
        currentId: null,
        currentLabel: '',
        currentVars: [],
        form: { subject: '', body: '' }, // 🌟 ADDED body here

        init() {
            if (window.lucide) lucide.createIcons();
            // 🌟 REMOVED Quill init from here completely
        },

        wrapVar(v) {
            return '\u007B\u007B' + v + '\u007D\u007D';
        },

        openModal(key, template, vars, label) {
            this.currentKey   = key;
            this.currentId    = template ? template.id : null;
            this.currentLabel = label || key;
            this.currentVars  = vars;
            this.form.subject = template ? template.subject : '';
            this.form.body    = template ? template.body : ''; // 🌟 Load directly into state
            this.showModal    = true;
        },

        insertVariable(v) {
            // 🌟 NEW: Inserts the variable exactly where the cursor is in the textarea!
            const el = document.getElementById('html-editor');
            if (!el) return;
            
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const text = this.form.body;
            const varText = this.wrapVar(v);
            
            this.form.body = text.substring(0, start) + varText + text.substring(end);
            
            this.$nextTick(() => {
                el.focus();
                el.setSelectionRange(start + varText.length, start + varText.length);
            });
        },

        submitForm() {
            if (!this.form.subject.trim()) {
                alert('Please enter a subject line.');
                return;
            }
            if (!this.form.body.trim()) {
                alert('Please paste the HTML email body.');
                return;
            }

            if (this.currentId) {
                const form = document.getElementById('update-form');
                form.action = '/platform/email-templates/' + this.currentId;
                document.getElementById('uf-subject').value = this.form.subject;
                document.getElementById('uf-body').value    = this.form.body; // 🌟 Read from state
                form.submit();
            } else {
                document.getElementById('sf-key').value     = this.currentKey;
                document.getElementById('sf-subject').value = this.form.subject;
                document.getElementById('sf-body').value    = this.form.body; // 🌟 Read from state
                document.getElementById('store-form').submit();
            }
        },

        closeModal() {
            this.showModal = false;
            this.form.body = '';
        },
    };
}
</script>
@endsection
