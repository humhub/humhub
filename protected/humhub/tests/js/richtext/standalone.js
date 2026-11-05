// The editor's tests run it standalone, as outside HumHub (`humhub-bridge.js`'s
// `isHumhub()`): drop the HumHub stubs of the global test setup before the editor loads.
globalThis.humhub = {};

// What jsdom leaves out and the editor (ProseMirror's view) or a test uses
if (!Range.prototype.getClientRects) {
    const emptyRect = () => ({ x: 0, y: 0, top: 0, right: 0, bottom: 0, left: 0, width: 0, height: 0 });
    Range.prototype.getClientRects = () => [];
    Range.prototype.getBoundingClientRect = emptyRect;
}

if (typeof globalThis.DataTransfer === 'undefined') {
    globalThis.DataTransfer = class {
        constructor() {
            this.data = {};
            this.files = [];
        }
        get types() {
            return Object.keys(this.data);
        }
        setData(type, value) {
            this.data[type] = String(value);
        }
        getData(type) {
            return this.data[type] || '';
        }
    };
}

if (typeof globalThis.ClipboardEvent === 'undefined') {
    globalThis.ClipboardEvent = class extends Event {
        constructor(type, init = {}) {
            super(type, init);
            this.clipboardData = init.clipboardData || null;
        }
    };
}
