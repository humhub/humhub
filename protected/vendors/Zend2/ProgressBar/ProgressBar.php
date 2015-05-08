<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ProgressBar;

use Zend\ProgressBar\Exception;
use Zend\Session;

/**
 * Zend\ProgressBar offers an interface for multiple environments.
 */
class ProgressBar
{
    /**
     * Min value
     *
     * @var float
     */
    protected $min;

    /**
     * Max value
     *
     * @var float
     */
    protected $max;

    /**
     * Current value
     *
     * @var float
     */
    protected $current;

    /**
     * Start time of the progressbar, required for ETA
     *
     * @var int
     */
    protected $startTime;

    /**
     * Current status text
     *
     * @var string
     */
    protected $statusText = null;

    /**
     * Adapter for the output
     *
     * @var \Zend\ProgressBar\Adapter\AbstractAdapter
     */
    protected $adapter;

    /**
     * Namespace for keeping the progressbar persistent
     *
     * @var string
     */
    protected $persistenceNamespace = null;

    /**
     * Create a new progressbar backend.
     *
     * @param  Adapter\AbstractAdapter $adapter
     * @param  float|int               $min
     * @param  float|int               $max
     * @param  string|null             $persistenceNamespace
     * @throws Exception\OutOfRangeException When $min is greater than $max
     */
    public function __construct(Adapter\AbstractAdapter $adapter, $min = 0, $max = 100, $persistenceNamespace = null)
    {
        // Check min/max values and set them
        if ($min > $max) {
            throw new Exception\OutOfRangeException('$max must be greater than $min');
        }

        $this->min     = (float) $min;
        $this->max     = (float) $max;
        $this->current = (float) $min;

        // See if we have to open a session namespace
        if ($persistenceNamespace !== null) {
            $this->persistenceNamespace = new Session\Container($persistenceNamespace);
        }

        // Set adapter
        $this->adapter = $adapter;

        // Track the start time
        $this->startTime = time();

        // See If a persistenceNamespace exists and handle accordingly
        if ($this->persistenceNamespace !== null) {
            if (isset($this->persistenceNamespace->isSet)) {
                $this->startTime  = $this->persistenceNamespace->startTime;
                $this->current    = $this->persistenceNamespace->current;
                $this->statusText = $this->persistenceNamespace->statusText;
            } else {
                $this->persistenceNamespace->isSet      = true;
                $this->persistenceNamespace->startTime  = $this->startTime;
                $this->persistenceNamespace->current    = $this->current;
                $this->persistenceNamespace->statusText = $this->statusText;
            }
        } else {
            $this->update();
        }
    }

    /**
     * Get the current adapter
     *
     * @return Adapter\AbstractAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Update the progressbar
     *
     * @param  float  $value
     * @param  string $text
     * @return void
     */
    public function update($value = null, $text = null)
    {
        // Update value if given
        if ($value !== null) {
            $this->current = min($this->max, max($this->min, $value));
        }

        // Update text if given
        if ($text !== null) {
            $this->statusText = $text;
        }

        // See if we have to update a namespace
        if ($this->persistenceNamespace !== null) {
            $this->persistenceNamespace->current    = $this->current;
            $this->persistenceNamespace->statusText = $this->statusText;
        }

        // Calculate percent
        if ($this->min === $this->max) {
            $percent = false;
        } else {
            $percent = (float) ($this->current - $this->min) / ($this->max - $this->min);
        }

        // Calculate ETA
        $timeTaken = time() - $this->startTime;

        if ($percent === .0 || $percent === false) {
            $timeRemaining = null;
        } else {
            $timeRemaining = round(((1 / $percent) * $timeTaken) - $timeTaken);
        }

        // Poll the adapter
        $this->adapter->notify($this->current, $this->max, $percent, $timeTaken, $timeRemaining, $this->statusText);
    }

    /**
     * Update the progressbar to the next value
     *
     * @param  int $diff
     * @param  string $text
     * @return void
     */
    public function next($diff = 1, $text = null)
    {
        $this->update(max($this->min, min($this->max, $this->current + $diff)), $text);
    }

    /**
     * Call the adapters finish() behaviour
     *
     * @return void
     */
    public function finish()
    {
        if ($this->persistenceNamespace !== null) {
            unset($this->persistenceNamespace->isSet);
        }

        $this->adapter->finish();
    }
}
