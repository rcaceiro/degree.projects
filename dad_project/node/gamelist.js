var MemoryGame = require('./game.js');

class GameList {
    constructor() {
        this.contadorID = 0;
        this.games = new Map();
    }

    gameByID(gameID) {
        return this.games.get(gameID);
    }

    getLobbyGamesOf(playerName) {
        let games = [];
        //ITERATE GAMES
        for (let [id, game] of this.games) {
            if ((!game.gameStarted) && (!game.gameEnded)) {

                //ITERATE PLAYERS
                let inGame = false;
                for (let playerObj of game.players) {
                    if (playerObj.nickname == playerName) {
                        inGame = true;
                        break;
                    }
                }
                if (!inGame) {
                    games.push(game);
                }
            }
        }
        return games;
    }

    createGame(playerName, token, rows, columns, maxPlayers,
               gameStartedCallBack, callbackAdvanceTurn, callbackGameEnded,turnPieceCallback) {
        this.contadorID = this.contadorID + 1;
        let game = new MemoryGame(this.contadorID,
            playerName,
            token,
            rows,
            columns,
            maxPlayers,
            gameStartedCallBack,
            callbackAdvanceTurn,
            callbackGameEnded,
            turnPieceCallback);
        this.games.set(game.gameID, game);
        return game;
    }

    joinGame(gameID, playerObj) {
        let game = this.gameByID(gameID);
        if (typeof game === 'undefined' || game.gameEnded || game.gameStarted) {
            return null;
        }
        for (let player of game.players) {
            if (player.nickname == playerObj.nickname) {
                return null;
            }
        }
        game.join(playerObj);
        return game;
    }

    leaveGame(gameID, playerName) {
        let game = this.gameByID(gameID);
        if (typeof game === 'undefined' || game.gameEnded || game.gameStarted) {
            return null;
        }
        if (game.createdBy == playerName) {
            this.games.delete(gameID);
        } else {
            if (!game.leave(playerName)) {
                return null;
            }
        }
        return game;
    }

    getConnectedGamesOf(playerName) {
        let games = [];
        for (var [key, game] of this.games) {
            if (!game.gameEnded) {

                //ITERATE PLAYERS
                let inGame = false;
                for (let playerObj of game.players) {
                    if (playerObj.nickname == playerName) {
                        inGame = true;
                        break;
                    }
                }
                if (inGame) {
                    games.push(game);
                }
            }
        }
        return games;
    }

    startGame(gameID, playerName) {
        let game = this.gameByID(gameID);
        if (typeof game === 'undefined' || game.gameEnded || game.gameStarted) {
            return null;
        }
        if (game.createdBy == playerName) {
            game.startGame();
            return game;
        }
        return null;
    }

    getStartedGameOf(gameID, playerName) {
        let game = this.gameByID(gameID);
        if (typeof game === 'undefined' || game.gameEnded || !game.gameStarted) {
            return null;
        }
        if (playerName == game.playerToPlay().nickname) {
            return game;
        }
        return null;
    }

}

module.exports = GameList;
