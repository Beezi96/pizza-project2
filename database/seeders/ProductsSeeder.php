<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /// Получаем категории по code
        $pizzaCategory = Category::where('code', 'pizza')->first();
        $drinkCategory = Category::where('code', 'drink')->first();

        $pizzas = [
            [
                'name' => 'Маргарита',
                'price' => 450,
                'description' => 'Томатный соус, моцарелла, базилик',
            ],
            [
                'name' => 'Пепперони',
                'price' => 550,
                'description' => 'Томатный соус, пепперони, сыр',
            ],
            [
                'name' => '4 сыра',
                'price' => 600,
                'description' => 'Моцарелла, дорблю, пармезан, чеддер',
            ],
            [
                'name' => 'Гавайская',
                'price' => 580,
                'description' => 'Курица, ананасы, сыр',
            ],
            [
                'name' => 'Ветчина и грибы',
                'price' => 520,
                'description' => 'Ветчина, шампиньоны, сыр',
            ],
        ];

        foreach ($pizzas as $pizza) {
            Product::create([
                'name' => $pizza['name'],
                'price' => $pizza['price'],
                'description' => $pizza['description'],
                'img' => null,
                'category_id' => $pizzaCategory->id,
            ]);
        }

        $drinks = [
            [
              'name' => 'Кола',
              'price' => 150,
              'description' => 'Газированный напиток',
            ],
            [
                'name' => 'Спрайт',
                'price' => 150,
                'description' => 'Лимонно-лаймовый напиток',
            ],
            [
                'name' => 'Фанта',
                'price' => 150,
                'description' => 'Апельсиновый напиток',
            ],
            [
                'name' => 'Вода',
                'price' => 100,
                'description' => 'Минеральная вода',
            ],
            [
                'name' => 'Сок',
                'price' => 180,
                'description' => 'Фруктовый сок',
            ],
        ];

        foreach ($drinks as $drink) {
            Product::create([
                'name' => $drink['name'],
                'price' => $drink['price'],
                'description' => $drink['description'],
                'img' => null,
                'category_id' => $drinkCategory->id,
            ]);
        }
    }
}
