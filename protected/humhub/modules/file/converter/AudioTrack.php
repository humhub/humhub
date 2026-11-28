<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\converter;

use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use Symfony\Component\Process\Process;
use Throwable;
use Yii;

/**
 * The audio of a video or audio file for speech recognition: its first audio stream as mono 16 kHz Opus at 24 kbit/s
 * in an Ogg container (`audio/ogg`, about 11 MB per hour). Speech recognition works on 16 kHz mono, Opus keeps speech
 * intelligible at a low bitrate, and speech-to-text APIs (e.g. Whisper) take Ogg/Opus.
 *
 * Needs ffmpeg with libopus (`file` module's `ffmpegPath`). A derived variant: it is created on request only
 * ([[prepare()]], [[getFilename()]], [[getUrl()]]), never on upload, and dropped with a new content. A long file takes
 * its time (at most [[TIMEOUT]]), so a queue job should ask for it, not a web request. A file without an audio stream
 * gets none, as does a failed conversion (logged).
 *
 * ```php
 * $track = new AudioTrack();
 * if ($track->applyFile($file) && $track->prepare()) {
 *     $stream = $file->store->getContentStream(AudioTrack::VARIANT);
 * }
 * ```
 *
 * @since 1.20
 */
class AudioTrack extends BaseConverter
{
    public const VARIANT = 'audio-track';

    /**
     * Seconds ffmpeg may take for one file, enough for a video of several hours
     */
    public const TIMEOUT = 600;

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return self::VARIANT;
    }

    /**
     * Creates the variant when the file doesn't have it yet
     *
     * @return bool whether the file has the variant now
     */
    public function prepare(): bool
    {
        if ($this->file === null || !$this->canConvert($this->file)) {
            return false;
        }

        $this->convert(self::VARIANT);

        return $this->file->store->has(self::VARIANT);
    }

    /**
     * @inheritdoc
     */
    protected function convert($fileName)
    {
        if ($this->file->store->has($fileName) || !$this->canConvert($this->file)) {
            return;
        }

        $tempInput = null;
        $output = null;

        try {
            $stream = $this->openStream();
            try {
                $streamMeta = stream_get_meta_data($stream);
                if (($streamMeta['wrapper_type'] ?? null) === 'plainfile') {
                    $input = $streamMeta['uri'];
                } else {
                    // A remote store: ffmpeg needs a local path
                    $input = $tempInput = FileHelper::getTempFile();
                    $target = fopen($tempInput, 'wb');
                    stream_copy_to_stream($stream, $target);
                    fclose($target);
                }
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            $output = FileHelper::getTempFile();
            $process = new Process($this->buildCommand($input, $output), null, null, null, self::TIMEOUT);
            $process->run();

            clearstatcache(true, $output);
            if (!$process->isSuccessful() || !is_file($output) || filesize($output) === 0) {
                $error = trim(preg_replace('/\s*\R\s*/', ' ', $process->getErrorOutput()));
                $this->logFailure($error !== '' ? $error : 'exit code ' . $process->getExitCode());
                return;
            }

            $this->file->store->setByPath($output, $fileName);
        } catch (Throwable $e) {
            $this->logFailure($e->getMessage());
        } finally {
            foreach ([$tempInput, $output] as $path) {
                if ($path !== null) {
                    @unlink($path);
                }
            }
        }
    }

    /**
     * The ffmpeg command: the first audio stream (ffmpeg fails on a file without one), without metadata, as mono
     * 16 kHz Opus at 24 kbit/s tuned for speech, in an Ogg container (the temporary output has no extension)
     *
     * @return string[]
     */
    protected function buildCommand(string $input, string $output): array
    {
        return [
            $this->getModule()->ffmpegPath, '-nostdin', '-hide_banner', '-loglevel', 'error', '-y',
            '-i', $input,
            '-map', '0:a:0', '-map_metadata', '-1',
            '-ac', '1', '-ar', '16000',
            '-c:a', 'libopus', '-b:a', '24k', '-application', 'voip',
            '-f', 'ogg', $output,
        ];
    }

    /**
     * The content of the file as stream
     *
     * @return resource
     */
    protected function openStream()
    {
        return $this->file->store->getContentStream();
    }

    /**
     * @inheritdoc
     */
    protected function canConvert(File $file)
    {
        $mimeType = (string)$file->mime_type;

        return !empty($this->getModule()->ffmpegPath)
            && (str_starts_with($mimeType, 'video/') || str_starts_with($mimeType, 'audio/'))
            && $file->store->has();
    }

    private function logFailure(string $reason): void
    {
        Yii::warning('Could not create the audio track of file ' . $this->file->id . ': ' . $reason, 'file');
    }

    private function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        return $module;
    }
}
