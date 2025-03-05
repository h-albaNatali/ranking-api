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
        $movementId = $args['movement_id'];

        try {
            // Validação de entrada
            $this->validateMovementId($movementId);

            // Verifica se o movimento existe
            $stmtCheckMovement = $db->prepare("SELECT 1 FROM movement WHERE id = :movement_id");
            $stmtCheckMovement->execute(['movement_id' => $movementId]);
            $movementExists = $stmtCheckMovement->fetchColumn();
            if (!$movementExists) {
                $response->getBody()->write(json_encode(["error" => "O movimento solicitado não existe."]));
                return $response->withHeader('Content-Type', 'application/json')
                                ->withStatus(404);
                            }

            $cacheFile = __DIR__ . "/../../cache/ranking_{$movementId}.json";
            if ($this->cacheEnabled && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTime) {
                $response->getBody()->write(file_get_contents($cacheFile));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }

            // Consulta os registros
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
                                    best_score DESC;
                                ");
            $stmt->execute(['movement_id' => $movementId]);
            $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Se não houver registros, retorna mensagem informando que está vazio
            if (empty($ranking)) {
                $response->getBody()->write(json_encode(["message" => "Nenhum registro encontrado para o movimento solicitado."]));
                return $response->withHeader('Content-Type', 'application/json')
                                ->withStatus(200);
                            }

            // Ajustar a posição no ranking para usuários com o mesmo valor ocuparem a mesma posição
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
            $this->logger->error("Erro ao buscar ranking", [
                'exception' => $e,
                'movement_id' => $movementId,
                'query' => 'SELECT movement.name ...',
                'params' => ['movement_id' => $movementId]
            ]);

            $message = ($_ENV['APP_ENV'] === 'production') ? 
                "Erro ao buscar ranking." : 
                "Erro ao buscar ranking: " . $e->getMessage();

            $response->getBody()->write(json_encode(['error' => $message]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    private function validateMovementId($movementId) {
        if (!is_numeric($movementId) || $movementId <= 0) {
            throw new \InvalidArgumentException("ID do movimento inválido: {$movementId}");
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
