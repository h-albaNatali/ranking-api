<?php
namespace App\Controllers;

use App\Database\Database;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use PDOException;

class RankingController {
    public function getRanking(Request $request, Response $response, array $args): Response {
        $movement_id = intval($args['movement_id']); // Garante que é um número inteiro

        try {
            $db = new Database();
            $conn = $db->connect();

            // Obtém o nome do movimento
            $movementQuery = "SELECT name FROM movement WHERE id = :movement_id";
            $stmt = $conn->prepare($movementQuery);
            $stmt->bindParam(":movement_id", $movement_id, PDO::PARAM_INT);
            $stmt->execute();
            $movement = $stmt->fetch(PDO::FETCH_ASSOC);
            $movement_name = $movement['name'] ?? null;

            // Se o movimento não existir, retorna erro 404
            if (!$movement_name) {
                return $this->jsonResponse($response, [
                    "error" => "Movimento não encontrado."
                ], 404);
            }

            // Obtém o ranking do movimento
            $query = "SELECT 
                        u.name AS user_name, 
                        MAX(pr.value) AS personal_record, 
                        MIN(pr.date) AS date
                      FROM personal_record pr
                      JOIN user u ON pr.user_id = u.id
                      WHERE pr.movement_id = :movement_id
                      GROUP BY u.id, u.name
                      ORDER BY personal_record DESC, date ASC";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(":movement_id", $movement_id, PDO::PARAM_INT);
            $stmt->execute();
            $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ajusta posição no ranking considerando empates
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

            // Retorna resposta estruturada
            return $this->jsonResponse($response, [
                "movement_name" => $movement_name,
                "ranking" => $ranking
            ]);

        } catch (PDOException $e) {
            return $this->jsonResponse($response, [
                "error" => "Erro ao acessar o banco de dados."
            ], 500);
        }
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
