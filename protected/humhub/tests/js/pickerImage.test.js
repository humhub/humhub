import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import PickerImage from '../../vue/picker/PickerImage.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

const TestUserPic = {
    name: 'TestUserPic',
    props: { label: String, px: Number },
    render() {
        return globalThis.Vue.h('b', { class: 'test-user-pic', 'data-px': this.px }, this.label);
    },
};

describe('PickerImage', () => {
    it('renders an icon', () => {
        const wrapper = mount(PickerImage, { props: { image: { type: 'icon', name: 'star' }, size: 24 } });
        expect(wrapper.find('i.ti.ti-star').exists()).toBe(true);
        expect(wrapper.find('i').attributes('aria-hidden')).toBe('true');
    });

    it('renders a Font Awesome compatibility icon', () => {
        const wrapper = mount(PickerImage, { props: { image: { type: 'icon', name: 'fa-cog' }, size: 24 } });
        expect(wrapper.find('i.fa.fa-cog').exists()).toBe(true);
    });

    it('renders a colour dot', () => {
        const wrapper = mount(PickerImage, { props: { image: { type: 'color', color: '#ff0000' }, size: 24 } });
        expect(wrapper.find('.picker-color').attributes('style')).toContain('background-color: rgb(255, 0, 0)');
    });

    it('renders a url image at its size', () => {
        const wrapper = mount(PickerImage, { props: { image: { type: 'url', url: '/a.jpg' }, size: 32 } });
        const img = wrapper.find('img');
        expect(img.attributes('src')).toBe('/a.jpg');
        expect(img.attributes('width')).toBe('32');
        expect(img.attributes('alt')).toBe('');
    });

    it('renders a registered type through its component and props mapper', () => {
        vueModule.registerPickerImage('test.pic', 'TestUserPic', (image, size) => ({ label: image.name, px: size }));
        const wrapper = mount(PickerImage, {
            props: { image: { type: 'test.pic', name: 'Anna' }, size: 24 },
            global: { components: { TestUserPic } },
        });
        expect(wrapper.find('.test-user-pic').text()).toBe('Anna');
        expect(wrapper.find('.test-user-pic').attributes('data-px')).toBe('24');
    });

    it('renders nothing for no image or an unregistered type', () => {
        expect(mount(PickerImage, { props: { image: null, size: 24 } }).html()).toBe('<!--v-if-->');
        expect(mount(PickerImage, { props: { image: { type: 'test.none' }, size: 24 } }).html()).toBe('<!--v-if-->');
    });
});
