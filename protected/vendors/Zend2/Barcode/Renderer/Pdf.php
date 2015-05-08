<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Barcode\Renderer;

use ZendPdf\Color;
use ZendPdf\Font;
use ZendPdf\Page;
use ZendPdf\PdfDocument;

/**
 * Class for rendering the barcode in PDF resource
 */
class Pdf extends AbstractRenderer
{
    /**
     * PDF resource
     * @var PdfDocument
     */
    protected $resource = null;

    /**
     * Page number in PDF resource
     * @var int
     */
    protected $page = 0;

    /**
     * Module size rendering
     * @var float
     */
    protected $moduleSize = 0.5;

    /**
     * Set a PDF resource to draw the barcode inside
     *
     * @param PdfDocument $pdf
     * @param int     $page
     * @return Pdf
     */
    public function setResource(PdfDocument $pdf, $page = 0)
    {
        $this->resource = $pdf;
        $this->page     = intval($page);

        if (!count($this->resource->pages)) {
            $this->page = 0;
            $this->resource->pages[] = new Page(
                Page::SIZE_A4
            );
        }
        return $this;
    }

    /**
     * Check renderer parameters
     *
     * @return void
     */
    protected function checkSpecificParams()
    {
    }

    /**
     * Draw the barcode in the PDF, send headers and the PDF
     * @return mixed
     */
    public function render()
    {
        $this->draw();
        header("Content-Type: application/pdf");
        echo $this->resource->render();
    }

    /**
     * Initialize the PDF resource
     * @return void
     */
    protected function initRenderer()
    {
        if ($this->resource === null) {
            $this->resource = new PdfDocument();
            $this->resource->pages[] = new Page(
                Page::SIZE_A4
            );
        }

        $pdfPage = $this->resource->pages[$this->page];
        $this->adjustPosition($pdfPage->getHeight(), $pdfPage->getWidth());
    }

    /**
     * Draw a polygon in the rendering resource
     * @param array $points
     * @param int $color
     * @param  bool $filled
     */
    protected function drawPolygon($points, $color, $filled = true)
    {
        $page = $this->resource->pages[$this->page];
        foreach ($points as $point) {
            $x[] = $point[0] * $this->moduleSize + $this->leftOffset;
            $y[] = $page->getHeight() - $point[1] * $this->moduleSize - $this->topOffset;
        }
        if (count($y) == 4) {
            if ($x[0] != $x[3] && $y[0] == $y[3]) {
                $y[0] -= ($this->moduleSize / 2);
                $y[3] -= ($this->moduleSize / 2);
            }
            if ($x[1] != $x[2] && $y[1] == $y[2]) {
                $y[1] += ($this->moduleSize / 2);
                $y[2] += ($this->moduleSize / 2);
            }
        }

        $color = new Color\Rgb(
            (($color & 0xFF0000) >> 16) / 255.0,
            (($color & 0x00FF00) >> 8) / 255.0,
            ($color & 0x0000FF) / 255.0
        );

        $page->setLineColor($color);
        $page->setFillColor($color);
        $page->setLineWidth($this->moduleSize);

        $fillType = ($filled)
                  ? Page::SHAPE_DRAW_FILL_AND_STROKE
                  : Page::SHAPE_DRAW_STROKE;

        $page->drawPolygon($x, $y, $fillType);
    }

    /**
     * Draw a polygon in the rendering resource
     * @param string  $text
     * @param float   $size
     * @param array   $position
     * @param string  $font
     * @param int     $color
     * @param string  $alignment
     * @param float   $orientation
     */
    protected function drawText(
        $text,
        $size,
        $position,
        $font,
        $color,
        $alignment = 'center',
        $orientation = 0
    ) {
        $page  = $this->resource->pages[$this->page];
        $color = new Color\Rgb(
            (($color & 0xFF0000) >> 16) / 255.0,
            (($color & 0x00FF00) >> 8) / 255.0,
            ($color & 0x0000FF) / 255.0
        );

        $page->setLineColor($color);
        $page->setFillColor($color);
        $page->setFont(Font::fontWithPath($font), $size * $this->moduleSize * 1.2);

        $width = $this->widthForStringUsingFontSize(
            $text,
            Font::fontWithPath($font),
            $size * $this->moduleSize
        );

        $angle = pi() * $orientation / 180;
        $left = $position[0] * $this->moduleSize + $this->leftOffset;
        $top  = $page->getHeight() - $position[1] * $this->moduleSize - $this->topOffset;

        switch ($alignment) {
            case 'center':
                $left -= ($width / 2) * cos($angle);
                $top  -= ($width / 2) * sin($angle);
                break;
            case 'right':
                $left -= $width;
                break;
        }
        $page->rotate($left, $top, $angle);
        $page->drawText($text, $left, $top);
        $page->rotate($left, $top, - $angle);
    }

    /**
     * Calculate the width of a string:
     * in case of using alignment parameter in drawText
     * @param string $text
     * @param Font $font
     * @param float $fontSize
     * @return float
     */
    public function widthForStringUsingFontSize($text, $font, $fontSize)
    {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $text);
        $characters    = array();
        for ($i = 0, $len = strlen($drawingString); $i < $len; $i++) {
            $characters[] = (ord($drawingString[$i ++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }
}
