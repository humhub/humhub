# Media viewer

Images and videos open in the media viewer (`humhub.ui.gallery`, since 1.20): a modal dialog with zoom (pinch,
double tap, Ctrl + wheel, the Z key), swipe and arrow keys, a native video player with subtitles, a caption and a download (or an "Open") button.
It replaces blueimp Gallery.

The viewer is built on [PhotoSwipe](https://photoswipe.com), which is internal: a module uses the markup and the
API on this page only, never PhotoSwipe, its options or its `.pswp*` classes. So the engine can be updated or
replaced without touching modules.

## Markup

Mark a link to the file, usually with a thumbnail inside, or an image:

```html
<a href="/file/file/download?guid=…" data-ui-gallery="gallery-post-12" title="Holiday.jpg">
    <img src="…preview…" alt="The beach at noon">
</a>

<img src="/file/file/download?guid=…" data-ui-gallery="text-4f2e" alt="Site plan">
```

| Attribute | Meaning |
|---|---|
| `data-ui-gallery` | The group: all elements with the same value open together, in document order. Without a value, the `[data-ui-gallery]` siblings of the clicked element. |
| `href` / `src` | The original: what the download button saves, and the target without JavaScript. |
| `data-ui-gallery-src` | Optional: what the viewer shows instead, e.g. the display version of an image (see below). |
| `type="video/…"` | The item is a video, played in the native player. Without it the item is an image. |
| `data-ui-gallery-open-url` | Optional: the URL of a modal offering more than the download (the "Open file" modal, `File::getViewUrl()`). The slide gets an "Open" button instead of the download button: it closes the viewer and loads the URL in the global modal. |
| `data-ui-gallery-subtitles-url` | Optional, videos: the variants API URL of the file (`GET /api/v2/file/{id}/variants`). When the slide's video is created, the viewer fetches it and adds a `<track>` per variant named `subtitles-<language>.vtt` or `captions-<language>.vtt` (`kind` and `srclang` from the name, `label` the language's name in the page language, `src` the variant's `url`; other variants are ignored); the track of the page language (`<html lang>`, primary subtag) is `default`. The request is made once per URL for the page and shared by all slides and viewers. A failed request only logs (and is not kept), the video plays without subtitles. |
| `title` | Optional caption, shown below the image and read with the position ("2 of 5: Holiday.jpg"). |
| `alt` of the thumbnail | The text alternative of the large image. |

An `img[data-ui-gallery]` inside a link is opened through the link. Images of most browsers' formats and the videos
`<video>` plays (mp4, webm, ogv; mov where the codec is supported) are shown; a video the browser can't play, or an
image that can't be loaded, offers its download instead.

A video opened by a click on its element starts playing (the click is the user gesture; a browser that refuses leaves
the native controls). A "+N" tile (an element
containing `.post-files-image-more`) and other slides don't autoplay. Only one medium plays at a time: a video
starting in the viewer pauses every other playing `<video>` and `<audio>` of the page.

## Display version of an image

The file module scales a large JPEG, PNG or WebP image down for the viewer: `humhub\modules\file\converter\DisplayImage`
creates the variant `display-image` on its first request, fitting `imageDisplayMaxWidth` × `imageDisplayMaxHeight`
(file module options, 2048 px each). A smaller image, and any file the converter doesn't apply to, is served as it is
under the same URL. So link the original and point the viewer to the display version:

```php
use humhub\modules\file\converter\DisplayImage;

$displayImage = new DisplayImage();
$options = ['data-ui-gallery' => 'gallery-' . $file->guid];
if ($displayImage->applyFile($file)) {
    $options['data-ui-gallery-src'] = $displayImage->getUrl();
}
echo Html::a($thumbnail, $file->getUrl(), $options);
```

`PreviewImage::renderGalleryLink()` does that, the file serializer delivers it as `displayUrl`, and rich text images
of uploaded files get it from `humhub.modules.file.getFileUrl(guid, 'display')`.

## JavaScript

Open the viewer without markup, e.g. from a carousel or a Vue component:

```js
humhub.modules.ui.gallery.open(elements, index);
```

`elements` are `[data-ui-gallery]`-like elements (an array, a `NodeList` or jQuery), read as described above;
`index` is a position or one of the elements. The focus returns to that element when the viewer closes.

Events, through `humhub.modules.event`:

| Event | When |
|---|---|
| `humhub:ui:gallery:opened` | The viewer is open and has the focus. |
| `humhub:ui:gallery:closed` | The viewer is closed and gone. |

A DOM event on `document` that the viewer listens to, `humhub:file:variants-changed` with `detail: {fileId}`: the
subtitles of that file changed (the subtitle manager fires it after a save or delete), so the viewer forgets the cached
variants request of its `data-ui-gallery-subtitles-url` and asks again for the next slide. The audio player of the file
rows listens to it likewise: it drops the cached list of the file and, if the file is loaded, fetches its tracks again.

## Accessibility

- A modal dialog named "Media viewer": the focus moves into it, Tab and Shift+Tab stay inside, Escape closes it and
  the focus returns to the element that opened it (`humhub.ui.focus.restore()`). "Open" moves the focus to the slide's
  tile before it loads its modal, so closing that modal returns the focus there. A slide change that hides the Download or
  Open button with the focus moves it to the other one.
- Every control is a named button (Close, Previous, Next, Zoom, Download or Open). Each slide is labelled with its position,
  and a change of the slide is announced with the caption.
- The subtitles are the player's own: its captions menu lists the languages, the tracks carry their language name.
- A video keeps its keys: ←/→ and Space control the player while the focus is in it, Escape still closes.
- Back (the browser's, a gesture, the Android button of the app) closes the viewer and the page stays: the open
  viewer has a history entry of its own, which closing it with Escape or Close removes again.
- With reduced motion (the user's appearance setting or the system's) the viewer opens and closes without
  animation.
