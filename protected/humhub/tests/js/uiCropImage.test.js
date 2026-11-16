import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

await import('../../resources/js/humhub/humhub.ui.cropImage.js');
const cropImage = globalThis.humhub.modules['ui.cropImage'];

/**
 * Stands in for Cropper.js: jsdom has no layout, so the real elements can't size or move anything.
 * The fake inserts the template after the image like the real `Cropper` class does and gives the
 * selection the part of its API the module uses — `$change()` emits a cancelable `change` that
 * bubbles to the canvas and applies the values unless prevented.
 */
class FakeCropper {
    constructor(element, options) {
        element.style.display = 'none';
        element.after(document.createRange().createContextualFragment(options.template));
        this.container = element.parentElement;

        const selection = this.getCropperSelection();
        Object.assign(selection, { x: 0, y: 0, width: 0, height: 0 });
        selection.$change = function (x, y, width = this.width, height = this.height) {
            const event = new CustomEvent('change', {
                bubbles: true,
                cancelable: true,
                detail: { x, y, width, height },
            });
            if (this.dispatchEvent(event)) {
                Object.assign(this, { x, y, width, height });
            }
            return this;
        };
        selection.$move = function (x, y) {
            return this.$change(this.x + x, this.y + y);
        };
    }

    getCropperCanvas() {
        return this.container.querySelector('cropper-canvas');
    }

    getCropperImage() {
        return this.container.querySelector('cropper-image');
    }

    getCropperSelection() {
        return this.container.querySelector('cropper-selection');
    }
}

const addImage = () => {
    const box = document.createElement('div');
    const image = document.createElement('img');
    image.id = 'crop';
    box.appendChild(image);
    document.body.appendChild(box);
    return image;
};

const loadedImage = (width, height) => {
    const image = addImage();
    Object.defineProperty(image, 'complete', { value: true });
    Object.defineProperty(image, 'naturalWidth', { value: width });
    Object.defineProperty(image, 'naturalHeight', { value: height });
    return image;
};

const key = (element, key, options = {}) => {
    const event = new KeyboardEvent('keydown', { key, bubbles: true, cancelable: true, ...options });
    element.dispatchEvent(event);
    return event;
};

