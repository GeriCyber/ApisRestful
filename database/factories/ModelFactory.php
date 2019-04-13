<?php

use App\User;
use App\Seller;
use App\Product;
use App\Category;
use App\Transaction;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
	static $password;
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => $password ?: $password = bcrypt('apisrest'), // password
        'remember_token' => Str::random(10),
        'verified' => $verified = $faker->randomElement([User::USUARIO_VERIFICADO, User::USUARIO_NO_VERIFICADO]),
        'verification_token' => $verified == User::USUARIO_VERIFICADO ? null : User::generateVerificationToken(),
        'verified' => $faker->randomElement([User::USUARIO_ADMINISTRADOR, User::USUARIO_REGULAR]),
    ];
});

$factory->define(Category::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
     	'description' => $faker->paragraph(1),
    ];
});

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
     	'description' => $faker->paragraph(1),
     	'quantity' => $faker->numberBetween(1, 10),
     	'status' => $faker->randomElement([Product::PRODUCTO_DISPONIBLE, Product::PRODUCTO_NO_DISPONIBLE]),
     	'image' => $faker->randomElement(['1.jpg', '2.jpg', '3.jpg']),
     	/*'seller_id' => User::inRandomOrder()->first()->id*/
     	'seller_id' => User::all()->random()->id,
    ];
});

$factory->define(Transaction::class, function (Faker $faker) {

	$seller = Seller::has('products')->get()->random();
	$buyer = User::all()->except($seller->id)->random();

    return [
     	'quantity' => $faker->numberBetween(1, 3),
     	'buyer_id' => $buyer->id,
     	'product_id' => $seller->products->random()->id,
    ];
});