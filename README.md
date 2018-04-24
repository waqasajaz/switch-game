# switch-game

*Please clone repository and push your code here.*

**Requirements**
A laravel application which uses websockets and renders webpage with following options
 1. Spawn Player Button
 2. Spawn Enemy Button
 3. Game Status Button
 4. Result Area


**Problem Statement** -
At any point of time pressing on **Game Status Button** will display the total number of players (live or killed) and enemies in the system. At the load there would be zero player and enemies. User can generate a player using  **Spawn Player Button**. User can also generate an enemy using **Spawn Enemy Button**.

**Conditions** - 
A player has two modes 
1. normal (default state, intial state)
2. super

Every player toggles its state from normal to super for 15 secs after one minute. An enemy kills a player (remove a player from system)  every 2 mins but it cannot kill a player if it is in super mode.

***NOTE***   -  Consider scenarios that there can be multiple enemies and players in the system at the same time. 
