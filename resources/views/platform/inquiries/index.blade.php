@extends('layouts.platform')

@section('title', 'Contact Inquiries')
@section('header', 'Contact Inquiries')

@section('content')
    <div class="pb-10">

        {{-- Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Contact Inquiries</h1>
                <p class="text-sm text-gray-500 mt-1">Messages submitted via the public landing page.</p>
            </div>
            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full">
                {{ $inquiries->total() }} total
            </span>
        </div>

        @if (session('success'))
            <div class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @if ($inquiries->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <i data-lucide="inbox" class="w-10 h-10 mb-3"></i>
                    <p class="text-sm font-medium">No inquiries yet</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-5 py-3.5 font-semibold text-xs text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-xs text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-xs text-gray-500 uppercase tracking-wider hidden md:table-cell">Message</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-xs text-gray-500 uppercase tracking-wider hidden lg:table-cell">Date</th>
                            <th class="px-5 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($inquiries as $inquiry)
                            <tr class="hover:bg-gray-50 transition-colors {{ $inquiry->is_read ? '' : 'bg-blue-50/40' }}">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        @if (! $inquiry->is_read)
                                            <span class="w-2 h-2 rounded-full bg-brand-600 shrink-0"></span>
                                        @else
                                            <span class="w-2 h-2 shrink-0"></span>
                                        @endif
                                        <span class="font-semibold text-gray-800">{{ $inquiry->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">{{ $inquiry->email }}</td>
                                <td class="px-5 py-4 text-gray-500 hidden md:table-cell max-w-xs truncate">
                                    {{ Str::limit($inquiry->message, 80) }}
                                </td>
                                <td class="px-5 py-4 text-gray-400 text-xs hidden lg:table-cell whitespace-nowrap">
                                    {{ $inquiry->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('platform.inquiries.show', $inquiry) }}"
                                        class="text-brand-600 hover:text-brand-700 font-semibold text-xs">
                                        View →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($inquiries->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $inquiries->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
