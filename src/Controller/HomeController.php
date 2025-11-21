<?php

namespace App\Controller;

use PDO;
use App\Core\Response;
use App\Core\Database;
use PDOException;

class HomeController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function index(array $request)
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name, pathImage, price, description, quantity, createdAt, updatedAt FROM guitars");
            $stmt->execute();

            $result = $stmt->fetchAll();

            Response::json("todas as guitarras no sistema", 200, $result);
        } catch (PDOException $th) {
            Response::json("erro ao consultar dados", 500, [
                "error" => Database::sanitizeError($th)
            ]);
        }
    }

    public function create(array $request)
    {
        $body = $request["post"];
        $files = $request["files"];

        $fileName = $this->saveImageFromServer($files["image"]);

        try {
            $stmt = $this->db->prepare("INSERT INTO guitars (name, pathImage, price, description, quantity) VALUES(?, ?, ?, ?, ?)");
            $stmt->execute([
                $body["name"],
                $fileName,
                $body["price"],
                $body["description"],
                $body["quantity"]
            ]);
        } catch (PDOException $th) {
            Response::json("erro ao salvar dados", 500, [
                "error" => Database::sanitizeError($th)
            ]);
        }

        Response::json("guitarra salva com sucesso", 204);
    }

    public function delete(array $request)
    {
        $get = $request["get"];

        try {
            $stmt = $this->db->prepare("SELECT pathImage FROM guitars WHERE id = ?");
            $stmt->execute([$get["id"]]);

            $result = $stmt->fetch();

            if (!$result) {
                Response::json("");
            }

            $this->deleteImage($result["pathImage"]);

            $stmt = $this->db->prepare("DELETE FROM guitars WHERE id = ?");
            $stmt->execute([$get["id"]]);

            Response::json("item deletado com sucesso", 200);

        } catch (PDOException $th) {
            Response::json("erro ao deletar dados", 500, [
                "error" => Database::sanitizeError($th)
            ]);
        }
    }

    private function saveImageFromServer(array $file)
    {

        if (!isset($file)) {
            Response::json("nenhuma imagem enviada", 400);
        }

        $name = $file["name"];
        $tmpPath = $file["tmp_name"];
        $size = $file["size"];
        $error = $file["error"];
        $type = $file["type"];

        if ($error != UPLOAD_ERR_OK) {
            Response::json("falha no upload", 400, [
                "error" => $error
            ]);
        }

        $allowMimes = ["image/png", "image/jpg", "image/jpeg"];
        $allowExt = ["jpg", "png", "jpeg"];
        $allowSize = 5 * 1024 * 1024;

        if (!in_array($type, $allowMimes)) {
            Response::json("MimeType não suportado", 400);
        }

        $extFile = pathinfo($name, PATHINFO_EXTENSION);

        if (!in_array($extFile, $allowExt)) {
            Response::json("Extensão não suportado", 400);
        }

        if ($size > $allowSize) {
            Response::json("Imagem muito pesada, máx de {$allowSize} bytes", 400);
        }

        $destDir = __DIR__ . "/../Uploads/";

        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }

        $newName = uniqid("img_", true) . "." . $extFile;
        $destPath = $destDir . $newName;

        if (!move_uploaded_file($tmpPath, $destPath)) {
            Response::json("erro ao fazer upload", 500);
        }

        return $newName;
    }

    private function deleteImage(string $filename) {
        $path = __DIR__ . "/../Uploads/" . $filename;

        if (file_exists($path)) {
            unlink($path);
        }
    }
}
