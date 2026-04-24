@extends('layouts.platform')

@section('title', 'Inquiry from ' . $contactInquiry->name)
@section('header', 'Contact Inquiry')

@section('content')
    <div class="pb-10 max-w-2xl">

        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('platform.inquiries.index') }}"
                class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            {{-- Header --}}
            <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $contactInquiry->name }}</h2>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                            <a href="mailto:{{ $contactInquiry->email }}" class="hover:underline">{{ $contactInquiry->email }}</a>
                        </span>
                        @if ($contactInquiry->phone)
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                {{ $contactInquiry->phone }}
                            </span>
                        @endif
                        <span class="flex items-center gap-1.5 text-xs text-gray-400">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            {{ $contactInquiry->created_at->format('d M Y, h:i A') }}
                        </span>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $contactInquiry->is_read ? 'bg-gray-100 text-gray-500' : 'bg-brand-500/10 text-brand-600' }}">
                    {{ $contactInquiry->is_read ? 'Read' : 'Unread' }}
                </span>
            </div>

            {{-- Message --}}
            <div class="px-6 py-5">
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $contactInquiry->message }}</p>
            </div>

            {{-- Actions --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-5">
                        <a href="mailto:{{ $contactInquiry->email }}"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700">
                        <i data-lucide="mail" class="w-4 h-4"></i> Reply via Email
                        </a>

                        @if ($contactInquiry->phone)
                        <a href="tel:{{ $contactInquiry->phone }}"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700">
                        <i data-lucide="phone" class="w-4 h-4"></i> Call {{ $contactInquiry->phone }}
                        </a>
                    @endif
                </div>

               <form method="POST" action="{{ route('platform.inquiries.destroy', $contactInquiry) }}" id="delete-inquiry-form">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDelete()"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-red-500 hover:text-red-700">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Delete
                    </button>
                </form>

            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function confirmDelete() {
            Swal.fire({
                title: 'Delete this inquiry?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444', // Tailwind red-500
                cancelButtonColor: '#6b7280',  // Tailwind gray-500
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-inquiry-form').submit();
                }
            });
        }
    </script>
@endsection
