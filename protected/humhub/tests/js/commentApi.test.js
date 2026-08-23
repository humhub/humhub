import { beforeEach, describe, expect, it, vi } from 'vitest';
import {
    collectRecordIds, createComment, deleteComment, extractFieldErrors, fetchComment,
    fetchCommentPermissions, fetchLikeStates, fetchWindow, isAdminDelete, mapComment, updateComment,
} from '../../modules/comment/vue/components/commentApi.js';

await import('../../resources/js/humhub/humhub.vue.js');

// The wire fixtures below are literal API payloads (camelCase fields, ISO-8601
// timestamps — see docs/develop/concept-api.md and the PHP-side CommentSerializer);
// mapComment() only adds what a client is supposed to derive itself.
const wireAuthor = (overrides = {}) => ({
    id: 9,
    guid: 'g1',
    displayName: 'Alice',
    url: '/u/alice',
    imageUrl: '/img/alice.jpg',
    contentContainerId: 5,
    ...overrides,
});

const wireComment = (overrides = {}) => ({
    id: 1,
    message: 'Hello',
    messageRenderOptions: { 'ui-richtext': true },
    contentId: 42,
    parentCommentId: null,
    recordId: 100,
    createdBy: wireAuthor(),
    createdAt: '2026-08-01T10:00:00+00:00',
    updatedAt: '2026-08-01T10:00:00+00:00',
    url: '/comment/perma?id=1',
    files: [],
    childCount: 0,
    replies: { total: 0, items: [], hasMore: false },
    extensions: {},
    ...overrides,
});

