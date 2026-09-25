import { describe, expect, it, vi } from 'vitest';
import { createUpdateQueue } from '../../modules/marketplace/vue/components/updateQueue.js';
import { errorMessage } from '../../modules/marketplace/vue/components/marketplaceApi.js';

describe('updateQueue', () => {
    it('updates one module after the other', async () => {
        const order = [];
        const update = vi.fn(async (id) => { order.push(`start:${id}`); await Promise.resolve(); order.push(`end:${id}`); });

        const result = await createUpdateQueue(['a', 'b'], update).run();

        expect(order).toEqual(['start:a', 'end:a', 'start:b', 'end:b']);
        expect(result).toEqual({ stopped: false, failed: [] });
    });

    it('continues after a failure and reports it', async () => {
        const update = vi.fn((id) => (id === 'a' ? Promise.reject(new Error('no')) : Promise.resolve()));

        const result = await createUpdateQueue(['a', 'b'], update).run();

        expect(update).toHaveBeenCalledTimes(2);
        expect(result.failed).toEqual(['a']);
    });

    it('finishes the running update and skips the rest when stopped', async () => {
        let queue;
        const update = vi.fn(async () => { queue.stop(); });
        queue = createUpdateQueue(['a', 'b', 'c'], update);

        const result = await queue.run();

        expect(update).toHaveBeenCalledTimes(1);
        expect(result.stopped).toBe(true);
    });
});

describe('marketplaceApi helpers', () => {
    it('takes the first validation message, then the error message, then the fallback', () => {
        expect(errorMessage({ errors: { id: ['Not installed.'] } }, 'x')).toBe('Not installed.');
        expect(errorMessage({ message: 'Down' }, 'x')).toBe('Down');
        expect(errorMessage(undefined, 'x')).toBe('x');
    });
});
