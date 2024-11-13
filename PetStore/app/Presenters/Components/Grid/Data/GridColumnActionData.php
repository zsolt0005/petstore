<?php declare(strict_types=1);

namespace PetStore\Presenters\Components\Grid\Data;

/**
 * Class GridColumnActionData
 *
 * @package PetStore\Presenters\Components\Grid\Data
 * @author  Zsolt Döme
 * @since   2024
 */
final readonly class GridColumnActionData
{
    /**
     * Constructor.
     *
     * @param string $icon
     * @param string $title
     * @param string $url
     * @param string $styles
     */
    public function __construct(
        public string $icon,
        public string $title,
        public string $url,
        public string $styles
    )
    {
    }

    /**
     * Factory method.
     *
     * @param string $icon
     * @param string $title
     * @param string $url
     * @param string $styles
     *
     * @return self
     */
    public static function create(string $icon, string $title, string $url, string $styles = ''): self
    {
        return new self($icon, $title, $url, $styles);
    }
}