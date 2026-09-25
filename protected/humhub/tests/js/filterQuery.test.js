import { describe, expect, it } from 'vitest';
import {
    defaultValues,
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

    it('knows whether a value is the default', () => {
        expect(isDefault(filters[2], [])).toBe(true);
        expect(isDefault(filters[2], ['update'])).toBe(false);
        expect(isDefault(filters[3], 'name')).toBe(true);
    });
});
