import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["userSelect", "userInfo"];

    connect() {
        if (this.hasUserSelectTarget) {
            this.userSelectTarget.addEventListener('change', this.onUserChange.bind(this));
        }
    }

    async onUserChange(event) {
        const userId = event.target.value;
        if (!userId) {
            this.userInfoTarget.innerHTML = '';
            return;
        }
        try {
            const response = await fetch(`/ajax/parent-info/${userId}`);
            if (!response.ok) throw new Error('Erreur lors du chargement de la fiche parent');
            const html = await response.text();
            this.userInfoTarget.innerHTML = html;
        } catch (e) {
            this.userInfoTarget.innerHTML = '<div class="error">Impossible de charger la fiche parent.</div>';
        }
    }
} 