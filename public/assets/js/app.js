document.addEventListener('submit', function (e) {
  var msg = e.target.getAttribute('data-confirm');
  if (msg && !window.confirm(msg)) e.preventDefault();
});

document.addEventListener('click', function (e) {
  if (e.target.closest('[data-toggle-sidenav]')) document.getElementById('sidenav').classList.toggle('show');
});

if (window.jQuery && jQuery.fn.select2) {
  jQuery(function ($) {
    $('select.select2').select2({ theme: 'bootstrap-5', width: '100%', allowClear: true, placeholder: $(this).data('placeholder') || 'Pilih…' });
  });
}
