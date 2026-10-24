import { describe, expect, it } from 'vitest';

describe('JS test harness', () => {
    it('provides the global stubs', () => {
        expect(globalThis.jQuery).toBeTypeOf('function');
        expect(globalThis.Vue.createApp).toBeTypeOf('function');
        expect(globalThis.humhub.module).toBeTypeOf('function');
    });

    it('registers stub modules through humhub.module()', () => {
        humhub.module('harnessProbe', (module, require, $) => {
            module.export({ ping: () => 'pong' });
            module.init = () => {
                module.initialized = true;
            };
        });
        expect(humhub.modules.harnessProbe.ping()).toBe('pong');
        expect(humhub.modules.harnessProbe.initialized).toBe(true);
    });
});