describe('ui.cropImage', () => {
    describe('displayScale()', () => {
        it('shrinks the image into the box', () => {
            expect(cropImage.displayScale(2000, 1000, 440, 500)).toBe(0.22);
            expect(cropImage.displayScale(1000, 3000, 440, 600)).toBe(0.2);
        });

        it('never enlarges a small image', () => {
            expect(cropImage.displayScale(300, 200, 440, 500)).toBe(1);
        });

        it('ignores a missing box size', () => {
            expect(cropImage.displayScale(2000, 1000)).toBe(1);
            expect(cropImage.displayScale(2000, 1000, 500)).toBe(0.25);
        });
    });

    describe('initialSelection()', () => {
        it('takes the given rectangle when it fits the image and the ratio', () => {
            expect(cropImage.initialSelection([0, 0, 1134, 192], 1134 / 192, 2000, 1000))
                .toEqual({ x: 0, y: 0, width: 1134, height: 192 });
        });

        it('keeps the rectangle inside the image', () => {
            expect(cropImage.initialSelection([10, 10, 500, 40], 0, 100, 100))
                .toEqual({ x: 10, y: 10, width: 90, height: 30 });
        });

        it('fits the aspect ratio into the rectangle, centered', () => {
            expect(cropImage.initialSelection([0, 0, 150, 150], 1, 100, 80))
                .toEqual({ x: 10, y: 0, width: 80, height: 80 });
        });

        it('takes the whole image without a usable rectangle', () => {
            expect(cropImage.initialSelection(undefined, 0, 100, 80)).toEqual({ x: 0, y: 0, width: 100, height: 80 });
            expect(cropImage.initialSelection([200, 200, 300, 300], 0, 100, 80)).toEqual({ x: 0, y: 0, width: 100, height: 80 });
        });
    });

    describe('imageCoords()', () => {
        it('converts a selection on the shown image into pixels of the image', () => {
            expect(cropImage.imageCoords({ x: 22, y: 11, width: 44, height: 22 }, 0.22, 2000, 1000))
                .toEqual({ x: 100, y: 50, w: 200, h: 100, x2: 300, y2: 150 });
        });

        it('stays inside the image', () => {
            expect(cropImage.imageCoords({ x: 50, y: 0, width: 51, height: 10 }, 0.5, 200, 100))
                .toEqual({ x: 100, y: 0, w: 100, h: 20, x2: 200, y2: 20 });
        });
    });

    describe('create()', () => {
        let onChange;

        const create = (options = {}) => cropImage.create('#crop', {
            aspectRatio: 1,
            minSize: [50, 50],
            setSelect: [0, 0, 1000, 1000],
            boxWidth: 500,
            boxHeight: 500,
            onChange,
            label: 'Image section',
            describedBy: 'crop-hint',
            ...options,
        });

        beforeEach(() => {
            globalThis.Cropper = { Cropper: FakeCropper };
            onChange = vi.fn();
        });

        afterEach(() => {
            delete globalThis.Cropper;
            document.body.replaceChildren();
        });

        it('shows the image in the size of the box', async () => {
            loadedImage(2000, 1000);
            await create();

            const canvas = document.querySelector('cropper-canvas');
            expect(canvas.style.width).toBe('500px');
            expect(canvas.style.height).toBe('250px');
        });

        it('starts with the given section and reports it in pixels of the image', async () => {
            loadedImage(2000, 1000);
            await create();

            const selection = document.querySelector('cropper-selection');
            expect(selection.getAttribute('x')).toBe('0');
            expect(selection.getAttribute('y')).toBe('0');
            expect(selection.getAttribute('width')).toBe('250');
            expect(selection.getAttribute('height')).toBe('250');
            expect(selection.getAttribute('aspect-ratio')).toBe('1');
            expect(onChange).toHaveBeenLastCalledWith({ x: 0, y: 0, w: 1000, h: 1000, x2: 1000, y2: 1000 });
        });

        it('reports every change in pixels of the image', async () => {
            loadedImage(2000, 1000);
            await create();

            document.querySelector('cropper-selection').$change(25, 10, 100, 100);

            expect(onChange).toHaveBeenLastCalledWith({ x: 100, y: 40, w: 400, h: 400, x2: 500, y2: 440 });
        });

        it('rejects a section smaller than the minimum size', async () => {
            loadedImage(2000, 1000);
            await create();
            onChange.mockClear();

            const selection = document.querySelector('cropper-selection');
            selection.$change(0, 0, 10, 10);

            expect(onChange).not.toHaveBeenCalled();
            expect(selection.width).not.toBe(10);
        });

        it('makes the section a named control described by the hint', async () => {
            loadedImage(2000, 1000);
            await create();

            const selection = document.querySelector('cropper-selection');
            expect(selection.getAttribute('tabindex')).toBe('0');
            expect(selection.getAttribute('role')).toBe('application');
            expect(selection.getAttribute('aria-label')).toBe('Image section');
            expect(selection.getAttribute('aria-describedby')).toBe('crop-hint');
            expect(document.querySelector('cropper-grid').getAttribute('aria-hidden')).toBe('true');
        });

        it('moves the section with the arrow keys, ten steps with Shift', async () => {
            loadedImage(2000, 1000);
            await create();
            const selection = document.querySelector('cropper-selection');
            selection.$change(100, 100, 100, 100);

            const event = key(selection, 'ArrowRight');
            key(selection, 'ArrowDown', { shiftKey: true });

            expect(event.defaultPrevented).toBe(true);
            expect([selection.x, selection.y]).toEqual([101, 110]);
        });

        it('changes the size with + and -, around the center', async () => {
            loadedImage(2000, 1000);
            await create();
            const selection = document.querySelector('cropper-selection');
            selection.$change(100, 100, 100, 100);

            key(selection, '+');
            expect(selection.x).toBeCloseTo(95);
            expect(selection.y).toBeCloseTo(95);
            expect(selection.width).toBeCloseTo(110);
            expect(selection.height).toBeCloseTo(110);

            key(selection, '-');
            expect(selection.width).toBeCloseTo(100);
        });

        it('stops a drag at the edge of the image', async () => {
            loadedImage(2000, 1000);
            await create();
            const selection = document.querySelector('cropper-selection');
            selection.$change(20, 10, 100, 100);

            selection.$change(-30, 10);
            expect([selection.x, selection.y]).toEqual([0, 10]);

            selection.$change(450, 200);
            expect([selection.x, selection.y]).toEqual([400, 150]);
        });

        it('refuses a size beyond the edge of the image', async () => {
            loadedImage(2000, 1000);
            await create();
            const selection = document.querySelector('cropper-selection');
            selection.$change(400, 0, 100, 100);

            selection.$change(400, 0, 120, 120);

            expect([selection.x, selection.width]).toEqual([400, 100]);
        });

        it('stops at the edge of the image instead of refusing the step', async () => {
            loadedImage(2000, 1000);
            await create();
            const selection = document.querySelector('cropper-selection');
            selection.$change(5, 145, 100, 100);

            key(selection, 'ArrowLeft', { shiftKey: true });
            key(selection, 'ArrowDown', { shiftKey: true });
            expect([selection.x, selection.y]).toEqual([0, 150]);

            selection.$change(400, 0, 100, 100);
            key(selection, '+');
            expect([selection.x, selection.y, Math.round(selection.width)]).toEqual([390, 0, 110]);
        });

        it('grows with + up to the size of the image', async () => {
            loadedImage(2000, 1000);
            await create();
            const selection = document.querySelector('cropper-selection');
            selection.$change(0, 0, 240, 240);

            key(selection, '+');

            expect([selection.x, selection.y, selection.width, selection.height]).toEqual([0, 0, 250, 250]);
        });

        it('leaves other keys alone', async () => {
            loadedImage(2000, 1000);
            await create();

            expect(key(document.querySelector('cropper-selection'), 'Tab').defaultPrevented).toBe(false);
        });

        it('has no keyboard control when keySupport is off', async () => {
            loadedImage(2000, 1000);
            await create({ keySupport: false });
            const selection = document.querySelector('cropper-selection');
            selection.$change(100, 100, 100, 100);

            key(selection, 'ArrowRight');

            expect(selection.hasAttribute('tabindex')).toBe(false);
            expect(selection.x).toBe(100);
        });

        it('dims the rest of the image with the given colour', async () => {
            loadedImage(2000, 1000);
            await create({ bgColor: 'black', bgOpacity: '0.5' });

            expect(document.querySelector('cropper-shade').getAttribute('theme-color'))
                .toBe('color-mix(in srgb, black 50%, transparent)');
        });

        it('waits for the image to load', async () => {
            const image = addImage();
            const ready = create();

            expect(document.querySelector('cropper-canvas')).toBeNull();

            Object.defineProperty(image, 'naturalWidth', { value: 400 });
            Object.defineProperty(image, 'naturalHeight', { value: 400 });
            image.dispatchEvent(new Event('load'));
            await ready;

            expect(document.querySelector('cropper-canvas')).not.toBeNull();
        });
    });
});
