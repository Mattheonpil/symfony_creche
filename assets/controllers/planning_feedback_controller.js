import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['inputGroup', 'select'];

  connect() {
    this.inputGroupTargets.forEach(group => this.updateGroup(group));
    this.selectTargets.forEach(select => {
      select.addEventListener('change', () => this.updateGroup(select.closest('.input-group')));
    });
  }

  updateGroup(group) {
    const selects = group.querySelectorAll('select');
    const allFilled = Array.from(selects).every(s => s.value && s.value !== 'HH' && s.value !== 'MM');
    group.classList.toggle('filled', allFilled);
  }
}
