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

        // DEBUG
        console.log('Soumission AJAX', planningId, formData);

        fetch(`/planning/${planningId}/presence-save`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Réponse AJAX', data);
            if (data.success) {
                // Met à jour la barre de présence
                // On cherche la .presence-bar dans la même child-row que l'editor-row
                const editorRow = editor;
                const childRow = editorRow.previousElementSibling;
                if (childRow && childRow.classList.contains('child-row')) {
                    const bar = childRow.querySelector('.presence-bar');
                    if (bar) {
                        bar.classList.remove('primary', 'orange', 'green', 'red');
                        bar.classList.add(data.barColor);
                        // Met à jour le texte si besoin
                        if (data.actual_arrival && data.actual_departure) {
                            bar.textContent = `${data.actual_arrival} - ${data.actual_departure}`;
                        } else if (data.actual_arrival) {
                            bar.textContent = `${data.actual_arrival} - ${bar.textContent.split(' - ')[1]}`;
                        } else if (data.actual_departure) {
                            bar.textContent = `${bar.textContent.split(' - ')[0]} - ${data.actual_departure}`;
                        }
                    }
                }
                // Ferme l'éditeur
                editor.innerHTML = '';
                editor.style.display = 'none';
            } else {
                // Affiche le formulaire avec erreurs
                editor.innerHTML = data.form;
            }
        });
    }
} 