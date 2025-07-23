<?php

use Illuminate\Support\Facades\Route;
use App\Jobs\TestKafkaJob;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-kafka', function () {
    TestKafkaJob::dispatch('Test message ' . now())
        ->onConnection('kafka')
        ->onQueue('test-topic');
        
    return 'Job dispatched to Kafka!';
});
