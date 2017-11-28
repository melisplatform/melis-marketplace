<?php

namespace MelisMarketPlace\Service;

use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

/**
 * This will decorate the text instead instead of displaying VT100 bash characters
 * Class ComposerOutputFormatterStyle
 * @package MelisMarketPlace\Service
 */
class ComposerOutputFormatterStyle implements OutputFormatterStyleInterface
{

    const INFO    = '#02de02';
    const ERROR   = '#ff190d';
    const COMMENT = '#fbff0f';

    protected $foreground;
    protected $background;
    protected $option;

    public function __construct($foreground = null, $background = null)
    {
        $this->setForeground($foreground);
        $this->setBackground($background);
    }

    /**
     * Sets style foreground color.
     *
     * @param string $color The color name
     */
    public function setForeground($color = null)
    {
        $this->foreground = $color;
    }

    /**
     * Sets style background color.
     *
     * @param string $color The color name
     */
    public function setBackground($color = null)
    {
        $this->background = $color;
    }

    /**
     * Sets some specific style option.
     *
     * @param string $option The option name
     */
    public function setOption($option)
    {
        return;
    }

    /**
     * Unsets some specific style option.
     *
     * @param string $option The option name
     */
    public function unsetOption($option)
    {
        return;
    }

    /**
     * Sets multiple style options at once.
     * @param array $options
     */
    public function setOptions(array $options)
    {
        return;
    }

    /**
     * Applies the style to a given text.
     *
     * @param string $text The text to style
     *
     * @return string
     */
    public function apply($text)
    {
        $foreground = null;
        $background = null;

        if($this->foreground)
            $foreground = 'color: ' . $this->foreground . ';';

        if($this->background)
            $background = 'background: ' . $this->background . ';';

        $dom = '<span style="'.$foreground.$background.'">' . $text . '</span>';

        return $dom;
    }
}