export function setupRecoveryCollection() {
    const collection = document.getElementById('recovery-collection');
    const addBtn = document.getElementById('add-recovery-btn');
    if (!collection || !addBtn) return;

    addBtn.addEventListener('click', function() {
        const prototype = collection.dataset.prototype;
        const index = collection.querySelectorAll('.recovery-item').length;
        const newForm = prototype.replace(/__name__/g, index);
        const temp = document.createElement('div');
        temp.innerHTML = newForm;
        const item = temp.firstElementChild;
        item.classList.add('recovery-item');
        // Ajoute le bouton de suppression
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'remove-recovery-btn form-button form-button--delete btn-position';
        removeBtn.textContent = 'Supprimer';
        item.appendChild(removeBtn);
        collection.appendChild(item);
    });

    collection.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-recovery-btn')) {
            e.target.closest('.recovery-item').remove();
        }
    });
}
