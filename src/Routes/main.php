<?php

use App\Core\Router;

Router::get("/", "HomeController@index");
Router::post("/", "HomeController@create");
Router::delete("/", "HomeController@delete");