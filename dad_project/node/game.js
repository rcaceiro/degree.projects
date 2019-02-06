var axios = require('axios');
var globals = require('./_globals');

class MemoryGame {
    constructor(ID, playerName, token,
                rows, columns, maxPlayers,
                gameStartedCallBack, callbackAdvanceTurn, callbackGameEnded, turnPieceCallback) {
        this.PieceState = {
            EMPTY: 0,
            HIDDEN: 1,
            SHOWN: 2
        };
        this.gameStartedCallBack = gameStartedCallBack;
        this.callbackAdvanceTurn = callbackAdvanceTurn;
        this.callbackGameEnded = callbackGameEnded;
        this.turnPieceCallback = turnPieceCallback;

        this.gameID = ID;
        this.token = token;
        this.createdBy = playerName;
        this.createdAt = new Date().toLocaleTimeString('en-GB');

        this.gameEnded = false;
        this.gameStarted = false;

        this.winner = '';

        this.players = [];

        this.gameTurn = 1;
        this.firstPlay = null;

        this.maxPlayers = maxPlayers;
        this.playerTurn = 1;

        this.rows = rows;
        this.columns = columns;

        //MATRIX of objects with {id & path}
        this.board = [];
        this.hidden = {};
        this.frozen = false;

        this.botTurn = false;

        this.join({
            'nickname': playerName,
            'bot': false
        });
    }

    playerToPlay() {
        return this.players[this.playerTurn - 1];
    }

    join(playerObj) {
        if (this.gameStarted === false) {
            this.players.push(playerObj);
        }

        if (this.players.length === this.maxPlayers) {
            this.startGame();
        }
    }

    leave(playerName) {
        for (let i = 0; i < this.players.length; ++i) {
            if (this.players[i].nickname == playerName) {
                this.players.splice(i, 1);
                return true;
            }
        }
        return false;
    }

    startGame() {
        console.log('Game ' + this.gameID + ' has started!');
        for (let i = 0; i < this.players.length; ++i) {
            this.players[i].afk = false;
            this.players[i].pontos = 0;
            this.players[i].jogada = 0;
        }

        //DEFAULT BEHAVIOR
        if (this.rows === 0) {
            switch (this.players.length) {
                case 1:
                case 2:
                case 3:
                    this.rows = 4;
                    break;
                case 4:
                    this.rows = 6;
                    break;
            }
        }
        if (this.columns === 0) {
            switch (this.players.length) {
                case 1:
                case 2:
                    this.columns = 4;
                    break;
                case 3:
                case 4:
                    this.columns = 6;
                    break;
            }
        }
        let piecesNeeded = (this.rows * this.columns) / 2;
        axios.create({
            headers: {
                Accept: 'application/json',
                Authorization: 'Bearer ' + this.token
            }
        })
            .get(
                globals.SERVER_URL +
                '/api/get-shuffled-pieces/' +
                piecesNeeded)
            .then(response => {
                this.hidden = response.data.hidden;
                for (let row = 0; row < this.rows; ++row) {
                    this.board[row] = [];
                    for (let column = 0; column < this.columns; ++column) {
                        this.board[row][column] =
                            response.data.tiles[row * this.columns + column];
                        this.board[row][column].state = this.PieceState.HIDDEN;
                    }
                }
                this.consoleLogBoard();
                this.gameStarted = true;
                this.gameStartedCallBack(this);
                this.startTurn();
            }).catch(response => {
            console.log('\nUnexpected error!!!\nError: ',
                response);
        });
    }

    startTurn() {
        this.firstPlay = null;
        this.frozen = false;
    
        if(this.players.length > 1){
            let timerTurn = JSON.parse(JSON.stringify(this.gameTurn));
            let time = this.players[this.playerTurn - 1].afk ? 4000 : 30000;

            setTimeout(() => {

                if (this.gameTurn > timerTurn) {
                    return;
                }
                if (this.firstPlay !== null) {
                    this.board[this.firstPlay.oldRow]
                        [this.firstPlay.oldColumn].state = this.PieceState.HIDDEN;
                }
                console.dir(this.playerToPlay().nickname + '\'s turn has timed out!');
                this.players[this.playerTurn - 1].afk = true;
                this.nextPlayer();
                ++this.gameTurn;
                if(this.playerToPlay().bot == false){
                    this.callbackAdvanceTurn(this, true);
                }else{
                    this.callbackAdvanceTurn(this, false);
                    this.playBot();

                }
                this.startTurn();
            }, time);
        }
    }

