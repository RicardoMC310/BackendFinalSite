<?php

namespace App\Core;

class Router {
    static private array $routes = [];

    static function get(string $path, string $action): void {
        self::$routes[] = [
            "path" => $path,
            "action" => $action,
            "method" => "GET"
        ];
    }

    static function post(string $path, string $action): void {
        self::$routes[] = [
            "path" => $path,
            "action" => $action,
            "method" => "POST"
        ];
    }

    static function put(string $path, string $action): void {
        self::$routes[] = [
            "path" => $path,
            "action" => $action,
            "method" => "PUT"
        ];
    }

    static function delete(string $path, string $action): void {
        self::$routes[] = [
            "path" => $path,
            "action" => $action,
            "method" => "DELETE"
        ];
    }

    static function routes(): array {
        return self::$routes;
    }

    static function dispatch(string $path, string $method): void {

        foreach (self::$routes as $route) {
            if (strtoupper($method) != $route["method"] || $path != $route["path"]) {
                continue;
            }

            list($controllerName, $methodName) = explode("@", $route["action"]);

            $controllerClass = "App\\Controller\\" . $controllerName;

            if (!class_exists($controllerClass)) {
                http_response_code(500);
                echo "Controller {$controllerClass} not found.";
                return;
            }

            $controllerInstance = new $controllerClass;

            if (!method_exists($controllerInstance, $methodName))
            {
                http_response_code(500);
                echo "Method {$methodName} in {$controllerClass} not found.";
                return;
            }

            $request = [
                "get" => $_GET,
                "post" => $_POST,
                "body" => file_get_contents("php://input"),
                "header" => getallheaders(),
                "method" => $method,
                "path" => $path
            ];

            $controllerInstance->$methodName($request);
            return;
        }

        http_response_code(404);
        echo "Path not found";

    }
}