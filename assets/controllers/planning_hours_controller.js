import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        "lundiA", "lundiD",
        "mardiA", "mardiD",
        "mercrediA", "mercrediD",
        "jeudiA", "jeudiD",
        "vendrediA", "vendrediD",
        "dayTotal", "weekTotal"
    ];

    connect() {
        this.updateAll();
    }

    updateAll() {
        let weekTotalMinutes = 0;
        const days = ["lundi", "mardi", "mercredi", "jeudi", "vendredi"];
    
        days.forEach((day, index) => {
            const startWrapper = this[`${day}ATarget`];
            const endWrapper = this[`${day}DTarget`];
            const dayTotalTarget = this.dayTotalTargets[index];

            const startValue = this.getTimeFromWrapper(startWrapper);
            const endValue = this.getTimeFromWrapper(endWrapper);
    
            if (dayTotalTarget) {
                const minutes = this.calculateMinutes(startValue, endValue);
                if (minutes > 0) {
                    dayTotalTarget.textContent = this.formatMinutes(minutes);
                    dayTotalTarget.classList.add('has-value');
                } else {
                    dayTotalTarget.textContent = "-";
                    dayTotalTarget.classList.remove('has-value');
                }
                weekTotalMinutes += minutes;
            }
        });
    
        if (this.hasWeekTotalTarget) {
            if (weekTotalMinutes > 0) {
                this.weekTotalTarget.textContent = this.formatMinutes(weekTotalMinutes);
                this.weekTotalTarget.classList.add('has-value');
            } else {
                this.weekTotalTarget.textContent = "-";
                this.weekTotalTarget.classList.remove('has-value');
            }
        }
    }

    getTimeFromWrapper(wrapper) {
        if (!wrapper) return null;

        const hourEl = wrapper.querySelector('select[id$="_hour"]');
        const minuteEl = wrapper.querySelector('select[id$="_minute"]');

        if (hourEl && minuteEl && hourEl.value && minuteEl.value) {
            return `${hourEl.value}:${minuteEl.value}`;
        }

        return null;
    }

    calculateMinutes(start, end) {
        if (!start || !end) {
            return 0;
        }
        const [sh, sm] = start.split(":").map(Number);
        const [eh, em] = end.split(":").map(Number);
        if (isNaN(sh) || isNaN(sm) || isNaN(eh) || isNaN(em)) {
            return 0;
        }
        const startMinutes = sh * 60 + sm;
        const endMinutes = eh * 60 + em;
        const diff = endMinutes - startMinutes;
        return diff > 0 ? diff : 0;
    }

    formatMinutes(totalMinutes) {
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;
        return `${hours.toString().padStart(2, '0')}H${minutes.toString().padStart(2, '0')}`;
    }
}