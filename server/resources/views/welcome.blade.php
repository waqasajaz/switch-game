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
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref">
            <div class="content">
                <div class="title m-b-md">
                    Switch Game
                </div>

                <div class="links">
                    <button onClick="sendEvent('spawn-player')">Spawn Player</button>
                    <button onClick="sendEvent('spawn-enemy')">Spawn Enemy</button>
                    <button onClick="sendEvent('get-status')">Game Status</button>
                </div>

                <div class="results">
                    <pre id="result"></pre>
                </div>
            </div>
        </div>
        <script>
            var exampleSocket = new WebSocket("ws://127.0.0.1:8090");
            exampleSocket.onopen = function (event) {
              exampleSocket.send("conn-success"); 
            };
            exampleSocket.onmessage = function (event) {
                console.log(event.data);
                document.getElementById("result").innerHTML = event.data;
            }
            function sendEvent(eventName) {
                exampleSocket.send(eventName); 
            }
        </script>
    </body>
</html>
