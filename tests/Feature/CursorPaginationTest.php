<?php

namespace Tests\Feature;

use App\Models\BorrowingTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CursorPaginationTest extends TestCase
{
    public function test_cursor_pages_preserve_ties_and_move_back_without_offsets(): void
    {
        Schema::create('borrowing_transactions', function (Blueprint $table) {
            $table->id();
            $table->dateTime('due_date');
        });
        foreach (range(1, 5) as $id) {
            DB::table('borrowing_transactions')->insert(['id' => $id, 'due_date' => '2026-11-01 17:00:00']);
        }

        $query = fn () => BorrowingTransaction::query()->orderBy('due_date')->orderBy('id');
        $first = $query()->cursorPaginate(2);
        DB::enableQueryLog();
        $second = $query()->cursorPaginate(2, ['*'], 'cursor', $first->nextCursor());
        $sql = strtolower(DB::getQueryLog()[0]['query']);

        $this->assertSame([1, 2], $first->pluck('id')->all());
        $this->assertSame([3, 4], $second->pluck('id')->all());
        $this->assertStringNotContainsString('offset', $sql);
        $this->assertSame([1, 2], $query()->cursorPaginate(2, ['*'], 'cursor', $second->previousCursor())->pluck('id')->all());
        $this->assertSame([5], $query()->cursorPaginate(2, ['*'], 'cursor', $second->nextCursor())->pluck('id')->all());
    }
}
