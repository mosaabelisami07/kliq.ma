<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Moves a record one place up or down in a sort_order list.
 */
class Reorder
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function move(string $modelClass, Model $record, string $direction): void
    {
        DB::transaction(function () use ($modelClass, $record, $direction): void {
            $ids = $modelClass::query()->orderBy('sort_order')->orderBy('id')->pluck('id')->values()->all();
            $position = array_search($record->getKey(), $ids, true);
            $target = $direction === 'up' ? $position - 1 : $position + 1;

            if ($position === false || ! isset($ids[$target])) {
                return;
            }

            [$ids[$position], $ids[$target]] = [$ids[$target], $ids[$position]];

            foreach ($ids as $order => $id) {
                $modelClass::query()->whereKey($id)->update(['sort_order' => $order]);
            }
        });
    }

    /**
     * The sort_order for a new record placed at the end of the list.
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function next(string $modelClass): int
    {
        return (int) $modelClass::query()->max('sort_order') + 1;
    }
}
