<?php
namespace App\Controllers;

use App\Database\Database;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use PDOException;
use App\Security\Auth;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class RankingController {
    private $logger;

    public function __construct() {
        $this->logger = new Logger('ranking_logger');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG));
    }

    public function getRanking(Request $request, Response $response, array $args): Response {
        if (!Auth::verifyToken($request)) {
            return $this->jsonResponse($response, ["error" => "Acesso não autorizado."], 401);
        }

        $movement_id = filter_var($args['movement_id'], FILTER_VALIDATE_INT);
        if (!$movement_id) {
            return $this->jsonResponse($response, ["error" => "ID inválido."], 400);
        }

        try {
            $db = new Database();
            $conn = $db->connect();

            $stmt = $conn->prepare("SELECT name FROM movement WHERE id = :movement_id");
            $stmt->bindParam(":movement_id", $movement_id, PDO::PARAM_INT);
            $stmt->execute();
            $movement = $stmt->fetch(PDO::FETCH_ASSOC);
            $movement_name = $movement['name'] ?? null;

            if (!$movement_name) {
                return $this->jsonResponse($response, ["error" => "Movimento não encontrado."], 404);
            }

            $cacheKey = "ranking_{$movement_id}";
            $cachedData = apcu_fetch($cacheKey);
            if ($cachedData) {
                return $this->jsonResponse($response, json_decode($cachedData, true));
            }

            $stmt = $conn->prepare("SELECT u.name AS user_name, MAX(pr.value) AS personal_record, MIN(pr.date) AS date FROM personal_record pr JOIN user u ON pr.user_id = u.id WHERE pr.movement_id = :movement_id GROUP BY u.id, u.name ORDER BY personal_record DESC, date ASC");
            $stmt->bindParam(":movement_id", $movement_id, PDO::PARAM_INT);
            $stmt->execute();
            $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $position = 1;
            $last_record = null;
            $real_position = 1;

            foreach ($ranking as &$record) {
                if ($last_record !== null && $record['personal_record'] < $last_record) {
                    $real_position = $position;
                }
                $record['position'] = $real_position;
                $last_record = $record['personal_record'];
                $position++;
            }

            $responseData = [
                "movement_name" => $movement_name,
                "ranking" => $ranking
            ];
            apcu_store($cacheKey, json_encode($responseData), 300);
            return $this->jsonResponse($response, $responseData);
        } catch (PDOException $e) {
            $this->logger->error("Erro ao acessar o banco de dados: " . $e->getMessage());
            return $this->jsonResponse($response, ["error" => "Erro interno no servidor."], 500);
        }
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
