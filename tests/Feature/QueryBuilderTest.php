<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\CounterSeeder;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from products');
        DB::delete("delete from categories");
        DB::delete("delete from counters");
    }

    public function testInsert()
    {
        DB::table("categories")->insert([
            "id" => "GADGET",
            "name" => "Gadget"
        ]);
        DB::table("categories")->insert([
            "id" => "FOOD",
            "name" => "Food"
        ]);

        $result = DB::select("select count(id) as total from categories");
        self::assertEquals(2, $result[0]->total);
    }

    public function testSelect()
    {
        $this->testInsert();

        $collection = DB::table('categories')->select(['id', 'name'])->get();
        self::assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function insertCategories()
    {
        $this->seed(CategorySeeder::class);
    }

    public function testWhere()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->where(function (Builder $builder) {
            // SELECT * FROM categories WHERE (id = SMARTPHONE OR id = LAPTOP)
            $builder->where('id', '=', 'SMARTPHONE');
            $builder->orWhere('id', '=', 'LAPTOP');
        })->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereBetween()
    {
        $this->insertCategories();

        $collection = DB::table('categories')
            ->whereBetween('created_at', ['2020-10-09 10:10:10', '2020-10-10 10:10:10'])
            ->get();

        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereIn()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereIn('id', ['SMARTPHONE', 'LAPTOP'])->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereNull('description')->get();

        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereDate('created_at', '2020-10-10')->get();

        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpdate()
    {
        $this->insertCategories();

        DB::table('categories')->where('id', '=', 'SMARTPHONE')->update([
            'name' => 'Handphone'
        ]);

        $collection = DB::table('categories')->where('name', '=', 'Handphone')->get();
        self::assertCount(1, $collection);
    }

    public function testUpdateOrInsert()
    {
        DB::table('categories')->updateOrInsert([
            'id' => 'VOUCHER'
        ], [
            'name' => 'Voucher',
            'description' => 'Ticket and Voucher',
            'created_at' => '2020-10-10 10:10:10'
        ]);

        $collection = DB::table('categories')->where('id', '=', 'VOUCHER')->get();
        self::assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testIncrement()
    {
        $this->seed(CounterSeeder::class);

        DB::table('counters')->where('id', '=', 'sample')->increment('counter', 1);

        $collection = DB::table('counters')->where('id', '=', 'sample')->get();
        self::assertCount(1, $collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testDelete()
    {
        $this->insertCategories();

        DB::table('categories')->where('id', '=', 'SMARTPHONE')->delete();

        $collection = DB::table('categories')->where('id', '=', 'SMARTPHONE')->get();
        self::assertCount(0, $collection);
    }

    public function insertProducts()
    {
        $this->insertCategories();

        DB::table('products')->insert([
            'id' => '1',
            'name' => 'Iphone 15',
            'category_id' => 'SMARTPHONE',
            'price' => 20000
        ]);
        DB::table('products')->insert([
            'id' => '2',
            'name' => 'Samsung S20',
            'category_id' => 'SMARTPHONE',
            'price' => 10000
        ]);
    }

    public function testJoin()
    {
        $this->insertProducts();

        $collection = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.name', 'products.price', 'categories.name as category_name')
            ->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testOrdering()
    {
        $this->insertProducts();

        $collection = DB::table('products')->whereNotNull('id')
            ->orderBy('price', 'desc')->orderBy('name', 'asc')->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testPaging()
    {
        $this->insertCategories();

        $collection = DB::table('categories')
            ->skip(0) // page pertama
            ->take(2)
            ->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testChunkResults()
    {
        $this->insertCategories();

        DB::table('categories')
            ->orderBy('id')
            ->chunk(1, function ($categories) {
                self::assertNotNull($categories);
                foreach ($categories as $category) {
                    Log::info(json_encode($category));
                }
            });
    }

    public function testLazyResults()
    {
        $this->insertCategories();

        $collection = DB::table('categories')
            ->orderBy('id')
            ->lazy(2)
            ->take(1);

        self::assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testCursorResults()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->orderBy('id')->cursor();

        self::assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testAggregate()
    {
        $this->insertProducts();

        $result = DB::table('products')->count('id');
        self::assertEquals(2, $result);

        $result = DB::table('products')->min('price');
        self::assertEquals(10000, $result);

        $result = DB::table('products')->max('price');
        self::assertEquals(20000, $result);

        $result = DB::table('products')->avg('price');
        self::assertEquals(15000, $result);

        $result = DB::table('products')->sum('price');
        self::assertEquals(30000, $result);
    }

    public function testQueryBuilderRaw()
    {
        $this->insertProducts();

        $collection = DB::table('products')
            ->select(
                DB::raw('count(id) as total_product'),
                DB::raw('min(price) as min_price'),
                DB::raw('max(price) as max_price'),
            )->get();

        self::assertEquals(2, $collection[0]->total_product);
        self::assertEquals(10000, $collection[0]->min_price);
        self::assertEquals(20000, $collection[0]->max_price);
    }

    public function insertProductFood()
    {
        DB::table('products')->insert([
            'id' => '3',
            'name' => 'Bakso',
            'category_id' => 'FOOD',
            'price' => 2000
        ]);
        DB::table('products')->insert([
            'id' => '4',
            'name' => 'Mie Ayam',
            'category_id' => 'FOOD',
            'price' => 2000
        ]);
    }

    public function testGroupBy()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $collection = DB::table('products')
            ->select('category_id', DB::raw('count(*) total_product'))
            ->groupBy('category_id')
            ->orderBy('category_id', 'desc')
            ->get();

        self::assertCount(2, $collection);
        self::assertEquals('SMARTPHONE', $collection[0]->category_id);
        self::assertEquals('FOOD', $collection[1]->category_id);
        self::assertEquals(2, $collection[0]->total_product);
        self::assertEquals(2, $collection[1]->total_product);
    }

    public function testGroupByHaving()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $collection = DB::table('products')
            ->select('category_id', DB::raw('count(*) total_product'))
            ->groupBy('category_id')
            ->orderBy('category_id', 'desc')
            ->having(DB::raw('count(*)'), '>', 2)
            ->get();

        self::assertCount(0, $collection);
    }

    public function testLocking()
    {
        $this->insertProducts();

        DB::transaction(function () {
            $collection = DB::table('products')
                ->where('id', '=', '1')
                ->lockForUpdate()
                ->get();
            self::assertCount(1, $collection);
        });
    }

    public function testPagination()
    {
        $this->insertCategories();

        $paginate = DB::table('categories')->paginate(perPage: 2, page: 2);

        self::assertEquals(2, $paginate->currentPage());
        self::assertEquals(2, $paginate->perPage());
        self::assertEquals(2, $paginate->lastPage());
        self::assertEquals(4, $paginate->total());

        $collection = $paginate->items();
        self::assertCount(2, $collection);
        foreach ($collection as $item) {
            Log::info(json_encode($item));
        }
    }

    public function testIterateAllPagination()
    {
        $this->insertCategories();

        $page = 1;

        while (true) {
            $paginate = DB::table('categories')->paginate(perPage: 2, page: $page);

            if ($paginate->isEmpty()) {
                break;
            } else {
                $page++;

                $collection = $paginate->items();
                self::assertCount(2, $collection);
                foreach ($collection as $item) {
                    Log::info(json_encode($item));
                }
            }
        }
    }

    public function testCursorPagination()
    {
        $this->insertCategories();

        $cursor = 'id';
        while (true) {
            $paginate = DB::table('categories')->orderBy('id')->cursorPaginate(perPage: 2, cursor: $cursor);

            foreach ($paginate->items() as $item) {
                self::assertNotNull($item);
                Log::info(json_encode($item));
            }

            $cursor = $paginate->nextCursor();
            if ($cursor == null) {
                break;
            }
        }
    }
}
