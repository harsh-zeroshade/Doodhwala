@extends('layouts.doodhwala')

@section('content')
<div id="cPage1" class="page active px-4">
    @if($liveStatus)
        <div class="my-4 bg-amber-50 border-[1.5px] border-amber-200 rounded-[14px] py-3 px-3.5 flex items-center gap-3">
            <div class="w-[34px] h-[34px] bg-amber-100 rounded-[10px] flex items-center justify-center shrink-0 text-xl">🛵</div>
            <div class="flex-1">
                <p class="m-0 text-[11px] font-bold text-amber-700 tracking-wide uppercase">{{ __('Live Status from :name', ['name' => $milkmanName]) }}</p>
                <p class="mt-0.5 text-sm font-bold text-slate-900">{{ $liveStatus->message }}</p>
            </div>
            <p class="m-0 text-[11px] text-amber-800 font-medium whitespace-nowrap">{{ $liveStatus->created_at->diffForHumans() }}</p>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 mb-3.5">
        <div class="metric-card bg-red-50 border-red-200">
            <p class="text-[11px] font-bold text-red-700 uppercase tracking-wide m-0 mb-1">{{ __('Pending Due') }}</p>
            <p class="text-[26px] font-extrabold text-red-700 m-0 leading-tight">₹{{ number_format($pendingDue) }}</p>
            <p class="mt-1 text-[11px] text-red-500 font-semibold m-0">{{ $monthLabel }}</p>
        </div>
        <div class="metric-card bg-green-50 border-green-200">
            <p class="text-[11px] font-bold text-green-700 uppercase tracking-wide m-0 mb-1">{{ __('Consumed') }}</p>
            <p class="text-[26px] font-extrabold text-emerald-600 m-0 leading-tight">{{ number_format($consumedLiters, 1) }}L</p>
            <p class="mt-1 text-[11px] text-green-600 font-semibold m-0">{{ __('This month') }}</p>
        </div>
    </div>

    <div class="card p-4 mb-3.5">
        <p class="m-0 mb-3 text-xs font-bold text-slate-400 uppercase tracking-widest">{{ __('This Week') }}</p>
        <div class="flex gap-2">
            <div class="flex-1 text-center py-3 px-2 bg-green-50 rounded-xl">
                <div class="text-[22px] mb-1">🐄</div>
                <p class="m-0 text-xl font-extrabold text-emerald-600">{{ number_format($weekCow, 0) }}L</p>
                <p class="mt-1 text-[11px] text-slate-500 font-semibold m-0">{{ __('Cow') }}</p>
            </div>
            <div class="flex-1 text-center py-3 px-2 bg-blue-50 rounded-xl">
                <div class="text-[22px] mb-1">🐃</div>
                <p class="m-0 text-xl font-extrabold text-blue-600">{{ number_format($weekBuffalo, 1) }}L</p>
                <p class="mt-1 text-[11px] text-slate-500 font-semibold m-0">{{ __('Buffalo') }}</p>
            </div>
            <div class="flex-1 text-center py-3 px-2 bg-yellow-50 rounded-xl">
                <div class="text-[22px] mb-1">💰</div>
                <p class="m-0 text-xl font-extrabold text-yellow-700">₹{{ number_format($weekDue) }}</p>
                <p class="mt-1 text-[11px] text-slate-500 font-semibold m-0">{{ __('Due') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2.5">
        <a href="{{ route('customer.calendar') }}" class="bg-white border-[1.5px] border-slate-200 rounded-[14px] p-4 text-left no-underline">
            <div class="text-2xl mb-2">📅</div>
            <p class="m-0 text-sm font-bold text-slate-900">{{ __('View Calendar') }}</p>
            <p class="mt-0.5 text-xs text-slate-400 m-0">{{ __('Delivery history') }}</p>
        </a>
        <a href="{{ route('customer.requests') }}" class="bg-white border-[1.5px] border-slate-200 rounded-[14px] p-4 text-left no-underline">
            <div class="text-2xl mb-2">✏️</div>
            <p class="m-0 text-sm font-bold text-slate-900">{{ __('Manage Holds') }}</p>
            <p class="mt-0.5 text-xs text-slate-400 m-0">{{ __('Vacation / Extra') }}</p>
        </a>
    </div>
</div>
@endsection
