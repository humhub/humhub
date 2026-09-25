import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import ModuleCard from '../../modules/marketplace/vue/components/ModuleCard.vue';
import { marketplaceModule } from './support/marketplaceFixtures.mjs';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const mountCard = (module, props = {}) => mount(ModuleCard, {
    props: { module, professionalEditionUrl: '/pe', ...props },
});

const action = (wrapper) => wrapper.find('.c-module-card__footer .c-module-card__action');
const secondary = (wrapper) => wrapper.find('.c-module-card__footer .c-module-card__secondary');
const outdated = (overrides = {}) => marketplaceModule({ installedVersion: '1.0.0', updateAvailable: true, ...overrides });

describe('ModuleCard', () => {
    it('shows the image, name, version and description', () => {
        const wrapper = mountCard(marketplaceModule());

        expect(wrapper.find('.c-module-card__image').attributes('src')).toBe('/img/calendar.png');
        const title = wrapper.find('a.c-module-card__title');
        expect(title.text()).toBe('Calendar X');
        expect(title.attributes('href')).toBe('https://marketplace.humhub.com/module/calendar-x');
        expect(wrapper.find('.c-module-card__version').text()).toBe('2.0.0');
        expect(wrapper.find('.c-module-card__text').text()).toBe('Plan events together');
    });

    describe('the pill', () => {
        it('is the type pill, which filters by its type', async () => {
            const wrapper = mountCard(marketplaceModule());

            const pill = wrapper.find('button.c-module-card__badge--official');
            expect(pill.text()).toBe('Official');
            expect(pill.attributes('aria-label')).toBe('Filter by Official');
            await pill.trigger('click');
            expect(wrapper.emitted('filter-type')).toEqual([['official']]);
        });

        it.each([
            ['professional', 'PRO'],
            ['partner', 'Partner'],
            ['community', 'Community'],
        ])('shows the %s type and filters by it', async (badge, text) => {
            const wrapper = mountCard(marketplaceModule({ badge }));

            const pill = wrapper.find(`button.c-module-card__badge--${badge}`);
            expect(pill.text()).toBe(text);
            await pill.trigger('click');
            expect(wrapper.emitted('filter-type')).toEqual([[badge]]);
        });

        it('shows a featured star, filtering by tag=featured, next to the type pill', async () => {
            const wrapper = mountCard(marketplaceModule({ featured: true }));

            const star = wrapper.find('button.c-module-card__badge--star');
            expect(star.attributes('aria-label')).toBe('Featured: filter by Featured');
            expect(star.attributes('title')).toBe('Featured');
            expect(star.find('.ti-star-filled').exists()).toBe(true);
            await star.trigger('click');
            expect(wrapper.emitted('filter-type')).toEqual([['featured']]);

            expect(wrapper.find('button.c-module-card__badge--official').exists()).toBe(true);
        });

        it('has no featured star when the module is not featured', () => {
            expect(mountCard(marketplaceModule({ featured: false })).find('.c-module-card__badge--star').exists()).toBe(false);
        });

        it('shows the star next to the PRO pill for a professional and featured module', () => {
            const wrapper = mountCard(marketplaceModule({ badge: 'professional', featured: true }));

            expect(wrapper.find('.c-module-card__badge--star').exists()).toBe(true);
            const pill = wrapper.find('button.c-module-card__badge--professional');
            expect(pill.text()).toBe('PRO');
        });

        it('shows the star next to the update pill when a featured module has an update', () => {
            const wrapper = mountCard(outdated({ featured: true }));

            expect(wrapper.find('.c-module-card__badge--star').exists()).toBe(true);
            expect(wrapper.find('.c-module-card__badge--update').exists()).toBe(true);
        });

        it('carries the third-party hint on Community and Partner pills', () => {
            expect(mountCard(marketplaceModule({ badge: 'partner', isThirdParty: true })).find('.c-module-card__badge').attributes('title'))
                .toBe('This Module was developed by a third-party.');
            expect(mountCard(marketplaceModule({ badge: 'community', isThirdParty: true, isCommunity: true })).find('.c-module-card__badge').attributes('title'))
                .toBe('This Module was developed by a third-party. (Unverified Community)');
        });

        it('has no pill without a badge, and a plain one for a deprecated module', () => {
            expect(mountCard(marketplaceModule({ badge: 'none' })).find('.c-module-card__badge').exists()).toBe(false);

            const deprecated = mountCard(marketplaceModule({ badge: 'deprecated' })).find('.c-module-card__badge');
            expect(deprecated.element.tagName).toBe('SPAN');
            expect(deprecated.text()).toBe('Deprecated');
        });

        it('names the available version instead of the type, not clickable', () => {
            const wrapper = mountCard(outdated({ latestCompatibleVersion: '2.1.0' }));

            const pill = wrapper.find('.c-module-card__badge');
            expect(pill.element.tagName).toBe('SPAN');
            expect(pill.classes()).toContain('c-module-card__badge--update');
            expect(pill.text()).toBe('2.1.0');
            expect(pill.find('.ti-arrow-up').exists()).toBe(true);
            expect(wrapper.find('.c-module-card__version').text()).toBe('1.0.0');
        });
    });

    describe('the actions', () => {
        it('offers the installation and links the marketplace page', async () => {
            const wrapper = mountCard(marketplaceModule());

            expect(action(wrapper).text()).toBe('Install');
            expect(action(wrapper).classes()).toContain('btn-primary');
            await action(wrapper).trigger('click');
            expect(wrapper.emitted('install')).toHaveLength(1);

            expect(secondary(wrapper).attributes('href')).toBe('https://marketplace.humhub.com/module/calendar-x');
            expect(secondary(wrapper).attributes('aria-label')).toBe('Module information');
            expect(secondary(wrapper).attributes('title')).toBe('Module information');
            expect(secondary(wrapper).find('.ti-info-circle').exists()).toBe(true);
        });

        it('has no secondary button without a marketplace page', () => {
            expect(secondary(mountCard(marketplaceModule({ marketplaceUrl: null }))).exists()).toBe(false);
        });

        it('offers buying with the price', async () => {
            const wrapper = mountCard(marketplaceModule({ availability: 'buy', price: { amount: 49, currency: 'EUR', onRequest: false } }));

            expect(action(wrapper).text()).toBe('Buy (49€)');
            expect(action(wrapper).classes()).toContain('btn-primary');
            await action(wrapper).trigger('click');
            expect(wrapper.emitted('buy')).toHaveLength(1);
        });

        it('links Professional Edition modules to the edition', () => {
            const link = action(mountCard(marketplaceModule({ availability: 'professionalEdition' })));

            expect(link.element.tagName).toBe('A');
            expect(link.text()).toBe('Learn more');
            expect(link.attributes('href')).toBe('/pe');
            expect(link.classes()).toContain('btn-primary');
        });

        it('says "Installed" for an installed module, without anything to do', () => {
            const button = action(mountCard(marketplaceModule({ installedVersion: '2.0.0', isEnabled: false, configUrl: '/config' })));

            expect(button.text()).toBe('Installed');
            expect(button.classes()).toContain('btn-secondary');
            expect(button.attributes('disabled')).toBeDefined();
        });

        it('says "Not compatible" without a compatible version', () => {
            const button = action(mountCard(marketplaceModule({ availability: 'incompatible', latestCompatibleVersion: null })));

            expect(button.text()).toBe('Not compatible');
            expect(button.classes()).toContain('btn-secondary');
            expect(button.attributes('disabled')).toBeDefined();
        });

        it('offers an available update and links its changelog', async () => {
            const wrapper = mountCard(outdated());

            expect(action(wrapper).text()).toBe('Update');
            expect(action(wrapper).classes()).toContain('btn-accent');
            await action(wrapper).trigger('click');
            expect(wrapper.emitted('update')).toHaveLength(1);

            expect(secondary(wrapper).attributes('href')).toBe('https://marketplace.humhub.com/module/calendar-x/changelog');
            expect(secondary(wrapper).attributes('aria-label')).toBe('Module changelog');
            expect(secondary(wrapper).find('.ti-file-text').exists()).toBe(true);
        });

        it('disables installing and buying while another action runs', () => {
            expect(action(mountCard(marketplaceModule(), { locked: true })).attributes('disabled')).toBeDefined();
            expect(action(mountCard(marketplaceModule({ availability: 'buy' }), { locked: true })).attributes('disabled')).toBeDefined();
            expect(action(mountCard(outdated(), { locked: true })).attributes('disabled')).toBeDefined();
        });
    });

    describe('the update states', () => {
        it('is queued while "Update all" has not reached it yet', () => {
            const button = action(mountCard(outdated(), { updateState: 'pending' }));

            expect(button.text()).toBe('Queued');
            expect(button.attributes('disabled')).toBeDefined();
        });

        it('shows a running update on the card and its button', () => {
            const wrapper = mountCard(outdated(), { updateState: 'running' });

            expect(wrapper.classes()).toContain('is-updating');
            expect(action(wrapper).text()).toBe('Updating');
            expect(action(wrapper).classes()).toEqual(expect.arrayContaining(['btn-warning', 'is-updating']));
            expect(action(wrapper).find('.ti-refresh.c-module-card__spin').exists()).toBe(true);
            expect(action(wrapper).attributes('disabled')).toBeDefined();
        });

        it('leaves announcing the update to the browser (no live region per button)', () => {
            expect(mountCard(outdated(), { updateState: 'running' }).find('[role="status"]').exists()).toBe(false);
            expect(ModuleCard.props.busy).toBeUndefined();
        });

        it('says "Updated!", then plays the remove animation, then fades in', async () => {
            const wrapper = mountCard(outdated(), { updateState: 'success' });

            expect(action(wrapper).text()).toBe('Updated!');
            expect(wrapper.classes()).toContain('is-updating');

            await wrapper.setProps({ updateState: 'removing' });
            expect(wrapper.classes()).toContain('is-removing');
            expect(action(wrapper).text()).toBe('Updated!');

            await wrapper.setProps({ module: marketplaceModule({ installedVersion: '2.0.0' }), updateState: 'done' });
            expect(wrapper.classes()).toEqual(expect.arrayContaining(['is-entering']));
            expect(wrapper.classes()).not.toContain('is-updating');
            expect(action(wrapper).text()).toBe('Installed');
            expect(wrapper.find('button.c-module-card__badge--official').exists()).toBe(true);
            expect(wrapper.find('.c-module-card__version').text()).toBe('2.0.0');
        });

        it('shows an error', () => {
            expect(mountCard(marketplaceModule(), { error: 'Download failed' }).find('[role="alert"]').text()).toBe('Download failed');
        });

        it('falls back to a generic message when an update failed without an error text', () => {
            const wrapper = mountCard(outdated(), { updateState: 'failed' });

            expect(wrapper.find('[role="alert"]').text()).toBe('Update failed');
            expect(action(wrapper).text()).toBe('Update');
            expect(action(wrapper).attributes('disabled')).toBeUndefined();
        });
    });

    describe('the search', () => {
        it('marks the matches in the name and the description, accent- and case-insensitively', () => {
            const wrapper = mountCard(marketplaceModule({ name: 'Meetings', description: 'Plan meetings, meet up. Méet!' }), { query: 'meet' });

            expect(wrapper.find('.c-module-card__title').html()).toContain('<mark class="c-module-card__mark">Meet</mark>ings');
            expect(wrapper.findAll('.c-module-card__text mark').map((mark) => mark.text())).toEqual(['meet', 'meet', 'Méet']);
            expect(wrapper.find('.c-module-card__text').text()).toBe('Plan meetings, meet up. Méet!');
        });

        it('marks nothing without a query or a match', () => {
            expect(mountCard(marketplaceModule()).find('mark').exists()).toBe(false);
            expect(mountCard(marketplaceModule(), { query: 'wiki' }).find('mark').exists()).toBe(false);
            expect(mountCard(marketplaceModule(), { query: 'wiki' }).find('.c-module-card__title').text()).toBe('Calendar X');
        });

        it('renders the name, the description and the query as text', () => {
            const wrapper = mountCard(marketplaceModule({ name: '<b>Bold</b> & <i>co</i>', description: '<img src=x onerror=alert(1)>' }), { query: '<b>' });

            expect(wrapper.find('.c-module-card__title b').exists()).toBe(false);
            expect(wrapper.find('.c-module-card__title mark').text()).toBe('<b>');
            expect(wrapper.find('.c-module-card__title').text()).toBe('<b>Bold</b> & <i>co</i>');
            expect(wrapper.find('.c-module-card__text img').exists()).toBe(false);
            expect(wrapper.find('.c-module-card__text').text()).toBe('<img src=x onerror=alert(1)>');
        });
    });
});