    play(row, column) {

        if (!this.isValidPlay(row, column)) {
            return null;
        }
        this.players[this.playerTurn - 1].afk = false;

        if (this.firstPlay === null) {
            this.firstPlay = JSON.parse(JSON.stringify(this.board[row][column]));
            this.firstPlay.oldRow = row;
            this.firstPlay.oldColumn = column;
            this.board[row][column].state = this.PieceState.SHOWN;

            this.turnPieceCallback(this, this.board[row][column].path, {
                'column':column,
                'row':row,
            });

        } else {
            if (this.frozen) {
                return null;
            }
            this.frozen = true;

            ///////////////////PLAYER GUESSED RIGHT////////////////////////////
            if (this.firstPlay.id === this.board[row][column].id) {

                ++this.gameTurn;
                this.players[this.playerTurn - 1].pontos++;
                this.players[this.playerTurn - 1].jogada = this.gameTurn;
                this.board[row][column].state = this.PieceState.SHOWN;

                //AFTER 1SECOND SET BOTH AT EMPTY
                setTimeout(() => {
                    // if(this.firstPlay !== null) {
                    this.board[this.firstPlay.oldRow]
                        [this.firstPlay.oldColumn].state = this.PieceState.EMPTY;
                    // }
                    this.board[row][column].state = this.PieceState.EMPTY;

                    if (this.isBoardComplete()) {
                        this.endGame();
                    } else {
                        this.startTurn();
                        this.callbackAdvanceTurn(this, false);

                        if(this.playerToPlay().bot == true){
                            this.playBot();
                        }
                    }

                }, 1000);

            } else {
                //////////////////PLAYER MISSES//////////////////////
                ++this.gameTurn;
                //AFTER 1SECOND


                setTimeout(() => {
                    this.nextPlayer();

                    this.board[this.firstPlay.oldRow]
                        [this.firstPlay.oldColumn].state = this.PieceState.HIDDEN;
                    this.board[row][column].state = this.PieceState.HIDDEN;

                    this.startTurn();
                    if(this.playerToPlay().bot == false){
                        this.callbackAdvanceTurn(this, true);
                    }else{
                        this.callbackAdvanceTurn(this, false);
                        this.playBot();

                    }
                }, 1000);


            }

            this.turnPieceCallback(this, this.board[row][column].path, {
                'column':column,
                'row':row,
            });

        }
    }

    playBot(){
        setTimeout(() => {
        let playable = this.setRandomBotPlay();
        this.play(playable.row, playable.col);
        }, 1000);

        setTimeout(() => {
            let playable2 = this.setRandomBotPlay();
            this.play(playable2.row, playable2.col);
        }, 2000);
    }


    setRandomBotPlay(){
        let randCol;
        let randRow;
        do {
            randCol = Math.floor((Math.random() * this.columns));
            randRow = Math.floor((Math.random() * this.rows));
        } while (!this.isValidPlay(randRow, randCol));

        return ({
            'row':randRow,
            'col':randCol
        })
    }



    nextPlayer() {
        ++this.playerTurn;
        if (this.playerTurn > this.players.length) {
            this.playerTurn = 1;
        }
    }

    isValidPlay(row, column) {
        if (row < 0 || row >= this.rows || column < 0 || column >= this.columns) {
            return false;
        }
        if (this.board[row][column].state !== this.PieceState.HIDDEN) {
            return false;
        }
        return true;
    }

    isBoardComplete() {
        for (let row = 0; row < this.rows; ++row) {
            for (let column = 0; column < this.columns; ++column) {
                if (this.board[row][column].state !== this.PieceState.EMPTY) {
                    return false;
                }
            }
        }
        return true;
    }

    endGame() {
        this.gameEnded = true;
        let jogadaMostPoints = Number.MAX_SAFE_INTEGER;
        let mostPoints = -1;

        //PARSE WINNER
        for (let i = 0; i < this.players.length; ++i) {
            if (this.players[i].pontos >= mostPoints) {
                if (this.players[i].pontos === mostPoints) {
                    if (this.players[i].jogada < jogadaMostPoints) {
                        this.winner = this.players[i].nickname;
                        mostPoints = this.players[i].pontos;
                        jogadaMostPoints = this.players[i].jogada;
                    }

                } else {
                    this.winner = this.players[i].nickname;
                    mostPoints = this.players[i].pontos;
                    jogadaMostPoints = this.players[i].jogada;
                }
            }
        }
        this.callbackGameEnded(this);
        process.stdout.write(this.winner +
            ' has won the game with ' + mostPoints +
            " points on turn " + jogadaMostPoints + "!\n");

        for(let i=0; i < this.players.length; i++){
            if(this.winner == this.players[i].nickname && this.players[i].bot){
                this.winner = 'admin';
            }
            if(this.players[i].bot == true){
                this.players.splice(i,1);
                --i;
            }
        }

        axios.create({
            headers: {
                Accept: 'application/json',
                Authorization: 'Bearer ' + this.token
            }
        })
            .post(
                globals.SERVER_URL + '/api/game',
                {
                    game: this
                })
            .then(response => {
                console.dir('Game ' + this.gameID + ' saved on server.');
            }).catch(response => {
            console.log('\nUnexpected error!!!\nError: ',
                response);
        });

    }

    consoleLogBoard() {
        process.stdout.write("\nBoard:");
        for (let row = 0; row < this.rows; ++row) {
            console.log("");
            for (let column = 0; column < this.columns; ++column) {
                if (this.board[row][column].id < 10) {
                    process.stdout.write(" ");
                }
                process.stdout.write(this.board[row][column].id + " ");
            }
        }
        console.log("");
    }
}

module.exports = MemoryGame;