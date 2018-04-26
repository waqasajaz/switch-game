<?php
namespace App\Http\Controllers;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketController extends Controller implements MessageComponentInterface{
    private $connections = [];
    
     /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn) {
        $this->connections[$conn->resourceId] = compact('conn') + ['user_data' => [
            'players' => array(),
            'enemies' => array()
        ]];
    }
    
     /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn){
        $disconnectedId = $conn->resourceId;
        unset($this->connections[$disconnectedId]);
        foreach($this->connections as &$connection)
            $connection['conn']->send(json_encode([
                'offline_user' => $disconnectedId,
                'from_user_id' => 'server control',
                'from_resource_id' => null
            ]));
    }
    
     /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e){
        echo "An error has occurred with user $conn->resourceId]: {$e->getLine()}, {$e->getMessage()}\n";
        unset($this->connections[$conn->resourceId]);
        $conn->close();
    }
    
     /**
     * Triggered when a client sends data through the socket
     * @param  \Ratchet\ConnectionInterface $conn The socket/connection that sent the message to your application
     * @param  string $msg The message received
     * @throws \Exception
     */
    function onMessage(ConnectionInterface $conn, $msg) {
        $userData = $this->connections[$conn->resourceId]['user_data'];
        if ($msg === 'spawn-player') {
            $player = ['createdAt' => time(), 'killed' => false ];
            array_push($userData['players'], $player);
            $this->connections[$conn->resourceId]['user_data'] = $userData;
            // $conn->send(json_encode($userData));
        } else if ($msg === 'spawn-enemy') {
            $enemy = ['createdAt' => time() ];
            array_push($userData['enemies'], $enemy);
            $this->connections[$conn->resourceId]['user_data'] = $userData;
            // $conn->send(json_encode($userData));
        } else if ($msg === 'get-status') {
            $status = [
                'activePlayers' => 0,
                'killedPlayers' => 0,
                'enemies' => count($userData['enemies'])
            ];

            $currentTime = time();   // current time in seconds

            // find enemies which can kill at this specific time
            $enemies = $userData['enemies'];
            $Lethalenemies = [];
            foreach ($enemies as $enemy) {
                // enemies which are active after 2 mins
                if (($currentTime - $enemy['createdAt']) % 120 === 0 ) {
                    array_push($Lethalenemies, $enemy);
                }
            }
            // if there is any active enemy then search for players which can be killed
            if (count($Lethalenemies) > 0) {
                $players = $userData['players'];
                foreach ($players as $player) {
                    // only check those players which are not killed
                    if (!$player['killed']) {
                        $age = $currentTime - $player['createdAt'];
                        // check if player is 60 secs (1 min) old and it is not in 15 sec super state
                        if ($age > 60 && ($age % 60) > 0 && ($age % 60) < 16 ) {
                            $status['activePlayers']++;
                        } else {
                            $status['killedPlayers']++;
                        }

                    } else {
                        $status['killedPlayers']++;
                    }
                }
            } else {
                $status['activePlayers'] = count($userData['players']);
            }
            $conn->send(json_encode($status));
        }
    }
}