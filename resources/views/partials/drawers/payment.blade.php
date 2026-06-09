<div class="drawer-overlay" id="ov2"></div>
<div class="drawer" id="dPay">
    <div class="drawer-handle"></div>
    <p class="text-[17px] font-extrabold text-slate-900 m-0 mb-0.5">{{ __('Add Cash Payment') }}</p>
    <p id="payName" class="text-[13px] text-slate-500 m-0 mb-4"></p>
    <div class="bg-red-50 border-[1.5px] border-red-200 rounded-xl py-3 px-4 mb-4 flex items-center justify-between">
        <span class="text-[13px] text-slate-500 font-semibold">{{ __('Outstanding Amount') }}</span>
        <span id="payDue" class="text-[22px] font-extrabold text-red-700"></span>
    </div>
    <p class="lbl">{{ __('Amount Received (₹)') }}</p>
    <input class="input-field mb-3 text-[22px] font-extrabold text-center" type="number" id="payAmt" placeholder="0" inputmode="numeric">
    <p class="lbl">{{ __('Payment Mode') }}</p>
    <div class="grid grid-cols-2 gap-2.5 mb-5">
        <button type="button" id="m1" class="chip chip-green justify-center rounded-xl h-11">💵 {{ __('Cash') }}</button>
        <button type="button" id="m2" class="chip chip-blue justify-center rounded-xl h-11">📱 {{ __('UPI / GPay') }}</button>
    </div>
    <button type="button" class="btn-primary" id="recordPayBtn">{{ __('Record Payment') }}</button>
</div>

<script>
document.getElementById('ov2')?.addEventListener('click', () => {
    document.getElementById('dPay')?.classList.remove('open');
    document.getElementById('ov2')?.classList.remove('open');
});
</script>
