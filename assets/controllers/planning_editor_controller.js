import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['editor']

    connect() {
        this.openEditorId = null;
    }

    toggle(event) {
        event.preventDefault();
        const clickedId = event.currentTarget.dataset.planningEditorIdValue;
        this.editorTargets.forEach(editor => {
            if (editor.dataset.planningId === clickedId) {
                if (editor.innerHTML.trim() === '') {
                    this.loadForm(clickedId, editor);
                }
                editor.style.display = (editor.style.display === 'none' || editor.style.display === '') ? 'block' : 'none';
            } else {
                editor.style.display = 'none';
            }
        });
    }

    loadForm(planningId, editor) {
        fetch(`/planning/${planningId}/presence-form`)
            .then(response => response.text())
            .then(html => {
                editor.innerHTML = html;
            });
    }

    submit(event) {
        event.preventDefault();
        const form = event.target;
        const planningId = form.dataset.planningId;
        const editor = this.editorTargets.find(e => e.dataset.planningId === planningId);

        const formData = new FormData(form);

        fetch(`/planning/${planningId}/presence-save`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {

                window.location.reload();

            } else {
                // Affiche le formulaire avec erreurs
                editor.innerHTML = data.form;
            }
        });
    }
} 