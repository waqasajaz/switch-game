<?php
namespace App\Http\Controllers;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\http\Controllers\GameHelper;

class WebSocketController extends Controller implements MessageComponentInterface{
    private $connections = [];
    
     /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn) {
        log_and_echo("A new user connected. ResourceID is: $conn->resourceId \n");
        $this->connections[$conn->resourceId] = ['user_data' => [
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
        log_and_echo("User disconnected. ResourceID is: $conn->resourceId \n");
        $disconnectedId = $conn->resourceId;
        unset($this->connections[$disconnectedId]);
    }
    
     /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e){
        log_and_echo("An error has occurred with user $conn->resourceId: {$e->getLine()}, {$e->getMessage()}\n");
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
        log_and_echo("Event Receied: $msg \n");
        $userData = $this->connections[$conn->resourceId]['user_data'];
        if ($msg === 'spawn-player') {
            $userData = GameHelper::spawnPlayer($userData, $conn);
            $this->connections[$conn->resourceId]['user_data'] = $userData;
        } else if ($msg === 'spawn-enemy') {
            $userData = GameHelper::spawnEnemy($userData, $conn);
            $this->connections[$conn->resourceId]['user_data'] = $userData;
        } else if ($msg === 'get-status') {
            $userData = GameHelper::processGameStatus($userData, $conn);
            $this->connections[$conn->resourceId]['user_data'] = $userData;
        }
    }
}