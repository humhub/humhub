#!/usr/bin/env python3
"""Compares the screenshots of the CSS visual comparison (VisualCest) of two runs.

    visual-diff.py <base-dir> <changed-dir> <output-dir> [<title>]

For every screenshot that differs, <output-dir>/<name>.png shows the base, the changed version and
the changed pixels (red) side by side. A Markdown report goes to stdout and, in GitHub Actions, to
the job summary. Exit code 0, also when screenshots differ; 1 if the runs could not be compared.
"""

import os
import sys

from PIL import Image, ImageChops

# A pixel counts as changed above this difference of one of its channels (anti-aliasing noise)
THRESHOLD = 16


def compare(base_file, changed_file, output_file):
    base = Image.open(base_file).convert('RGB')
    changed = Image.open(changed_file).convert('RGB')

    width, height = max(base.width, changed.width), max(base.height, changed.height)
    padded = []
    for image in (base, changed):
        canvas = Image.new('RGB', (width, height), (255, 0, 255))
        canvas.paste(image, (0, 0))
        padded.append(canvas)

    mask = ImageChops.difference(*padded).convert('L').point(lambda value: 255 if value > THRESHOLD else 0)
    changed_pixels = mask.histogram()[255]
    if changed_pixels == 0 and base.size == changed.size:
        return 0, None

    highlight = Image.composite(Image.new('RGB', (width, height), (255, 0, 0)),
                                Image.blend(padded[1], Image.new('RGB', (width, height), (255, 255, 255)), 0.7),
                                mask)
    gap = 10
    sheet = Image.new('RGB', (width * 3 + gap * 2, height), (40, 40, 40))
    for index, image in enumerate((padded[0], padded[1], highlight)):
        sheet.paste(image, (index * (width + gap), 0))
    sheet.save(output_file, optimize=True)

    return changed_pixels, mask.getbbox()


def main():
    if len(sys.argv) not in (4, 5):
        print(__doc__)
        return 1

    base_dir, changed_dir, output_dir = sys.argv[1:4]
    title = 'CSS visual comparison' + (f' ({sys.argv[4]})' if len(sys.argv) == 5 else '')
    os.makedirs(output_dir, exist_ok=True)

    names = sorted({name for directory in (base_dir, changed_dir) if os.path.isdir(directory)
                    for name in os.listdir(directory) if name.endswith('.png')})
    if not names:
        print('No screenshots found.')
        return 1

    rows, changed = [], 0
    for name in names:
        base_file, changed_file = os.path.join(base_dir, name), os.path.join(changed_dir, name)
        if not os.path.exists(base_file) or not os.path.exists(changed_file):
            rows.append(f'| `{name[:-4]}` | only in one run | |')
            changed += 1
            continue
        pixels, box = compare(base_file, changed_file, os.path.join(output_dir, name))
        if pixels or box:
            changed += 1
            area = f'{box[0]},{box[1]} – {box[2]},{box[3]}' if box else 'size'
            rows.append(f'| `{name[:-4]}` | {pixels:,} px changed | {area} |')

    if changed:
        report = (f'## {title}: {changed} of {len(names)} screenshots differ\n\n'
                  '| Screenshot | Difference | Area (x,y – x,y) |\n|---|---|---|\n' + '\n'.join(rows) +
                  '\n\nThe images (base · changed · changed pixels in red) are in the `css-visual-diff` artifact.\n')
        if os.environ.get('GITHUB_ACTIONS'):
            print(f'::warning::{title}: {changed} of {len(names)} screenshots differ')
    else:
        report = f'## {title}: all {len(names)} screenshots are identical\n'

    print(report)
    summary = os.environ.get('GITHUB_STEP_SUMMARY')
    if summary:
        with open(summary, 'a', encoding='utf-8') as file:
            file.write(report)

    return 0


if __name__ == '__main__':
    sys.exit(main())
