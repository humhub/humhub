<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\components\Event;
use humhub\libs\StdClass;
use humhub\modules\file\models\File;

/**
 * WARNING: This class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * ---
 *
 * This class is used to handle the data stored in the `File::$metadata->file` namespace.
 *
 * @since 1.15; this class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * @internal (ToDo)
 *
 * @see File::$metadata
 * @see Metadata
 * @see StdClass
 */
final class FileModuleMetadata extends ModuleMetadata
{
    public const MODULE_NAME = 'file';

    public const EVENT_GET_WELL_KNOWN_VARIANTS = 'getWellKnownVariants';
    public const WELL_KNOWN_VARIANT_DRAFT = '_draft';
    public const WELL_KNOWN_VARIANT_ORIGINAL = '_original';
    public const WELL_KNOWN_VARIANT_UPLOAD = '_upload';

    // @codingStandardsIgnoreStart PSR2.Methods.MethodDeclaration.Underscore
    public ?FileVariantMetadata $_draft;
    public ?FileVariantMetadata $_original;
    public ?FileVariantMetadata $_upload;
    // @codingStandardsIgnoreEnd PSR2.Methods.MethodDeclaration.Underscore

    /**
     * @var array<string, string> $wellKnownVariants
     * @see static::getWellKnownVariants()
     */
    private static array $wellKnownVariants;

    public function __construct(...$args)
    {
        // unset properties so that the first access calls the magic functions
        unset($this->_draft, $this->_original, $this->_upload);

        parent::__construct($args);
    }

    public function __get($name)
    {
        $name = $this->validatePropertyName($name, __METHOD__);

        if ($name) {
            $class = self::getWellKnownVariants()[$name] ?? null;
            if ($class) {
                $this->$name = new $class();
            }

            // now return the value
            return $this->$name;
        }

        return null;
    }

    /**
     * Used to get a list of well-known variants and their respective metadata class.
     *
     * Returns a set of `[$name => $class]` pairs, where
     * - `$name` is the variant name, and
     * - `$class` the class name to be used to instantiate its metadata class
     *
     * @return string[]|array<string, string> returns a set of `[$name => $class]` pairs, see function description
     */
    public static function getWellKnownVariants(): array
    {
        try {
            return self::$wellKnownVariants;
        } catch (\Error $e) {
            $event = new Event([
                'result' => [
                    self::WELL_KNOWN_VARIANT_DRAFT => FileVariantMetadata::class,
                    self::WELL_KNOWN_VARIANT_ORIGINAL => FileVariantMetadata::class,
                    self::WELL_KNOWN_VARIANT_UPLOAD => FileVariantMetadata::class,
                ]
            ]);

            Event::trigger(self::class, self::EVENT_GET_WELL_KNOWN_VARIANTS, $event);

            self::$wellKnownVariants = $event->result;
        }

        return self::$wellKnownVariants;
    }
}
