import { describe, expect, it } from 'vitest';
import {
    defaultValues,
    fixedSignature,
    isDefault,
    readValues,
    requestParams,
    writeQuery,
} from '../../vue/filter/filterQuery.js';

const filters = [
    { key: 'q', type: 'text' },
    { key: 'categoryId', type: 'select' },
    { key: 'status', type: 'tags', multiple: true },
    { key: 'sort', type: 'tags', default: 'name' },
    { key: 'mine', type: 'checkbox' },
    { key: 'id', type: 'text', hidden: true },
];

describe('filterQuery', () => {
    it('derives defaults per type', () => {
        expect(defaultValues(filters)).toEqual({
            q: '', categoryId: '', status: [], sort: 'name', mine: false, id: '',
        });
    });

    it('reads values from a query string and keeps defaults for the rest', () => {
        expect(readValues(filters, '?q=cal&status=update,installed&mine=1&r=marketplace%2Fbrowse')).toEqual({
            q: 'cal', categoryId: '', status: ['update', 'installed'], sort: 'name', mine: true, id: '',
        });
    });

    it('reads values over a given base instead of the defaults', () => {
        const base = { q: 'kept', categoryId: '3', status: ['a'], sort: 'name', mine: true, id: '' };
        expect(readValues(filters, '?q=cal', base)).toEqual({ ...base, q: 'cal' });
        expect(base.q).toBe('kept');
    });

    it('writes non-default values and keeps foreign parameters', () => {
        const values = { ...defaultValues(filters), q: 'cal', status: ['update'] };
        expect(writeQuery(filters, values, '?r=marketplace%2Fbrowse&q=old&sort=name'))
            .toBe('?r=marketplace%2Fbrowse&q=cal&status=update');
        expect(writeQuery(filters, defaultValues(filters), '?q=old')).toBe('');
    });

    it('builds request parameters without empty values', () => {
        const values = { ...defaultValues(filters), q: 'cal', status: ['update', 'installed'] };
        expect(requestParams(filters, values)).toEqual({ q: 'cal', status: 'update,installed', sort: 'name', mine: '0' });
    });

    it('sends a select value as it is, ignoring anything else an option carries', () => {
        const status = [{
            key: 'scope',
            type: 'select',
            options: [
                { value: 'member', label: 'Member' },
                { value: 'archived', label: 'Archived', params: { archived: 1 } },
            ],
        }];

        expect(requestParams(status, { scope: 'archived' })).toEqual({ scope: 'archived' });
        expect(requestParams(status, { scope: '' })).toEqual({});
    });

    it('merges fixed values into the request parameters, overriding a filter of the same key', () => {
        const values = { ...defaultValues(filters), q: 'cal', categoryId: '3' };

        expect(requestParams(filters, values, { categoryId: 5, spaceId: 7, ids: [1, 2], mine: true })).toEqual({
            q: 'cal', categoryId: '5', sort: 'name', mine: '1', spaceId: '7', ids: '1,2',
        });
    });

    it('treats a registered filter type as a string, or with multiple as a list of strings', () => {
        const custom = [
            { key: 'status', type: 'tasks.status' },
            { key: 'assigned', type: 'user', multiple: true, default: '3' },
        ];

        expect(defaultValues(custom)).toEqual({ status: '', assigned: ['3'] });
        expect(readValues(custom, '?status=open&assigned=1,2')).toEqual({ status: 'open', assigned: ['1', '2'] });
        expect(requestParams(custom, { status: 'open', assigned: ['1', '2'] })).toEqual({ status: 'open', assigned: '1,2' });
    });

    it('compares fixed objects by what they send, not by identity or key order', () => {
        expect(fixedSignature({ spaceId: 5, ids: [1, 2] })).toBe(fixedSignature({ ids: ['1', '2'], spaceId: '5' }));
        expect(fixedSignature({ spaceId: 5 })).not.toBe(fixedSignature({ spaceId: 6 }));
        expect(fixedSignature({ spaceId: 5 })).not.toBe(fixedSignature({}));
        expect(fixedSignature(null)).toBe(fixedSignature({}));
    });

    it('knows whether a value is the default', () => {
        expect(isDefault(filters[2], [])).toBe(true);
        expect(isDefault(filters[2], ['update'])).toBe(false);
        expect(isDefault(filters[3], 'name')).toBe(true);
    });
});
