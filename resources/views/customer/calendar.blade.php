@extends('layouts.doodhwala')

@section('content')
<div id="cPage2" class="page active px-4">

    {{-- Month navigator --}}
    <div class="flex items-center gap-2 pt-4 pb-3">
        <a href="{{ route('customer.calendar', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}"
           class="w-9 h-9 flex items-center justify-center rounded-[12px] shrink-0 no-underline"
           style="background:#F1F5F9;border:1.5px solid #E2E8F0;">
            <i class="ph ph-caret-left" style="font-size:16px;color:#475569;"></i>
        </a>
        <div class="flex-1 text-center">
            <p class="m-0 font-extrabold text-slate-900" style="font-size:17px;letter-spacing:-.02em;">{{ $month->translatedFormat('F Y') }}</p>
            <p class="m-0 text-xs text-slate-400 mt-0.5">{{ $deliveredCount }} {{ __('days delivered') }}</p>
        </div>
        @if($month->lt(now()->startOfMonth()))
            <a href="{{ route('customer.calendar', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}"
               class="w-9 h-9 flex items-center justify-center rounded-[12px] shrink-0 no-underline"
               style="background:#F1F5F9;border:1.5px solid #E2E8F0;">
                <i class="ph ph-caret-right" style="font-size:16px;color:#475569;"></i>
            </a>
        @else
            <div class="w-9 h-9 flex items-center justify-center rounded-[12px] shrink-0"
                 style="background:#F8FAFC;border:1.5px solid #F1F5F9;opacity:.4;">
                <i class="ph ph-caret-right" style="font-size:16px;color:#94A3B8;"></i>
            </div>
        @endif
    </div>

    {{-- Monthly spend summary (3-part) --}}
    <div class="grid grid-cols-3 gap-2 mb-4">
        <div class="card p-3 text-center">
            <p class="lbl text-center" style="color:#15803D;">{{ __('Spent') }}</p>
            <p class="m-0 font-extrabold" style="font-size:17px;color:#15803D;letter-spacing:-.02em;">₹{{ number_format($monthSpend) }}</p>
        </div>
        <div class="card p-3 text-center">
            <p class="lbl text-center" style="color:#1E40AF;">{{ __('Liters') }}</p>
            <p class="m-0 font-extrabold" style="font-size:17px;color:#1E40AF;letter-spacing:-.02em;">{{ number_format($monthLiters, 1) }}L</p>
        </div>
        <div class="card p-3 text-center {{ $monthDue > 0 ? '' : '' }}">
            <p class="lbl text-center" style="color:{{ $monthDue > 0 ? '#DC2626' : '#16A34A' }};">{{ $monthDue > 0 ? __('Due') : __('Paid') }}</p>
            <p class="m-0 font-extrabold" style="font-size:17px;color:{{ $monthDue > 0 ? '#DC2626' : '#16A34A' }};letter-spacing:-.02em;">₹{{ number_format($monthDue > 0 ? $monthDue : $monthPaid) }}</p>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex gap-2 mb-3 justify-end">
        <span class="badge badge-green"><i class="ph ph-check" style="font-size:9px;"></i> {{ __('Delivered') }}</span>
        <span class="badge badge-red"><i class="ph ph-minus" style="font-size:9px;"></i> {{ __('Skipped') }}</span>
    </div>

    <div class="card p-3.5">
        <div class="grid grid-cols-7 gap-[3px] mb-1.5">
            @foreach(['S','M','T','W','T','F','S'] as $dow)
                <div class="text-center text-[10px] font-bold text-slate-400 py-1">{{ $dow }}</div>
            @endforeach
        </div>
        <div id="calGrid" class="grid grid-cols-7 gap-[3px]">
            @for($i = 0; $i < $firstWeekday; $i++)
                <div></div>
            @endfor
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $record = $records->get($day);
                    $isToday = $month->copy()->day($day)->isToday();
                    $isFuture = $month->copy()->day($day)->isFuture();
                    $isDelivered = $record && $record->status === 'delivered';
                    $cellClass = $isToday ? 'cal-today' : ($isFuture ? 'cal-future' : ($isDelivered ? 'cal-delivered' : 'cal-skipped'));
                @endphp
                <div class="calendar-cell {{ $cellClass }} cal-day"
                     data-day="{{ $day }}"
                     data-date="{{ $month->copy()->day($day)->toDateString() }}">
                    <span class="text-xs font-extrabold">{{ $day }}</span>
                    @if($isToday)
                        <span class="text-[9px] font-extrabold mt-px">{{ __('NOW') }}</span>
                    @elseif($isDelivered)
                        <span class="text-[9px] font-extrabold mt-px">{{ number_format($record->totalLiters(), 1) }}L</span>
                    @elseif(!$isFuture)
                        <span class="text-[9px] mt-px">—</span>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    <div id="dayPanel" class="mt-3 hidden">
        <div class="card p-4">
            <p class="m-0 mb-2.5 text-xs font-bold text-slate-400 uppercase tracking-wide">{{ $month->translatedFormat('F') }} <span id="selDay">1</span>, {{ $month->year }}</p>
            <div id="dayContent"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const { toast } = window.doodhwala;
    const dayUrl = @json(route('customer.calendar.day'));
    const labels = {
        today:     { icon: '🛵', msg: @json(__('Delivery in progress today')), sub: @json(__('Milkman is on the way')) },
        future:    { msg: @json(__('No delivery data yet.')) },
        skipped:   { icon: '❌', msg: @json(__('No Delivery')), sub: @json(__('Milkman was on leave / holiday')) },
        delivered: { icon: '✅', tpl: @json(__('Delivered · :totalL total')) },
    };

    document.querySelectorAll('.cal-day').forEach(cell => {
        cell.addEventListener('click', async () => {
            document.getElementById('selDay').textContent = cell.dataset.day;
            const panel   = document.getElementById('dayPanel');
            const content = document.getElementById('dayContent');
            panel.classList.remove('hidden');

            const res  = await fetch(dayUrl + '?date=' + cell.dataset.date, { headers: { Accept: 'application/json' } });
            const data = await res.json();

            if (data.type === 'today') {
                content.innerHTML = `<div class="flex items-center gap-2.5"><span class="text-2xl">${labels.today.icon}</span><div><p class="m-0 text-sm font-bold text-blue-700">${labels.today.msg}</p><p class="mt-0.5 text-xs text-slate-500 m-0">${labels.today.sub}</p></div></div>`;
            } else if (data.type === 'future') {
                content.innerHTML = `<p class="m-0 text-sm text-slate-400">${labels.future.msg}</p>`;
            } else if (data.type === 'skipped') {
                content.innerHTML = `<div class="flex items-center gap-2.5"><span class="text-2xl">${labels.skipped.icon}</span><div><p class="m-0 text-sm font-bold text-red-700">${labels.skipped.msg}</p><p class="mt-0.5 text-xs text-slate-500 m-0">${labels.skipped.sub}</p></div></div>`;
            } else {
                content.innerHTML = `<div class="flex items-center gap-2.5"><span class="text-2xl">${labels.delivered.icon}</span><div><p class="m-0 text-sm font-bold text-emerald-600">${labels.delivered.tpl.replace(':totalL', data.total.toFixed(1))}</p><p class="mt-0.5 text-xs text-slate-500 m-0">🐄 ${data.cow}L · 🐃 ${data.buffalo}L</p></div></div>`;
            }
        });
    });
});
</script>
@endpush
