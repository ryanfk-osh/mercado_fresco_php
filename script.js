const body = document.body;
const drawer = document.getElementById('cartDrawer');
const backdrop = document.getElementById('cartBackdrop');
const openBtn = document.getElementById('openCart');
const closeBtn = document.getElementById('closeCart');
function openCart(){ body.classList.add('cart-open'); drawer.setAttribute('aria-hidden','false'); }
function closeCart(){ body.classList.remove('cart-open'); drawer.setAttribute('aria-hidden','true'); }
openBtn?.addEventListener('click', openCart);
closeBtn?.addEventListener('click', closeCart);
backdrop?.addEventListener('click', closeCart);
document.getElementById('continueShopping')?.addEventListener('click', closeCart);
document.addEventListener('keydown', e => { if(e.key === 'Escape') closeCart(); });

document.querySelectorAll('.quantity-form').forEach(form => {
  const input = form.querySelector('input[name="quantidade"]');
  form.querySelector('.qty-minus')?.addEventListener('click', () => {
    input.value = Math.max(0, (parseInt(input.value, 10) || 0) - 1);
  });
  form.querySelector('.qty-plus')?.addEventListener('click', () => {
    input.value = Math.min(99, (parseInt(input.value, 10) || 0) + 1);
  });
});

document.getElementById('checkout')?.addEventListener('click', () => {
  const toast = document.getElementById('toast');
  toast?.classList.add('visible');
  window.setTimeout(() => toast?.classList.remove('visible'), 3500);
});
