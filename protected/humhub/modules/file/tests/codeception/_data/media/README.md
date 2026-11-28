# Media fixtures

Tiny files for `MediaInfoReaderTest`, `MediaHelperTest` and `AudioTrackTest`, generated with ffmpeg (run in this directory):

```bash
Q="-hide_banner -loglevel error -y"

# 64x36, 1 s
ffmpeg $Q -f lavfi -i testsrc=size=64x36:rate=10:duration=1 -c:v libx264 -pix_fmt yuv420p -preset veryslow -crf 40 -an landscape.mp4

# same stream, display rotation 90 degrees: shown as 36x64
ffmpeg $Q -display_rotation 90 -i landscape.mp4 -c copy rotated.mp4

# 64x36, 1 s, with a mono AAC audio stream (landscape.mp4 has none)
ffmpeg $Q -f lavfi -i testsrc=size=64x36:rate=10:duration=1 -f lavfi -i "sine=frequency=440:duration=1" -c:v libx264 -pix_fmt yuv420p -preset veryslow -crf 40 -c:a aac -b:a 16k -ar 16000 -ac 1 -shortest with-audio.mp4

# 48x32, 1 s
ffmpeg $Q -f lavfi -i testsrc=size=48x32:rate=10:duration=1 -c:v libvpx -b:v 20k -an clip.webm

# audio only, 1 s
ffmpeg $Q -f lavfi -i "sine=frequency=440:duration=1" -c:a pcm_u8 -ar 8000 tone.wav

# audio with an attached cover picture (16x16), about 1 s
ffmpeg $Q -f lavfi -i color=c=red:size=16x16:duration=1 -frames:v 1 cover.png
ffmpeg $Q -f lavfi -i "sine=frequency=440:duration=1" -i cover.png -map 0:a -map 1:v -c:a libmp3lame -b:a 16k -ar 16000 -c:v png -id3v2_version 3 -disposition:v attached_pic cover.mp3
rm cover.png

# an MP3 whose first frame follows 417 zero bytes, as some encoders write it
ffmpeg $Q -f lavfi -i "sine=frequency=440:duration=1" -c:a libmp3lame -b:a 32k -write_xing 0 -id3v2_version 0 plain.mp3
{ head -c 417 /dev/zero; cat plain.mp3; } > padded.mp3
rm plain.mp3
```
