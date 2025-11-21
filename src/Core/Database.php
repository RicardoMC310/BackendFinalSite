<?php

namespace App\Core;

use PDO;
use PDOException;
use App\Core\Response;

class Database
{
    static private ?PDO $conn = null;

    static function connect(): PDO
    {

        if (self::$conn == null) {
            $host = "localhost";
            $user = "root";
            $passwd = "";
            $database = "SiteFinal";

            try {
                self::$conn = new PDO(
                    "mysql:host=$host;dbname=$database;charset=utf8",
                    $user,
                    $passwd,
                    [
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]
                );
            } catch (PDOException $th) {
                $info = $th->errorInfo;

                Response::json("erro ao conectar com o banco", 500, [
                    "code" => $info[0],
                    "message" => $info[2]
                ]);
            }
        }

        return self::$conn;
    }

    static function sanitizeError(PDOException $th): string
    {
        $info = $th->errorInfo;

        return "COD[$info[0]]: $info[2]";
    }
}
