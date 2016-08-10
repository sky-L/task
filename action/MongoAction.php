<?php
namespace Action;

require "BaseAction.php";

use League\Monga;

class MongoAction extends BaseAction
{
    /**
     *
     */
    public function index()
    {
        $connection = Monga::connection();

        $database = $connection->database('sky_1');

        // Get a collection
        $collection = $database->collection('collection_name');

// Drop the collection
       // $collection->drop();

// Truncate the collection
       // $collection->truncate();

// Insert some values into the collection
        $insertIds = $collection->insert([
            [
                'name' => 'John',
                'surname' => 'Doe',
                'nick' => 'The Unknown Man',
                'age' => 20,
            ],
            [
                'name' => 'Frank',
                'surname' => 'de Jonge',
                'nick' => 'Unknown',
                'nik' => 'No Man',
                'age' => 23,
            ],
        ]);
        echo "<pre>";
        print_r($insertIds);
exit(__FILE__ . __LINE__);

// Update a collection
        $collection->update(function ($query) {
            $query->increment('age')
                ->remove('nik')
                ->set('nick', 'FrenkyNet');
        });

// Find Frank
        $frank = $collection->findOne(function ($query) {
            $query->where('name', 'Frank')
                ->whereLike('surname', '%e Jo%');
        });

// Or find him using normal array syntax
        $frank = $collection->find([
            'name' => 'Frank',
            'surname' => new MongoRegex('/e Jo/imxsu')
        ]);

        $frank['age']++;

        $collection->save($frank);

// Also supports nested queries
        $users = $collection->find(function ($query) {
            $query->where(function ($query) {
                $query->where('name', 'Josh')
                    ->orWhere('surname', 'Doe');
            })->orWhere(function () {
                $query->where('name', 'Frank')
                    ->where('surname', 'de Jonge');
            });
        });

// get the users as an array
        $arr = $users->toArray();

        var_dump($connection);
    }
}


$obj = new MongoAction();

$obj->index();