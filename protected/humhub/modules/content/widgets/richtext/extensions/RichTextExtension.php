<?php

namespace humhub\modules\content\widgets\richtext\extensions;

use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use humhub\components\ActiveRecord;

/**
 * A RichTextExtension class can be used to prepare or postprocess a richtext prior of rendering or converting.
 *
 * @since 1.8
 */
interface RichTextExtension
{
    /**
     * Callback function called before a richtext output is rendered. This callback can be used to prepare
     * a richtext widget prior of rendering.
     *
     * @param ProsemirrorRichText $richtext
     * @param string $output
     * @return string
     */
    public function onBeforeOutput(ProsemirrorRichText $richtext, string $output): string;

    /**
     * Callback function called after a richtext output is rendered. This callback can be used to postprocess
     * a richtext widget result.
     *
     * @param ProsemirrorRichText $richtext
     * @param string $output
     * @return string
     */
    public function onAfterOutput(ProsemirrorRichText $richtext, string $output): string;

    /**
     * Callback function called after a richtext output is rendered. This callback can be used to postprocess
     * a richtext widget result.
     *
     * @param string $text
     * @param ActiveRecord $record
     * @param string|null $attribute
     * @param array $result
     * @return string
     */
    public function onPostProcess(string $text, ActiveRecord $record, ?string $attribute, array &$result): string;

    /**
     * Callback function called before a converter started to parse the a richtext. This callback can be used
     * to prepare a richtext prior conversion to a given format.
     *
     * @param string $text
     * @param string $format
     * @param array $options
     * @return string
     */
    public function onBeforeConvert(string $text, string $format, array $options = []): string;

    /**
     * Callback function called after a converter finished processing a richtext. This callback can be used
     * to preprocess an already converted richtext.
     *
     * @param string $text
     * @param string $format
     * @param array $options
     * @return string
     */
    public function onAfterConvert(string $text, string $format, array $options = []): string;

    /**
     * Contributes per-record client-render options this extension's {@see self::onBeforeOutput()} computed
     * that the client cannot derive from the processed markdown text alone (e.g.
     * {@see \humhub\modules\content\widgets\richtext\extensions\oembed\OembedExtension}'s server-fetched
     * oembed preview HTML, keyed by url) - see
     * {@see \humhub\modules\content\widgets\richtext\ProsemirrorRichText::getMarkdownAndRenderOptions()}
     * and `docs/develop/ui-js-vuejs-interop.md`, "RichTextOutput".
     *
     * Called once per render, immediately after every {@see self::onBeforeOutput()} call for that render has
     * completed - the same timing/statefulness contract {@see self::onAfterOutput()} already relies on for
     * extensions (like OembedExtension) that stash per-render state as an instance property between the two
     * calls.
     *
     * The overwhelming majority of extensions return an empty array here: their entire contribution already
     * lives IN the markdown text {@see self::onBeforeOutput()} returns and needs no separate client-side
     * config to reproduce.
     *
     * @return array
     * @since 1.19
     */
    public function getRenderOptions(): array;
}
