// Gestion suppression/annulation responsables/accompagnateurs pour add_child_to_user

document.addEventListener('DOMContentLoaded', function() {
    // Sélecteur des boutons toggle
    const btns = document.querySelectorAll('.toggle-remove-btn');
    if (!btns.length) return;

    // Champ hidden pour stocker les IDs à exclure
    let hiddenInput = document.getElementById('excluded_responsables');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'excluded_responsables'; // plus de crochets
        hiddenInput.id = 'excluded_responsables';
        document.getElementById('add-child-to-user-form').appendChild(hiddenInput);
    }
    let excludedIds = [];

    btns.forEach(function(btn) {
        const responsableId = btn.getAttribute('data-responsable-id');
        const block = document.querySelector('.removable-responsable[data-responsable-id="' + responsableId + '"]');
        if (!block || !btn || !responsableId) return;

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!block.classList.contains('to-be-removed')) {
                block.classList.add('to-be-removed');
                btn.textContent = 'Annuler la suppression pour cet enfant';
                btn.classList.remove('form-button--delete');
                btn.classList.add('form-button--cancel');
                excludedIds.push(responsableId);
            } else {
                block.classList.remove('to-be-removed');
                btn.textContent = 'Supprimer pour ce nouvel enfant';
                btn.classList.remove('form-button--cancel');
                btn.classList.add('form-button--delete');
                excludedIds = excludedIds.filter(id => id !== responsableId);
            }
            if (excludedIds.length > 0) {
                hiddenInput.value = excludedIds.join(',');
                hiddenInput.disabled = false;
            } else {
                hiddenInput.value = '';
                hiddenInput.disabled = true;
            }
        });
    });
}); 