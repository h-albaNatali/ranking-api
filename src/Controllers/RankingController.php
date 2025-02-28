<?php
namespace App\Controllers;

use App\Database\Database;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class RankingController {
    public function getRanking(Request $request, Response $response, array $args): Response {
        $movement_id = $args['movement_id'];

        $db = new Database();
        $conn = $db->connect();

        $movementQuery = "SELECT name FROM movement WHERE id = :movement_id";
        $stmt = $conn->prepare($movementQuery);
        $stmt->bindParam(":movement_id", $movement_id, PDO::PARAM_INT);
        $stmt->execute();
        $movement = $stmt->fetch(PDO::FETCH_ASSOC);
        $movement_name = $movement['name'] ?? null;

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

        if (empty($ranking)) {
            $error = [
                "movement_name" => $movement_name ?? "Movimento não encontrado",
                "error" => "Nenhum ranking encontrado para esse movimento."
            ];
            $response->getBody()->write(json_encode($error, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

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

        $result = [
            "movement_name" => $movement_name,
            "ranking" => $ranking
        ];

        $response->getBody()->write(json_encode($result, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