describe('commentApi', () => {
    beforeEach(() => {
        globalThis.humhub.config.module('user').id = 9;
        globalThis.humhub.config.module('user').blockedUserIds = [];
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({}));
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(wireComment()));
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ code: 200 }));
    });

    describe('mapComment', () => {
        it('passes the payload through and parses its timestamps', () => {
            const mapped = mapComment(wireComment());

            expect(mapped.author.displayName).toBe('Alice');
            expect(mapped.author.imageUrl).toBe('/img/alice.jpg');
            expect(mapped.url).toBe('/comment/perma?id=1');
            expect(mapped.createdAt).toBeInstanceOf(Date);
            // ISO-8601 with offset — no timezone knowledge needed on the client
            expect(mapped.createdAt.toISOString()).toBe('2026-08-01T10:00:00.000Z');
        });

        it('derives isEdited from updatedAt !== createdAt, exactly like Comment::isUpdated()', () => {
            expect(mapComment(wireComment()).isEdited).toBe(false);
            expect(mapComment(wireComment({ updatedAt: '2026-08-01T10:00:01+00:00' })).isEdited).toBe(true);
            expect(mapComment(wireComment({ updatedAt: null })).isEdited).toBe(false);
        });

        it('reads the same instant written in another offset as unedited', () => {
            expect(mapComment(wireComment({ updatedAt: '2026-08-01T12:00:00+02:00' })).isEdited).toBe(false);
        });

        it('derives blocked from the viewer\'s own block list', () => {
            globalThis.humhub.config.module('user').blockedUserIds = [66];
            expect(mapComment(wireComment()).blocked).toBe(false);
            expect(mapComment(wireComment({ createdBy: wireAuthor({ id: 66 }) })).blocked).toBe(true);
        });

        it('maps the reply preview recursively and keeps a missing preview null', () => {
            const reply = wireComment({ id: 2, parentCommentId: 1, replies: null });
            const mapped = mapComment(wireComment({ replies: { total: 1, items: [reply], hasMore: true } }));

            expect(mapped.replies.total).toBe(1);
            expect(mapped.replies.hasMore).toBe(true);
            expect(mapped.replies.items[0].author.displayName).toBe('Alice');
            expect(mapped.replies.items[0].createdAt).toBeInstanceOf(Date);
            expect(mapped.replies.items[0].replies).toBeNull();
        });

        it('tolerates a minimal payload (missing optional fields)', () => {
            const mapped = mapComment({ id: 5, message: 'raw' });
            expect(mapped.message).toBe('raw');
            expect(mapped.author).toBeNull();
            expect(mapped.blocked).toBe(false);
            expect(mapped.files).toEqual([]);
            expect(mapped.replies).toBeNull();
        });

        it('carries no caller-context fields — the payload is cacheable, they are fetched', () => {
            const mapped = mapComment(wireComment());

            expect(mapped.canEdit).toBeUndefined();
            expect(mapped.canDelete).toBeUndefined();
            expect(mapped.likes).toBeUndefined();
            expect(mapped.author.online).toBeUndefined();
        });
    });

    describe('isAdminDelete', () => {
        it('is moderation only on someone ELSE\'s comment', () => {
            const own = mapComment(wireComment());
            const foreign = mapComment(wireComment({ createdBy: wireAuthor({ id: 66 }) }));

            // own comment (author 9 === current user 9)
            expect(isAdminDelete(own, true)).toBe(false);
            // foreign comment, deletable → moderation
            expect(isAdminDelete(foreign, true)).toBe(true);
            // foreign comment, not deletable
            expect(isAdminDelete(foreign, false)).toBe(false);
        });

        it('does not report it without a known current user id', () => {
            const foreign = mapComment(wireComment({ createdBy: wireAuthor({ id: 66 }) }));
            delete globalThis.humhub.config.module('user').id;

            expect(isAdminDelete(foreign, true)).toBe(false);
        });
    });

    describe('caller-context endpoints', () => {
        it('fetchCommentPermissions targets the per-comment permissions route', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ canEdit: true, canDelete: false }));

            const permissions = await fetchCommentPermissions(7);

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/comment/7/permissions');
            expect(permissions).toEqual({ canEdit: true, canDelete: false });
        });

        it('fetchLikeStates asks for all record ids in ONE request and unwraps the map', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({
                results: { 100: { total: 2, liked: true, canLike: true } },
            }));

            const states = await fetchLikeStates([100, 101]);

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/like/states?recordIds=100%2C101');
            expect(states).toEqual({ 100: { total: 2, liked: true, canLike: true } });
        });

        it('fetchLikeStates makes no request for an empty id list', async () => {
            globalThis.humhubStubs.client.get = vi.fn();

            expect(await fetchLikeStates([])).toEqual({});
            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
        });

        it('collectRecordIds covers reply previews and dedupes', () => {
            const reply = wireComment({ id: 2, recordId: 101, replies: null });
            const root = mapComment(wireComment({ replies: { total: 1, items: [reply], hasMore: false } }));

            expect(collectRecordIds([root, root])).toEqual([100, 101]);
        });
    });

    describe('endpoints', () => {
        it('fetchWindow targets the content window and maps its results', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({
                results: [wireComment()], prevCount: 1, nextCount: 2, total: 5, rootTotal: 4,
            }));

            const window = await fetchWindow({ contentId: 42, pageSize: 3, direction: 'previous', commentId: 7 });

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith(
                '/api/v2/comment/content/42/window?pageSize=3&direction=previous&commentId=7',
            );
            expect(window.results[0].author.displayName).toBe('Alice');
            expect(window.results[0].createdAt).toBeInstanceOf(Date);
            expect(window).toMatchObject({ prevCount: 1, nextCount: 2, total: 5, rootTotal: 4 });
        });

        it('fetchWindow targets the parent window for replies', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: [] }));

            await fetchWindow({ contentId: 42, parentCommentId: 4, pageSize: 3 });

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/comment/parent/4/window?pageSize=3');
        });

        it('fetchComment targets the single view', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(wireComment({ id: 7 })));

            const comment = await fetchComment(7);

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/comment/7');
            expect(comment.id).toBe(7);
        });

        it('createComment POSTs with content/parent params and maps the response', async () => {
            await createComment({ contentId: 42, parentCommentId: 4, message: 'm', fileList: ['g'] });

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith(
                '/api/v2/comment?contentId=42&parentCommentId=4',
                { data: { message: 'm', fileList: ['g'] } },
            );
        });

        it('updateComment PUTs through the verb bridge', async () => {
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve(wireComment({ id: 7, message: 'edited' })));

            const comment = await updateComment(7, { message: 'edited', fileList: [] });

            expect(globalThis.humhubStubs.client.ajax).toHaveBeenCalledWith(
                '/api/v2/comment/7',
                expect.objectContaining({ method: 'PUT', data: { message: 'edited', fileList: [] } }),
            );
            expect(comment.message).toBe('edited');
        });

        it('deleteComment DELETEs, with optional moderation fields', async () => {
            await deleteComment(7);
            expect(globalThis.humhubStubs.client.ajax).toHaveBeenCalledWith(
                '/api/v2/comment/7',
                expect.objectContaining({ method: 'DELETE' }),
            );

            await deleteComment(7, { notify: '1', message: 'reason' });
            expect(globalThis.humhubStubs.client.ajax).toHaveBeenLastCalledWith(
                '/api/v2/comment/7',
                expect.objectContaining({ method: 'DELETE', data: { notify: '1', message: 'reason' } }),
            );
        });
    });

    describe('extractFieldErrors', () => {
        it('unwraps the 422 validation envelope', () => {
            expect(extractFieldErrors({ status: 422, errors: { message: ['blank'] } })).toEqual({ message: ['blank'] });
        });

        it('re-keys parentCommentId errors onto message for the single-field form', () => {
            expect(extractFieldErrors({ errors: { parentCommentId: ['nested too deep'] } }))
                .toEqual({ parentCommentId: ['nested too deep'], message: ['nested too deep'] });
        });

        it('returns null for non-validation shapes', () => {
            expect(extractFieldErrors({ status: 403, name: 'Forbidden' })).toBeNull();
            expect(extractFieldErrors(null)).toBeNull();
        });
    });
});
