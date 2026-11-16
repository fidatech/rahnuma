{{--
    One pop-up form serves every row of a list. A button with data-edit (the values as JSON),
    data-action (where the form goes) and data-modal (the id of the pop-up) fills the form and opens it.
--}}
<script>
(() => {
 if (window.rahnumaEditBound) return; window.rahnumaEditBound = true;
 document.addEventListener('click', (e) => {
  const button = e.target.closest('[data-edit]'); if (!button) return;
  const modal = document.getElementById(button.dataset.modal), form = modal?.querySelector('form'); if (!form) return;
  if (button.dataset.action) form.action = button.dataset.action;
  let values = {}; try { values = JSON.parse(button.dataset.edit || '{}'); } catch (err) {}
  Object.entries(values).forEach(([name, value]) => {
   const field = form.elements[name]; if (!field || field instanceof RadioNodeList) return;
   if (field.type === 'checkbox') { field.checked = !!value && value !== '0'; return; }
   field.value = value ?? '';
   if (window.jQuery && field.tagName === 'SELECT') window.jQuery(field).trigger('change.select2');
  });
  modal.querySelectorAll('[data-edit-title]').forEach((el) => { el.textContent = button.dataset.title || ''; });
  window.ERP.showModal(modal);
 });
})();
</script>
