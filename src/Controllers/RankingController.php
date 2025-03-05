<?php

namespace App\Controllers;

use App\Database\Database;
use Psr\Http\Message\ResponseInterface as Response;
use PDO;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class RankingController {
    private $logger;
    private $cacheEnabled;
    private $cacheTime;

    public function __construct() {
        $this->logger = new Logger('ranking_api');
        $this->logger->pushHandler(new StreamHandler(__DIR__.'/../../logs/error.log', Logger::ERROR));
        $this->cacheEnabled = getenv('CACHE_ENABLED') === 'true';
        $this->cacheTime = (int) getenv('CACHE_TIME');
    }

    public function getRanking($request, $response, $args) {
        $db = Database::getInstance()->getConnection();
        $movementId = filter_var($args['movement_id'], FILTER_VALIDATE_INT);

        if (!$movementId || $movementId <= 0) {
            $response->getBody()->write(json_encode(["error" => "ID do movimento inválido."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $stmtCheckMovement = $db->prepare("SELECT COUNT(*) FROM movement WHERE id = :movement_id");
            $stmtCheckMovement->execute(['movement_id' => $movementId]);
            if ($stmtCheckMovement->fetchColumn() == 0) {
                $response->getBody()->write(json_encode(["error" => "O movimento solicitado não existe."]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            $cacheFile = __DIR__ . "/../../cache/ranking_{$movementId}.json";
            if ($this->cacheEnabled && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTime) {
                $response->getBody()->write(file_get_contents($cacheFile));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }

            $stmt = $db->prepare("SELECT movement.name AS movement_name, 
                                        user.name, 
                                        MAX(personal_record.value) AS best_score, 
                                        MAX(personal_record.date) AS record_date
                                    FROM 
                                        personal_record
                                    JOIN 
                                        user ON personal_record.user_id = user.id
                                    JOIN 
                                        movement ON personal_record.movement_id = movement.id
                                    WHERE 
                                        personal_record.movement_id = :movement_id
                                    GROUP BY 
                                        user.id, movement.name
                                    ORDER BY 
                                        best_score DESC;");
            $stmt->execute(['movement_id' => $movementId]);
            $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($ranking)) {
                $response->getBody()->write(json_encode(["message" => "Nenhum registro encontrado para o movimento solicitado."]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }

            $ranking = $this->adjustRankingPositions($ranking);

            if ($this->cacheEnabled) {
                $cacheDir = __DIR__ . "/../../cache";
                if (!is_dir($cacheDir)) {
                    mkdir($cacheDir, 0755, true);
                }
                file_put_contents($cacheFile, json_encode($ranking));
            }

            $response->getBody()->write(json_encode($ranking));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $this->logger->error("Erro ao buscar ranking", ['exception' => $e, 'movement_id' => $movementId]);
            $response->getBody()->write(json_encode(['error' => 'Erro ao buscar ranking.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    private function adjustRankingPositions(array $ranking) {
        $rank = 1;
        $prev_score = null;
        foreach ($ranking as $key => &$row) {
            if ($prev_score !== null && $row['best_score'] < $prev_score) {
                $rank = $key + 1;
            }
            $row['rank'] = $rank;
            $prev_score = $row['best_score'];
        }
        return $ranking;
    }
}
