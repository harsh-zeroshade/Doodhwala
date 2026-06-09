const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

export function toast(msg) {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.style.display = 'block';
    setTimeout(() => { t.style.display = 'none'; }, 2400);
}

export function changeQty(inputId, delta) {
    const el = document.getElementById(inputId);
    if (!el) return;
    const v = Math.max(0, Math.round((parseFloat(el.value || 0) + delta) * 10) / 10);
    el.value = v.toFixed(1);
    el.dispatchEvent(new Event('change', { bubbles: true }));
}

export async function apiPost(url, body = {}) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            Accept: 'application/json',
        },
        body: JSON.stringify(body),
    });
    if (!res.ok) throw new Error('Request failed');
    return res.json();
}

export async function apiDelete(url) {
    const res = await fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrf(),
            Accept: 'application/json',
        },
    });
    if (!res.ok) throw new Error('Request failed');
    return res.json();
}

window.doodhwala = { toast, changeQty, apiPost, apiDelete };

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-drawer-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const drawer = document.getElementById(btn.dataset.drawerOpen);
            const overlay = document.getElementById(btn.dataset.drawerOverlay);
            drawer?.classList.add('open');
            overlay?.classList.add('open');
        });
    });

    document.querySelectorAll('[data-drawer-close]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const drawer = document.getElementById(btn.dataset.drawerClose);
            const overlay = document.getElementById(btn.dataset.drawerOverlay);
            drawer?.classList.remove('open');
            overlay?.classList.remove('open');
        });
    });
});
