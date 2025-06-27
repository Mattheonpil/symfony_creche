import { startStimulusApp } from '@symfony/stimulus-bundle';
import PlanningHoursController from './controllers/planning_hours_controller.js';
import PlanningFeedbackController from './controllers/planning_feedback_controller.js';
import '@symfony/ux-live-component';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
app.register('planning-hours', PlanningHoursController);
app.register('planning-feedback', PlanningFeedbackController);
