<?php
namespace App\Http\Controllers;

class GameHelper {
    
    public static function spawnPlayer($userData, $conn) {
        $player = ['createdAt' => time(), 'killed' => false ];
        array_push($userData['players'], $player);
        $conn->send(json_encode(['eventType' => 'spawn-player', 'data' => $userData]));
        return $userData;
    }

    public static function spawnEnemy($userData, $conn) {
        $enemy = ['createdAt' => time() ];
        array_push($userData['enemies'], $enemy);
        $conn->send(json_encode(['eventType' => 'spawn-enemy', 'data' => $userData]));
        return $userData;
    }

    public static function processGameStatus($userData, $conn) {
        $status = [
            'activePlayers' => 0,
            'killedPlayers' => 0,
            'enemies' => count($userData['enemies'])
        ];

        $currentTime = time();   // current time in seconds

        // find enemies which can kill at this specific time
        $enemies = $userData['enemies'];
        $lethalEnemies = [];
        foreach ($enemies as $enemy) {
            // enemies which are active after 2 mins
            if (($currentTime - $enemy['createdAt']) % 120 === 0 ) {
                array_push($lethalEnemies, $enemy);
            }
        }
        // if there is any active enemy then search for players which can be killed
        if (count($lethalEnemies) > 0) {
            $players = $userData['players'];
            foreach ($players as $key => $player) {
                // only check those players which are not killed
                if (!$player['killed']) {
                    $age = $currentTime - $player['createdAt'];
                    // check if player is 60 secs (1 min) old and it is not in 15 sec super state
                    if ($age > 60 && ($age % 60) > 0 && ($age % 60) < 16 ) {
                        $status['activePlayers']++;
                    } else {
                        $status['killedPlayers']++;
                        $userData['players'][$key]['killed'] = true;    // mark player as killed
                    }
                } else {
                    $status['killedPlayers']++;
                }
            }
        } else {
            $status['activePlayers'] = count($userData['players']);
        }
        $conn->send(json_encode(['eventType' => 'get-status', 'data' => $status, 'userData' => $userData]));
        return $userData;
    }
}