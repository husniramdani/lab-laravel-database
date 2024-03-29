<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from categories');
    }

    public function testTransactionSuccess()
    {
        DB::transaction(function () {
            DB::insert('insert into categories(id, name, description, created_at) values (?, ?, ?, ?)', [
                "GADGET", "Gadget", "Gadget Category", "2020-10-10 10:10:10"
            ]);
            DB::insert('insert into categories(id, name, description, created_at) values (?, ?, ?, ?)', [
                "FOOD", "Food", "Food Category", "2020-10-10 10:10:10"
            ]);
        });

        $result = DB::select('select * from categories');
        self::assertCount(2, $result);
    }

    public function testTransactionFailed()
    {
        try {
            DB::transaction(function () {
                DB::insert('insert into categories(id, name, description, created_at) values (?, ?, ?, ?)', [
                    "GADGET", "Gadget", "Gadget Category", "2020-10-10 10:10:10"
                ]);
                DB::insert('insert into categories(id, name, description, created_at) values (?, ?, ?, ?)', [
                    "GADGET", "Food", "Food Category", "2020-10-10 10:10:10"
                ]);
            });
        } catch (QueryException $error) {
            //throw $th;
        }

        $result = DB::select('select * from categories');
        self::assertCount(0, $result);
    }

    public function testManualTransaction()
    {
        try {
            DB::beginTransaction();
            DB::insert('insert into categories(id, name, description, created_at) values (?, ?, ?, ?)', [
                "GADGET", "Gadget", "Gadget Category", "2020-10-10 10:10:10"
            ]);
            DB::insert('insert into categories(id, name, description, created_at) values (?, ?, ?, ?)', [
                "FOOD", "Food", "Food Category", "2020-10-10 10:10:10"
            ]);
            DB::commit();
        } catch (QueryException $error) {
            DB::rollBack();
        }

        $result = DB::select('select * from categories');
        self::assertCount(2, $result);
    }
}
