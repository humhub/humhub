<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

use Throwable;

/**
 * @since 1.15
 */
trait InvalidArgumentExceptionTrait
{
    protected string $methodName;
    protected ?string $parameter = null;
    protected array $valid = [];
    protected $given;
    protected string $suffix = '';
    protected bool $isInstantiating = true;

    /**
     * @param string $parameterOrMessage Name of parameter in question, or alternatively the full message string
     *      containing at least one space character (ASCII 32). In this case, `$valid` and `$given` are considered to be
     *      `$code` and `$previous` respectively
     * @param string|string[] $valid (List of) valid parameter(s)
     * @param mixed $given Parameter received
     * @param int $code Optional exception code
     * @param Throwable|null $previous Optional previous exception
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpMissingParamTypeInspection
     */
    public function __construct($parameterOrMessage, $valid = null, $given = null, $code = null, $previous = null)
    {
        $exception = null;
        $message = 'Invalid exception instantiation';

        try {
            if (!is_string($parameterOrMessage)) {
                throw new InvalidArgumentTypeException(
                    '$parameterOrMessage',
                    ['string'],
                    $parameterOrMessage,
                    0,
                    $this,
                );
            }

            if (empty($parameterOrMessage = trim($parameterOrMessage))) {
                throw new InvalidArgumentValueException(
                    '$parameterOrMessage',
                    'non-empty string',
                    $parameterOrMessage,
                    0,
                    $this,
                );
            }

            // check if $parameter is actually the $message
            if (strpos($parameterOrMessage, ' ') !== false) {
                $message = $parameterOrMessage;
                $code ??= is_int($valid) ? $valid : 0;
                if ($given instanceof Throwable) {
                    $previous ??= $given;
                }
            } else {
                if (false !== $pos = strrpos($parameterOrMessage, '::')) {
                    $this->methodName = trim(substr($parameterOrMessage, 0, $pos), ':');
                    $this->parameter = trim(substr($parameterOrMessage, $pos), ':');
                } else {
                    $trace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                    $trace = end($trace);
                    $this->methodName = ltrim(
                        ($trace['class'] ?? '') . '::' . ($trace['function'] ?? 'unknown method'),
                        ':',
                    );

                    $this->parameter = $parameterOrMessage;
                }
                try {
                    $this->setValid($valid);
                } catch (InvalidArgumentTypeException $t) {
                    throw $t->setMethodName($this->methodName);
                }

                $this->given = $given;

                $message = $this->formatMessage();
            }
        } catch (Throwable $exception) {
        }

        parent::__construct($message, $code, $previous);

        if ($exception) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw $exception;
        }

        $this->isInstantiating = false;
    }

    /**
     * @see          static::__construct()
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function newInstance($parameterOrMessage, $valid = null, $given = null, $code = null, $previous = null): self
    {
        return new static($parameterOrMessage, $valid, $given, $code, $previous);
    }

    protected function formatPrologue(): string
    {
        $int = filter_var($this->parameter, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($int === null) {
            $parameter = preg_replace('@^(\.\.\.)?\$?@', '$1\$', $this->parameter);

            return $this->parameter === null
                ? 'Unknown argument'
                : "Argument " . $parameter;
        }

        return 'Argument #' . $int;
    }

    protected function formatValid(): string
    {
        return (count($this->valid) > 1
                ? 'one of '
                : '') . implode(', ', $this->valid);
    }

    protected function formatGiven(): string
    {
        $given = $this->given ?? 'NULL';

        /**
         * @noinspection PhpLoopNeverIteratesInspection
         * @noinspection LoopWhichDoesNotLoopInspection
         */
        while (empty($given)) {
            if ($given === '') {
                $given = 'empty string';
                break;
            }

            if ($given === '0') {
                $given = "'0'";
                break;
            }

            if ($given === []) {
                $given = '[]';
                break;
            }

            break;
        }

        if (!is_string($given)) {
            try {
                $given = json_encode($given, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $given = serialize($given);
            }
        }

        return $given;
    }

    public function formatMessage(): string
    {
        return sprintf(
            '%s passed to %s must be %s%s - %s given.',
            $this->formatPrologue(),
            $this->methodName,
            $this->formatValid(),
            $this->getSuffix(),
            $this->formatGiven(),
        );
    }

    protected function updateMessage(): self
    {
        if ($this->isInstantiating) {
            return $this;
        }

        $this->message = $this->formatMessage();

        return $this;
    }

    public function getGiven()
    {
        return $this->given;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function setMethodName(string $methodName): self
    {
        $this->methodName = $methodName;

        return $this->updateMessage();
    }

    public function getName(): string
    {
        if (method_exists(parent::class, 'getName')) {
            return parent::getName() . " value";
        }

        return 'Invalid value';
    }

    public function getParameter(): ?string
    {
        return $this->parameter;
    }

    public function setParameter(?string $parameter): InvalidArgumentExceptionTrait
    {
        $this->parameter = $parameter;

        return $this->updateMessage();
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    public function setSuffix(string $suffix): self
    {
        $this->suffix = $suffix;

        return $this->updateMessage();
    }

    public function getValid(): array
    {
        return $this->valid;
    }

    public function setValid($valid): self
    {
        if (is_string($valid)) {
            $this->valid = [$valid];
            return $this;
        }

        if (is_iterable($valid)) {
            foreach ($valid as $key => $value) {
                try {
                    $this->valid[] = (string)($value ?? 'NULL');
                } catch (\Error $t) {
                    throw new InvalidArgumentTypeException(sprintf("\$valid[%s]", $key), ['string'], $value, 0, $this);
                }
            }

            return $this;
        }

        throw new InvalidArgumentTypeException('$valid', ['string', 'string[]'], $valid, 0, $this);
    }
}
