<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\widgets\Menu;
use Throwable;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The context menu of a content record, as data instead of markup — what the
 * `ContentControls` Vue island loads when its `⋮` is opened.
 *
 * ## Why this exists
 *
 * `WallEntryControls` is the platform's content context menu, and modules have always
 * extended it by adding widget entries in a `WallEntryControls::EVENT_INIT` handler. A
 * client-rendered menu cannot use those: a widget is markup, and a Vue menu needs
 * `{id, label, icon, sortOrder}` so it can sort, filter, override and remove entries.
 *
 * Rather than break every contributing module the way the comment island's own controls
 * menu did (see `docs/develop/module-migrate.md`), this endpoint resolves the very same
 * widget stack — event handlers and all — and serializes the result. A module that
 * contributes a describable entry ({@see \humhub\modules\ui\menu\DescribableWidget}) needs
 * no change at all; one that contributes markup only gets that markup shipped as an `html`
 * escape hatch, with a deprecation notice.
 *
 * ## Caller context
 *
 * Everything here depends on WHO is asking, which is exactly why it is its own endpoint
 * rather than part of a content payload (see `docs/develop/concept-api.md`, "Caller context
 * is not part of a payload"). The island loads it lazily on menu open, so the price is paid
 * only when a menu is actually used — the same trade the comment island makes with
 * `GET comment/<id>/permissions`.
 *
 * @since 1.20
 */
class ControlsController extends BaseController
{
    /**
     * The core control entries a host island may ask the server NOT to send, by the name it
     * knows them under. A host that renders an action itself — a file browser has its own
     * Edit/Move/Delete — would otherwise get a second, server-rendered copy of it.
     *
     * Named rather than derived from the widget class, because the class is an implementation
     * detail the caller should not have to spell.
     */
    private const SUPPRESSIBLE = [
        'edit' => 'disableControlsEntryEdit',
        'topics' => 'disableControlsEntryTopics',
        'permalink' => 'disableControlsEntryPermalink',
        'delete' => 'disableControlsEntryDelete',
        'visibility' => 'disableControlsEntrySwitchVisibility',
        'notifications' => 'disableControlsEntrySwitchNotification',
        'pin' => 'disableControlsEntryPin',
        'move' => 'disableControlsEntryMove',
        'archive' => 'disableControlsEntryArchive',
    ];

    /**
     * @inheritdoc
     */
    protected bool $enableSessionAuth = true;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET', 'HEAD'],
                ],
            ],
        ]);
    }

    /**
     * The resolved context menu of one content record.
     *
     * @param int|string $id the content id
     * @param string|null $viewContext where the menu is being shown, one of the
     *        {@see \humhub\modules\content\widgets\stream\StreamEntryOptions} `VIEW_CONTEXT_*`
     *        values. Selects the same render-options profile a server-rendered menu would
     *        have used in that place, so a menu inside a module's own UI does not offer
     *        stream-only actions and vice versa.
     * @param string|null $suppress comma-separated core entry names the caller renders itself
     *        and does not want from the server — see {@see self::SUPPRESSIBLE}.
     */
    public function actionIndex($id, $viewContext = null, $suppress = null)
    {
        $content = Content::findOne(['id' => (int)$id]);

        if (!$content) {
            throw new NotFoundHttpException();
        }

        if (!$content->canView()) {
            throw new ForbiddenHttpException();
        }

        $record = $content->getModel();

        if (!$record instanceof ContentActiveRecord) {
            throw new NotFoundHttpException();
        }

        return [
            'entries' => $this->resolveEntries($record, $viewContext, (string)$suppress),
            'capabilities' => $this->describeCapabilities($content),
        ];
    }

    /**
     * What the caller may do with this record — the values a host island gates its own
     * native menu entries on, so it does not have to re-implement the rules client-side.
     *
     * `canDelete` mirrors {@see \humhub\modules\content\widgets\DeleteLink}: deleting is
     * governed by edit permission. `canAdminDelete` distinguishes deleting someone else's
     * content, which is what the admin-delete dialog (reason, notify the author) keys on.
     */
    protected function describeCapabilities(Content $content): array
    {
        $canEdit = $content->canEdit();

        return [
            'canEdit' => $canEdit,
            'canDelete' => $canEdit,
            'canAdminDelete' => $canEdit && (int)$content->created_by !== (int)Yii::$app->user->id,
            'canPin' => $content->canPin(),
            'canArchive' => $content->canArchive(),
            // canMove() answers true or a human-readable reason why not, so anything but a
            // strict true is a no.
            'canMove' => $content->canMove() === true,
        ];
    }

    /**
     * Runs the widget stack for this record and turns every resolved entry into a descriptor.
     *
     * @return array[]
     */
    protected function resolveEntries(ContentActiveRecord $record, ?string $viewContext, string $suppress = ''): array
    {
        $controls = $this->createControls($record, $viewContext, $suppress);

        if ($controls === null || $controls->renderOptions->isControlsMenuDisabled()) {
            return [];
        }

        // The same order WallEntryControls::run() uses: the wall entry widget contributes its
        // own entries after the EVENT_INIT handlers have run, then EVENT_RUN gets its say.
        $controls->initControls();
        $controls->trigger(Menu::EVENT_RUN);

        $entries = [];
        $usedIds = [];

        foreach ($controls->getEntries(null, true) as $entry) {
            $descriptor = $this->describeEntry($entry);

            if ($descriptor === null) {
                continue;
            }

            $descriptor['id'] = $this->uniqueId((string)($descriptor['id'] ?? ''), $usedIds);
            $entries[] = $descriptor;
        }

        return $entries;
    }

    /**
     * Instantiates the menu the way a stream entry would, so every `EVENT_INIT` handler sees
     * the state it expects. Constructing the widget is what fires that event.
     */
    protected function createControls(ContentActiveRecord $record, ?string $viewContext, string $suppress = ''): ?WallEntryControls
    {
        $options = new WallStreamEntryOptions();

        if ($viewContext !== null && $viewContext !== '') {
            $options->viewContext($viewContext);
        }

        foreach (array_filter(array_map('trim', explode(',', $suppress))) as $name) {
            if (isset(self::SUPPRESSIBLE[$name])) {
                $options->{self::SUPPRESSIBLE[$name]}();
            }
        }

        if (!$record->wallEntryClass || !class_exists($record->wallEntryClass)) {
            return null;
        }

        try {
            $wallEntryWidget = Yii::createObject([
                'class' => $record->wallEntryClass,
                'model' => $record,
                'renderOptions' => $options,
            ]);

            return Yii::createObject([
                'class' => WallEntryControls::class,
                'object' => $record,
                'wallEntryWidget' => $wallEntryWidget,
                'renderOptions' => $options,
            ]);
        } catch (Throwable $e) {
            Yii::error($e);
            return null;
        }
    }

    /**
     * One entry as a descriptor, falling back to its rendered markup.
     *
     * The fallback ships the entry's own HTML under `html`, which the island injects with
     * `v-html` and runs the UI additions over — legacy `data-action-click` handlers included.
     * It keeps modules working that render menu items no descriptor can express, and it is
     * deliberately a dead end: the entry cannot be overridden, removed or conditionally shown
     * by a client, which is why it logs a deprecation.
     */
    protected function describeEntry(MenuEntry $entry): ?array
    {
        $descriptor = $entry->describe();

        if ($descriptor !== null) {
            return $descriptor;
        }

        $html = trim((string)$entry->render(['class' => 'dropdown-item']));

        if ($html === '') {
            return null;
        }

        Yii::warning(
            'Menu entry ' . $entry->getEntryClass() . ' cannot describe itself and was delivered '
            . 'as raw HTML. Implement humhub\\modules\\ui\\menu\\DescribableWidget — the HTML '
            . 'fallback is deprecated and will be removed.',
            'content',
        );

        return [
            'id' => $entry->getId(),
            'sortOrder' => $entry->getSortOrder(),
            'html' => $this->unwrapListItem($html),
        ];
    }

    /**
     * Strips one enclosing `<li>` from rendered entry markup.
     *
     * A widget entry renders its own `<li>` (that is why `views/wallEntryControls.php` emits
     * it unwrapped, unlike a plain entry), but the island renders every entry — described or
     * not — as one `<li>` of its own. Handing it inner markup keeps that uniform. Markup that
     * is not a single `<li>` is passed through untouched.
     */
    protected function unwrapListItem(string $html): string
    {
        $unwrapped = preg_replace('#^<li\b[^>]*>(.*)</li>$#is', '$1', $html, 1, $count);

        return $count === 1 ? trim((string)$unwrapped) : $html;
    }

    /**
     * Keeps entry ids unique within one menu.
     *
     * Ids are what a client overrides and removes entries by, so two entries may not share
     * one. A widget entry with no id of its own falls back to its class name
     * ({@see \humhub\modules\ui\menu\WidgetMenuEntry::describeIdFor()}), which collides as
     * soon as the same widget is contributed twice — `share-between-humhub` adds one
     * `ShareLink` per configured site. Only the resolving side sees the whole menu, so it
     * disambiguates here.
     *
     * @param string[] $usedIds
     */
    protected function uniqueId(string $id, array &$usedIds): string
    {
        if ($id === '') {
            $id = 'entry';
        }

        $candidate = $id;

        for ($suffix = 2; in_array($candidate, $usedIds, true); $suffix++) {
            $candidate = $id . '-' . $suffix;
        }

        $usedIds[] = $candidate;

        return $candidate;
    }
}
