import { initCarousels } from './modules/carousel';
import { initContactForm, initMailLinks, initScrollSpy } from './modules/contact';
import { initCursor, initMagnetic } from './modules/cursor';
import { initFilters, initMenu } from './modules/interface';
import { initLoader } from './modules/loader';
import { initProjectMorph } from './modules/media';
import { initReveals, prepareReveals } from './modules/reveal';
import { initHeader, initHeroPointer, initParallax } from './modules/scroll';
import { initTheme } from './modules/theme';
import { whenLoaded } from './modules/utils';

window.__kliq = true;

prepareReveals();
initTheme();
initLoader();
initMenu();
initHeader();
initCursor();
initMagnetic();
initParallax();
initHeroPointer();
initFilters();
initCarousels();
initProjectMorph();
initContactForm();
initScrollSpy();
initMailLinks();

whenLoaded().then(() => {
    initReveals();
});
