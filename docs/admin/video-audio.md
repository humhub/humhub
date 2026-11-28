# Video and audio files — ffprobe and ffmpeg

HumHub shows attached videos as tiles and audio files as playable rows, and keeps subtitles with them. For most of
this it needs nothing on the server. Two optional programs of the [FFmpeg](https://ffmpeg.org/) project add to it:

| Program | What HumHub uses it for | Without it |
|---|---|---|
| `ffprobe` | Width, height and duration of videos and audio files, read when a file is uploaded | The built-in reader (getID3) reads them; it knows the common formats (MP4, MOV, WebM, Ogg, MP3, WAV, FLAC, M4A) |
| `ffmpeg` | The audio track of a video or audio file for speech recognition, e.g. for a module that creates subtitles | Modules that need the audio track can't process videos |

Both are part of the `ffmpeg` package of every Linux distribution (`apt install ffmpeg`, `dnf install ffmpeg`, …).

## ffprobe: metadata of videos and audio files

The built-in reader is enough for most installations. Set the path of `ffprobe` when users upload formats it doesn't
know, or files it reads wrongly:

```php
// config/common.php
return [
    'modules' => [
        'file' => [
            'ffprobePath' => '/usr/bin/ffprobe',
        ],
    ],
];
```

or as environment variable: `HUMHUB_CONFIG__MODULES__FILE__FFPROBE_PATH=/usr/bin/ffprobe`.

Where `ffprobe` can't tell a value (e.g. the length of an MP3 with a variable bitrate, which it only estimates), the
built-in reader fills it in. Files uploaded before are read by the queue: `php yii file/collect-metadata` runs the
same at once.

## ffmpeg: the audio track

Modules that create subtitles or transcripts with AI send the audio of a file to a speech recognition service. HumHub
prepares it with `ffmpeg`: the first audio track of the file, mono, 16 kHz, Opus in an Ogg container (about 11 MB per
hour), which fits the upload limits of speech services. It is made only when such a module asks for it, in a queue
job, and kept as a variant of the file until the file gets new content.

```php
// config/common.php
return [
    'modules' => [
        'file' => [
            'ffmpegPath' => '/usr/bin/ffmpeg',
        ],
    ],
];
```

or as environment variable: `HUMHUB_CONFIG__MODULES__FILE__FFMPEG_PATH=/usr/bin/ffmpeg`.

- `ffmpeg` needs the Opus encoder (`libopus`); the distribution packages have it (`ffmpeg -encoders | grep opus`).
- A conversion that fails (no audio track, no encoder, more than 10 minutes) leaves no audio track and is logged in
  the category `file`.
- Make sure the queue runs (see the cron jobs of your installation): the conversion of a long video takes a while and
  must not run in a web request.
