<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <link href="{{ asset('/css/bootstrap4.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('/css/game.css') }}" rel="stylesheet" type="text/css">
        
    </head>
    <body>
        <div class="content">
            <div class="title m-b-md">
                Switch Game
            </div>
            <div class="row game-panel">
                <div class="col-4">
                    <label>Players</label>
                    <button onClick="sendEvent('spawn-player')">Spawn Player</button>
                    <div><ul id="player-result"</ul></div>
                </div>
                <div class="col-4">
                    <label>Results</label>
                    <button onClick="sendEvent('get-status')">Game Status</button>
                    <div id="game-result"></div>
                </div>
                <div class="col-4">
                    <label>Enemies</label>
                    <button onClick="sendEvent('spawn-enemy')">Spawn Enemy</button>
                    <div><ul id="enemy-result"></ul></div>
                </div>
            </div>
        </div>
        
        <script>
            var gameSocket = new WebSocket("ws://127.0.0.1:8090");
            
            gameSocket.onopen = function (event) {
                gameSocket.send("conn-success"); 
            };
            
            function sendEvent(eventName) {
                gameSocket.send(eventName); 
            };

            gameSocket.onmessage = function (event) {
                var status = JSON.parse(event.data);
                console.log(status);
                switch (status.eventType) {
                    case 'spawn-player':
                        renderPlayers(status.data.players)
                        break;
                    case 'spawn-enemy':
                        renderEnemies(status.data.enemies)
                        break;
                    case 'get-status':
                        renderStatus(status.data);
                        renderPlayers(status.userData.players)
                        renderEnemies(status.userData.enemies)
                        break;
                    default:
                        break;
                }
            };

            function renderPlayers(players) {
                let playersHTML = [];
                $('#player-result').html('');
                $.each(players, function( index, player ) {
                    let lifeStr = player.killed ? 'Killed' : 'Alive'; 
                    playersHTML.push(
                        $('<li></li>').text(player.createdAt + ' (' + lifeStr + ')')
                    );
                });
                $('#player-result').html(playersHTML);
            }

            function renderEnemies(enemies) {
                let enemiesHTML = [$('<li></li>').text('ID (Time to come to life)')];
                $('#enemy-result').html('');
                $.each(enemies, function( index, enemy ) {
                    let timeToComeAlive = 120 - (((new Date().getTime() - new Date(enemy.createdAt*1000))/1000) % 120);
                    enemiesHTML.push(
                        $('<li></li>').text(enemy.createdAt + ' (' + Math.round(timeToComeAlive) + ' seconds)')
                    );
                });
                $('#enemy-result').html(enemiesHTML);
            }

            function renderStatus(status) {
                let statusStr = '<b>Active Players: </b>' + status.activePlayers +
                    '<br/><b>Enemies: </b>' + status.enemies +
                    '<br/><b>killed Players: </b>' + status.killedPlayers;
                $('#game-result').html($.parseHTML(statusStr));

            }

        </script>
        <script src="{{asset('js/jquery.min.js')}}"></script>
    </body>
</html>
