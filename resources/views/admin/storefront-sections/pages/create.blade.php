@extends('layouts.admin')

@section('title', 'Create Page')

@section('header-title')
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.pages.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Create Page</h1>
            <p class="text-xs text-gray-400 font-medium mt-1">Add new content to your public storefront</p>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .form-label { display: block; font-size: 12px; font-weight: 700; color: #4b5563; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
    
    /* Standard inputs */
    .form-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 10px 14px; font-size: 14px; color: #1f2937; background: #fff; transition: all 150ms; outline: none; }
    .form-input:focus { border-color: var(--brand-500); box-shadow: 0 0 0 4px color-mix(in srgb, var(--brand-500) 10%, transparent); }
    
    /* Input Groups (Prefixes) */
    .input-group { display: flex; align-items: stretch; border: 1.5px solid #e5e7eb; border-radius: 10px; overflow: hidden; transition: all 150ms; background: #fff; }
    .input-group:focus-within { border-color: var(--brand-500); box-shadow: 0 0 0 4px color-mix(in srgb, var(--brand-500) 10%, transparent); }
    .input-group-prefix { display: flex; align-items: center; padding: 0 12px; background: #f9fafb; border-right: 1.5px solid #e5e7eb; color: #9ca3af; font-size: 13px; font-weight: 500; user-select: none; }
    .input-group-field { flex: 1; width: 100%; padding: 10px 12px; font-size: 13px; color: #1f2937; background: transparent; outline: none; border: none; }
    
    .card-box { background: #fff; border: 1px solid #f1f5f9; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
</style>
@endpush

@section('content')
<div class="pb-10 w-full max-w-[1600px] mx-auto">
        {{-- Header & Actions --}}
        <div class="mb-6 flex flex-col sm:flex-row flex-wrap sm:items-center justify-between gap-4">
            <div>
                {{-- <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Create New Page</h3> --}}
                <p class="text-sm text-gray-500 mt-1">Add a new legal, informational, or custom page to your storefront.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.pages.index') }}"
                    class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Pages
                </a>
            </div>
        </div>
    <form method="POST" action="{{ route('admin.pages.store') }}" class="w-full">    
        @csrf


        <div class="flex flex-col lg:flex-row gap-6 w-full">
            
            {{-- 🟢 LEFT COLUMN: MAIN CONTENT (70%) --}}
            {{-- min-w-0 prevents flex items from overflowing their containers on mobile/iPad --}}
            <div class="flex-1 min-w-0 space-y-6">
                
                <div class="card-box">
                    <div class="mb-5">
                        <label class="form-label">Page Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="form-input text-lg font-bold" placeholder="e.g., Privacy Policy">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label flex justify-between">
                            <span>Page Content</span>
                            <span class="text-gray-400 font-normal normal-case tracking-normal">Supports HTML</span>
                        </label>
                        
                        {{-- Standard textarea ready for a Rich Text Editor --}}
                        <textarea name="content" rows="18" 
                                  class="form-input font-mono text-[13px] leading-relaxed" 
                                  placeholder="<h2>Write your content here...</h2>">{{ old('content') }}</textarea>
                        
                        @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

            </div>

            {{-- 🟢 RIGHT COLUMN: SETTINGS & SEO (30%) --}}
            {{-- Fixed width on desktop, full width on mobile/iPad --}}
            <div class="w-full lg:w-[320px] xl:w-[360px] flex-shrink-0 space-y-6">
                
                {{-- Publish Card --}}
                <div class="card-box bg-gray-50/50">
                    <div class="flex items-center justify-between mb-6">
                        <label class="form-label mb-0 text-gray-700">Visibility</label>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="is_published" value="0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_published" value="1" class="sr-only peer" {{ old('is_published') ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-[13px] font-bold text-white transition-all hover:opacity-95" style="background: var(--brand-600);">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Create Page
                    </button>
                </div>

                {{-- Attributes Card --}}
                <div class="card-box">
                    <h3 class="text-[12px] font-black text-gray-800 uppercase tracking-wider mb-5 pb-3 border-b border-gray-100">Page Attributes</h3>
                    
                    <div class="mb-5">
                        <label class="form-label">Page Type <span class="text-red-500">*</span></label>
                        <select name="type" class="form-input bg-gray-50 cursor-pointer" required>
                            <option value="">Select a type...</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1.5 leading-snug">This helps categorize links in your public storefront footer.</p>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">URL Slug</label>
                        
                        {{-- 🟢 BUG FIX: Flexbox Input Group (No Overlaps!) --}}
                        <div class="input-group">
                            <span class="input-group-prefix">/page/</span>
                            <input type="text" name="slug" value="{{ old('slug') }}" 
                                   class="input-group-field font-mono" placeholder="auto-generated">
                        </div>
                        
                        <p class="text-[11px] text-gray-400 mt-1.5 leading-snug">Leave blank to auto-generate from the title.</p>
                        @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- SEO Card --}}
                <div class="card-box">
                    <h3 class="text-[12px] font-black text-gray-800 uppercase tracking-wider mb-5 pb-3 border-b border-gray-100">Search Engine (SEO)</h3>
                    
                    <div class="mb-5">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title') }}" 
                               class="form-input" placeholder="Title for Google search">
                        @error('seo_title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Meta Description</label>
                        <textarea name="seo_description" rows="4" class="form-input text-[13px]" 
                                  placeholder="A brief summary of this page...">{{ old('seo_description') }}</textarea>
                        @error('seo_description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection