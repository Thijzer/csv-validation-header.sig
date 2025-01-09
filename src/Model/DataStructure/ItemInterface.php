<?php

namespace Misery\Model\DataStructure;

/**
 * Interface ItemObject
 *
 * Represents a data structure for managing items with various operations such as adding, editing, copying,
 * moving, and retrieving items. Each item is identified by a unique code and can hold associated data values.
 */
interface ItemInterface
{
    /**
     * Adds a new item or updates an existing item identified by its code.
     *
     * @param string $code The unique code for the item.
     * @param mixed $itemValue The value associated with the item.
     * @param array $context Additional context or metadata for the item (optional).
     * @return void
     */
    public function addItem(string $code, mixed $itemValue, array $context = []): void;

    /**
     * Copies the value of an existing item to a new item identified by a different code.
     *
     * @param string $fromCode The code of the item to copy from.
     * @param string $toCode The code of the item to copy to.
     * @return void
     */
    public function copyItem(string $fromCode, string $toCode): void;

    /**
     * Moves the value of an existing item to a new item, removing the original.
     *
     * @param string $fromCode The code of the item to move from.
     * @param string $toCode The code of the item to move to.
     * @return void
     */
    public function moveItem(string $fromCode, string $toCode): void;

    /**
     * Edits the value of an existing item identified by its code.
     *
     * @param string $code The unique code of the item.
     * @param mixed $dataValue The new value to assign to the item.
     * @return void
     */
    public function editItemValue(string $code, mixed $dataValue): void;

    /**
     * Removes an item identified by its unique code.
     *
     * @param string $code The unique code of the item to remove.
     * @return void
     */
    public function removeItem(string $code): void;

    /**
     * Checks if an item exists for the given code.
     *
     * @param string $code The unique code to check.
     * @return bool True if the item exists, false otherwise.
     */
    public function hasItem(string $code): bool;

    /**
     * Retrieves an item by its unique code.
     *
     * @param string $codeOrMatch The unique code or a match of the item.
     * @return ItemNode|null The item object or null if the item does not exist.
     */
    public function getItem(string $codeOrMatch): ?ItemNode;

    /**
     * Retrieves all items in the structure.
     *
     * @return ItemNode[] An array of all items.
     */
    public function getItemNodes(): array;

    /**
     * Retrieves only the codes
     * @return array
     */
    public function getItemCodes(): array;
    /**
     * Finds an item matching the given criteria.
     *
     * @param string $match The criteria to match against.
     * @return ItemNode|null The first matching item or null if no match is found.
     */
    public function getItemByMatch(string $match): ?ItemNode;

    /**
     * Retrieves items that match the given criteria as a generator.
     *
     * @param string $match The criteria to match against.
     * @return \Generator<ItemNode> A generator yielding matching items.
     */
    public function getItemsByMatch(string $match): \Generator;

    /**
     * Retrieves items by their type as a generator.
     *
     * @param string $type The type to filter items by.
     * @return \Generator<ItemNode> A generator yielding items of the given type.
     */
    public function getItemsByType(string $type): \Generator;
}
