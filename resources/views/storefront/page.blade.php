@extends('layouts.storefront')

@section('title', $page->meta_title)
@section('meta_description', $page->seo_description ?? "Read the {$page->title} for {$company->name}.")

@push('styles')
<style>
    /* ════════ SaaS "Prose" Formatting (Zero Dependencies) ════════ */
    .custom-prose {
        color: #4b5563; /* text-gray-600 */
        line-height: 1.8;
        font-size: 1.05rem;
    }
    .custom-prose p {
        margin-bottom: 1.5em;
    }
    .custom-prose h1, 
    .custom-prose h2, 
    .custom-prose h3 {
        color: #111827; /* text-gray-900 */
        font-weight: 800;
        margin-top: 2.2em;
        margin-bottom: 0.8em;
        line-height: 1.3;
        letter-spacing: -0.015em;
    }
    .custom-prose h1 { font-size: 2.25rem; }
    .custom-prose h2 { font-size: 1.75rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 0.3em; }
    .custom-prose h3 { font-size: 1.25rem; }
    
    .custom-prose a {
        color: var(--brand-600, #008a62);
        text-decoration: none;
        font-weight: 600;
        border-bottom: 2px solid color-mix(in srgb, var(--brand-600) 30%, transparent);
        transition: border-color 150ms ease;
    }
    .custom-prose a:hover {
        border-color: var(--brand-600, #008a62);
    }
    
    .custom-prose ul {
        list-style-type: none;
        padding-left: 0;
        margin-bottom: 1.5em;
    }
    .custom-prose ul li {
        position: relative;
        padding-left: 1.75em;
        margin-bottom: 0.5em;
    }
    /* Custom polished bullet points */
    .custom-prose ul li::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0.6em;
        height: 6px;
        width: 6px;
        border-radius: 50%;
        background-color: #d1d5db; /* text-gray-300 */
    }
    
    .custom-prose strong, .custom-prose b {
        font-weight: 700;
        color: #1f2937; /* text-gray-800 */
    }
    
    .custom-prose blockquote {
        border-left: 4px solid var(--brand-500, #008a62);
        background: #f9fafb;
        padding: 1em 1.5em;
        margin: 1.5em 0;
        border-radius: 0 8px 8px 0;
        font-style: italic;
        color: #374151;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:py-16 lg:px-8">
    
    {{-- Page Header --}}
    <div class="text-center mb-12 pb-8 border-b border-gray-100">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight sm:text-5xl mb-4">
            {{ $page->title }}
        </h1>
        <p class="text-sm text-gray-500 font-medium uppercase tracking-widest">
            Last updated on {{ $page->updated_at->format('F j, Y') }}
        </p>
    </div>

    {{-- 
        Page Content 
        We removed Tailwind's 'prose' and applied our own '.custom-prose'
    --}}
    <div class="custom-prose">
        {!! $page->content !!}
    </div>

</div>
@endsection