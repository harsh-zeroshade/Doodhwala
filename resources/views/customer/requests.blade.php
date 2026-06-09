@extends('layouts.doodhwala')

@section('content')
<div id="cPage3" class="page active px-4">
    <p class="py-3.5 text-[13px] text-slate-500 m-0">{{ __('Set vacation holds or request extra milk') }}</p>

    <div class="card p-4 mb-4">
        <p class="m-0 mb-4 text-[15px] font-extrabold text-slate-900">{{ __('New Request') }}</p>

        <p class="lbl">{{ __('Select Date') }}</p>
        <input type="date" class="input-field mb-4" id="reqDate" min="{{ today()->toDateString() }}">

        <p class="lbl">{{ __('Request Type') }}</p>
        <div class="grid grid-cols-2 gap-2.5 mb-4">
            <button type="button" id="btnHold" class="req-type-btn p-3.5 rounded-xl border-2 border-red-300 bg-red-50 text-center" data-type="hold">
                <div class="text-xl mb-1">✈</div>
                <p class="m-0 text-[13px] font-bold text-red-700">{{ __('Vacation Hold') }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400 m-0">{{ __('Skip (0L)') }}</p>
            </button>
            <button type="button" id="btnExtra" class="req-type-btn p-3.5 rounded-xl border-2 border-slate-200 bg-slate-50 text-center" data-type="extra">
                <div class="text-xl mb-1">➕</div>
                <p class="m-0 text-[13px] font-bold text-blue-700">{{ __('Extra Milk') }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400 m-0">{{ __('Add quantity') }}</p>
            </button>
        </div>

        <div id="extraQtyBlock" class="hidden mb-4">
            <p class="lbl">{{ __('Extra Cow Milk (L)') }}</p>
            <div class="flex items-center gap-2.5 mb-3">
                <div class="qty-ctrl border-blue-200">
                    <button type="button" class="qty-btn bg-blue-50 text-blue-600 qty-minus-ex" data-target="exCow">−</button>
                    <input class="qty-val border-blue-200" id="exCow" value="0.5" readonly>
                    <button type="button" class="qty-btn bg-blue-50 text-blue-600 qty-plus-ex" data-target="exCow">+</button>
                </div>
                <span class="text-[13px] text-slate-500">{{ __('liters') }}</span>
            </div>
            <p class="lbl">{{ __('Extra Buffalo Milk (L)') }}</p>
            <div class="flex items-center gap-2.5">
                <div class="qty-ctrl border-blue-200">
                    <button type="button" class="qty-btn bg-blue-50 text-blue-600 qty-minus-ex" data-target="exBuf">−</button>
                    <input class="qty-val border-blue-200" id="exBuf" value="0.0" readonly>
                    <button type="button" class="qty-btn bg-blue-50 text-blue-600 qty-plus-ex" data-target="exBuf">+</button>
                </div>
                <span class="text-[13px] text-slate-500">{{ __('liters') }}</span>
            </div>
        </div>

        <p class="lbl">{{ __('Note for Milkman') }}</p>
        <textarea class="input-field resize-none h-[70px] leading-relaxed mb-4" id="reqNote" rows="2" placeholder="{{ __('e.g. Guests coming, please bring extra...') }}"></textarea>
        <button type="button" class="btn-primary" id="submitRequest">{{ __('Submit Request') }}</button>
    </div>

    <p class="lbl">{{ __('Upcoming Requests') }}</p>
    <div id="reqList" class="flex flex-col gap-2.5">
        @foreach($upcoming as $order)
            <div class="card p-4" data-order-id="{{ $order->id }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="m-0 text-sm font-bold text-slate-900">{{ $order->order_date->translatedFormat('M j, Y') }}</p>
                        <div class="mt-1 flex items-center gap-1.5">
                            @if($order->type === 'hold')
                                <span class="badge badge-red">✈ {{ __('Vacation Hold') }}</span>
                            @else
                                <span class="badge badge-blue">➕ {{ __('Extra Milk') }}</span>
                                <span class="text-xs text-slate-500">+{{ $order->extra_cow_milk }}L {{ __('Cow') }} @if($order->note)· {{ $order->note }}@endif</span>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="cancel-req bg-red-100 text-red-700 border-0 rounded-lg py-1.5 px-3 text-xs font-bold cursor-pointer" data-id="{{ $order->id }}">{{ __('Cancel') }}</button>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const { apiPost, apiDelete, toast } = window.doodhwala;
    let reqType = 'hold';
    const storeUrl  = @json(route('customer.requests.store'));
    const cancelUrl = @json(route('customer.requests.cancel', ['extraOrder' => '__ID__']));

    const pickType = (t) => {
        reqType = t;
        document.getElementById('btnHold').className  = 'req-type-btn p-3.5 rounded-xl border-2 text-center ' +
            (t === 'hold'  ? 'border-red-500  bg-red-50  shadow-[0_0_0_3px_rgba(239,68,68,.15)]'   : 'border-slate-200 bg-slate-50');
        document.getElementById('btnExtra').className = 'req-type-btn p-3.5 rounded-xl border-2 text-center ' +
            (t === 'extra' ? 'border-blue-500 bg-blue-50 shadow-[0_0_0_3px_rgba(59,130,246,.15)]'  : 'border-slate-200 bg-slate-50');
        document.getElementById('extraQtyBlock').classList.toggle('hidden', t !== 'extra');
    };

    document.getElementById('btnHold').addEventListener('click',  () => pickType('hold'));
    document.getElementById('btnExtra').addEventListener('click', () => pickType('extra'));

    document.querySelectorAll('.qty-plus-ex, .qty-minus-ex').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            const delta = btn.classList.contains('qty-plus-ex') ? 0.5 : -0.5;
            const v = Math.max(0, Math.round((parseFloat(input.value) + delta) * 10) / 10);
            input.value = v.toFixed(1);
        });
    });

    document.getElementById('submitRequest').addEventListener('click', async () => {
        const dt = document.getElementById('reqDate').value;
        if (!dt) { document.getElementById('reqDate').style.borderColor = '#ef4444'; return; }
        const body = {
            order_date:          dt,
            type:                reqType,
            note:                document.getElementById('reqNote').value,
            extra_cow_milk:      parseFloat(document.getElementById('exCow').value),
            extra_buffalo_milk:  parseFloat(document.getElementById('exBuf').value),
        };
        const res = await apiPost(storeUrl, body);
        const list = document.getElementById('reqList');
        const item = document.createElement('div');
        item.className = 'card p-4';
        item.dataset.orderId = res?.order?.id ?? '';
        const lbl = reqType === 'hold'
            ? '<span class="badge badge-red">✈ '  + @json(__('Vacation Hold')) + '</span>'
            : '<span class="badge badge-blue">➕ ' + @json(__('Extra Milk'))    + '</span>';
        item.innerHTML =
            '<div class="flex items-center justify-between">' +
                '<div>' +
                    '<p class="m-0 text-sm font-bold text-slate-900">' + dt + '</p>' +
                    '<div class="mt-1">' + lbl + '</div>' +
                '</div>' +
                '<button type="button" class="cancel-req bg-red-100 text-red-700 border-0 rounded-lg py-1.5 px-3 text-xs font-bold"' +
                    (res?.order?.id ? ' data-id="' + res.order.id + '"' : '') + '>' +
                    @json(__('Cancel')) +
                '</button>' +
            '</div>';
        list.prepend(item);
        document.getElementById('reqDate').value = '';
        toast(@json(__('Request submitted ✓')));
    });

    document.getElementById('reqList').addEventListener('click', async (e) => {
        const btn = e.target.closest('.cancel-req');
        if (!btn) return;
        const card = btn.closest('.card');
        const id   = btn.dataset.id;
        if (id) await apiDelete(cancelUrl.replace('__ID__', id));
        card.remove();
    });
});
</script>
@endpush
