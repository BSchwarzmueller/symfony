import { startStimulusApp } from '@symfony/stimulus-bridge';

// lädt alle Controller aus ./controllers anhand der controllers.json
startStimulusApp(require.context('@symfony/stimulus-bridge/lazy-controller-loader!./controllers', true, /\.[jt]sx?$/));

// Registriert die Symfony UX Vue-Integration
import '@symfony/ux-vue';
