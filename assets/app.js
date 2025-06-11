import './bootstrap.js';
import './styles/app.css';
import { Timeline, DataSet } from "vis-timeline/standalone";

document.addEventListener('DOMContentLoaded', function() {
    initializeTimeline();
});

function initializeTimeline() {
    const container = document.querySelector('.timeline-container');
    if (!container) return;

    const timelineElement = document.getElementById('timeline');
    const date = container.dataset.date;
    const plannings = JSON.parse(container.dataset.plannings || '[]');

    // Add validation
    if (!plannings.length) {
        timelineElement.innerHTML = '<p>Aucun planning disponible pour cette date</p>';
        return;
    }

    // Prepare items with validation
    const items = new DataSet(
        plannings.filter(planning => planning && planning.child).map(planning => ({
            id: planning.id,
            content: planning.child ? `${planning.child.firstName || ''} ${planning.child.name || ''}`.trim() : 'Inconnu',
            start: `${date} ${planning.startTime || '00:00:00'}`,
            end: `${date} ${planning.endTime || '00:00:00'}`,
            group: planning.child ? planning.child.id : 0
        }))
    );

    // Prepare groups
    const groups = new DataSet(
        plannings.map(planning => ({
            id: planning.child.id,
            content: `${planning.child.firstName} ${planning.child.name}`
        }))
    );

    // Configure options
    const options = {
        start: `${date} 07:00`,
        end: `${date} 19:00`,
        timeAxis: { scale: 'hour', step: 1 },
        orientation: 'top',
        stack: false,
        showMajorLabels: true,
        showMinorLabels: true,
        zoomable: false,
        moveable: false
    };

    // Initialize timeline
    new Timeline(timelineElement, items, groups, options);
}



