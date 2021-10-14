<?php
/**
 * Box packing (3D bin packing, knapsack problem).
 *
 * @author Doug Wright
 */
declare(strict_types=1);

namespace DVDoug\BoxPacker;

use DVDoug\BoxPacker\Item;
use JsonSerializable;
use ReturnTypeWillChange;

class EroumItem implements Item, JsonSerializable
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $ct_id;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var int
     */
    private $weight;

    /**
     * @var int
     */
    private $keepFlat;

    /**
     * TestItem constructor.
     */
    public function __construct(
        string $description,
        string $ct_id,
        int $width,
        int $length,
        int $depth,
        int $weight,
        bool $keepFlat
    ) {
        $this->description = $description;
        $this->ct_id = $ct_id;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
        $this->weight = $weight;
        $this->keepFlat = $keepFlat;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCtId(): string
    {
        return $this->ct_id;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getKeepFlat(): bool
    {
        return !!$this->keepFlat;
    }

    #[ReturnTypeWillChange]
    public function jsonSerialize()/*: mixed*/
    {
        return [
            'description' => $this->description,
            'ct_id' => $this->ct_id,
            'width' => $this->width,
            'length' => $this->length,
            'depth' => $this->depth,
            'weight' => $this->weight,
            'keepFlat' => $this->keepFlat,
        ];
    }
}
