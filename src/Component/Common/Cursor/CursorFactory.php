<?php

namespace Misery\Component\Common\Cursor;

use Misery\Component\Common\Registry\RegisteredByNameInterface;

class CursorFactory implements RegisteredByNameInterface
{
    public function createFromName(string $name, CursorInterface $prevCursor): CursorInterface
    {
        switch ($name) {
            case 'cached_cursor':
                return new CachedCursor($prevCursor);
            case 'functional_cursor':
            // we need to pass a callable function for this to work
            //    return new FunctionalCursor($cursor);
            default:
                throw new \Exception(sprintf('Cursor by name %s not found', $name));
        }
    }

    public function getName(): string
    {
        return 'cursor';
    }
}