<template>
    <div>
        <div class="row" hidden="true">
            <div class="col-md-offset-10 col-md-1 col-xs-offset-8 col-xs-4">
                <button class="btn btn-lg btn-primary"
                        :disabled="refreshing"
                        @click.prevent="refresh">
                    <span class="glyphicon glyphicon-refresh"></span>
                </button>
            </div>
        </div>

        <box v-if="activeGames.length == 0" :active="true"
             :type="'warning'"
             style="background-color: rgba(255, 255, 255, 0.50)"
             key="noactivegames">
            <div slot="box-body">
                <div class="simplert__icon simplert__icon--warning">
                    <div class="simplert__line simplert__line--warning"></div>
                    <div class="simplert__line simplert__line--warning-2"></div>
                </div>

                <h1>Oops!!</h1>
                <br/>
                <br/>
                <h4>No active games found! Check back again or start your own!</h4>
                <br/>
                <router-link to="/game/lobby"
                             tag="button"
                             active-class="active"
                             class="btn btn-lg btn-primary center-block">
                    <span>Create a Game!</span>
                </router-link>
                <br/>
                <br/>
            </div>
        </box>


        <br>
        <br>
        <div class="game-separator" v-for="(game, key) in activeGames"
             :key="game.gameID" :id="'game' + game.gameID">
            <div class="row">
                <div class="col-md-offset-3 col-md-6
                            col-xs-offset-2 col-xs-8">
                    <h1 class="text-center">
                        Game {{ game.gameID}}
                    </h1>
                    <br>
                </div>
                <div class="col-md-3 col-xs-2">
                    <button style="font-size: 200%;color: #090909"
                            v-if="game.gameEnded"
                            class="btn btn-link pull-right"
                            @click="removeGame(key)">
                        ❌
                    </button>
                </div>
            </div>
            <div class="game-area" >
                <div v-if="!game.gameStarted" class="row">
                    <div class="col-md-3 col-xs-12 " >
                        <h2>&nbsp;Players
                            ({{ game.players.length }}/{{ game.maxPlayers }}):</h2>
                        <h3 v-for="player of game.players" v-if="!player.bot && !player.afk"
                            style="color:rgba(60, 160, 200, 0.80);">
                            &nbsp;&nbsp;&nbsp;-&nbsp;
                            {{ player.nickname }}
                        </h3>
                        <h3 v-for="player of game.players" v-if="player.bot"
                            style="color:rgba(148, 148, 148, 0.83)">
                            &nbsp;&nbsp;&nbsp;-&nbsp;
                            {{ player.nickname }}
                        </h3>

                        <br/>
                        <div v-if="game.createdBy == $root.user.nickname && game.players.length < game.maxPlayers" >
                            <button class="btn btn-md btn-warning btn-block"
                                    style="width:150px; margin-left:30px;"
                                    @click.prevent="addBot(game.gameID)">Add a Cool Robot
                            </button>
                            <br/>
                        </div>
                    </div>

                    <div class="col-md-6 col-xs-12">

                    </div>

                    <div class="col-md-3 col-xs-12" align="center">
                        <vue-steam-chat
                                style="height: 300px; width: 200px;"
                                :messages="messagesGame[key]"
                                @vue-steam-chat-on-message="message => {
                                    onNewMessageGame(message, game.gameID)}">
                        </vue-steam-chat>
                        <br>
                    </div>
                    <div class="col-md-offset-1 col-md-4
                            col-xs-offset-1 col-xs-10">
                        <button class="btn btn-lg btn-danger btn-block"
                                @click.prevent="leaveGame(game.gameID)">Leave Game
                        </button>
                    </div>


                    <div v-if="game.createdBy === $root.user.nickname"
                         class="col-md-offset-1 col-md-4
                               col-xs-offset-1 col-xs-10">
                        <button class="btn btn-lg btn-primary btn-block"
                                @click.prevent="startGame(game.gameID)">Start Game
                        </button>
                    </div>
                </div>

                <div class="row" v-else>
                    <div class="game-scores col-md-3 col-xs-12">
                        <h2 style="color:#f8bb86; white-space: nowrap;">
                            <strong>&nbsp;Turn number: </strong>
                            <strong style="color:#636b6f">{{ game.gameTurn }}</strong>
                        </h2>
                        <h2 style="color:#f8bb86"><strong>&nbsp;Scoreboard: </strong></h2>
                        <h3 v-for="(player, id) of game.players" v-if="!player.bot && !player.afk"
                            style="color:rgba(60, 160, 200, 0.80);">
                            &nbsp;&nbsp;&nbsp;-&nbsp;{{ player.nickname }}:&nbsp;{{ player.pontos }}
                        </h3>
                        <h3 v-for="(player, id) of game.players" v-if="player.bot"
                            style="color:rgba(148, 148, 148, 0.83)">
                            &nbsp;&nbsp;&nbsp;-&nbsp;{{ player.nickname }}:&nbsp;{{ player.pontos }}
                        </h3>
                        <h3 v-for="(player, id) of game.players" v-if="player.afk"
                            style="text-decoration:line-through; color:rgba(188, 52, 52, 0.89);">
                            &nbsp;&nbsp;&nbsp;-&nbsp;{{ player.nickname }}:&nbsp;{{ player.pontos }}
                        </h3>

                    </div>
                    <div class="game-board col-md-6 col-xs-12">
                        <h2 class="text-center"
                            v-if="!game.gameEnded">
                            &nbsp;<strong>Player turn:</strong> {{ game.players[game.playerTurn-1].nickname}}

                        </h2>

                        <h2 class="text-center"
                            v-else>
                            &nbsp;<strong>Winner: </strong>{{ game.winner }}
                        </h2>

                        <br/>
                        <div v-for="(pieces, row) of game.board" class="game-row" align="center">
                            <img class="game-piece"
                                 v-for="(piece, column) of pieces"
                                 v-bind:src="'../../img/' + piece"
                                 @click="clickPiece(game.gameID, row, column)">
                        </div>
                        <br>
                    </div>
                    <div class="col-md-3 col-xs-12" align="center">
                        <vue-steam-chat
                                style="height: 300px; width: 200px;"
                                :messages="messagesGame[key]"
                                @vue-steam-chat-on-message="message => {
                                    onNewMessageGame(message, game.gameID)}">
                        </vue-steam-chat>
                    </div>
                </div>
            </div>
            <br>
        </div>
    </div>
</template>

<script type="text/javascript">
    require('vue-steam-chat/dist/index.css');
    import VueSteamChat from 'vue-steam-chat';
    import Box from 'vue-info-box';

    export default {
        components: {
            VueSteamChat,
            Box
        },
        props: {
            gameid: {
                Type: Number,
                required: false
            },
        },
        data: function () {
            return {
                refreshing: false,
                activeGames: [],
                messagesGame: [],
            }
        },
        sockets: {
            set_active_games(activeGames) {
                this.activeGames = activeGames;
                this.refreshing = false;
                if (this.$route.params.gameid !== '0') {
                    setTimeout(() => {
                        let gamecontent =
                            this.$el.querySelector("#game" + this.$route.params.gameid);
                        if(gamecontent){
                            gamecontent.scrollIntoView(true);
                            window.scrollBy(0, -45);
                        }
                    }, 1000);
                }
                for (let game of this.activeGames) {
                    this.messagesGame.push([]);
                    this.$socket.emit('get_active_game_chat', {gameID: game.gameID});
                }
            },
            add_active_game(game) {
                this.activeGames.push(game);
                this.messagesGame.push([]);
                this.$socket.emit('get_active_game_chat', {gameID: game.gameID});
            },
            remove_active_game(gameID) {
                for (let i = 0; i < this.activeGames.length; i++) {
                    if (this.activeGames[i].gameID === gameID) {
                        this.activeGames.splice(i, 1);
                        this.messagesGame.splice(i, 1);
                        break;
                    }
                }
            },
            update_active_game(game) {
                for (let i = 0; i < this.activeGames.length; i++) {
                    if (this.activeGames[i].gameID === game.gameID) {
                        this.activeGames.splice(i, 1, game);
                        break;
                    }
                }

            },
            show_piece(info) {
                for (let i = 0; i < this.activeGames.length; i++) {
                    if (this.activeGames[i].gameID === info.gameID) {
                        this.activeGames[i].board[info.row].splice(info.column, 1, info.piece);
                        break;
                    }
                }
            },
            set_active_game_messages(data) {
                for (let i = 0; i < this.activeGames.length; i++) {
                    if (this.activeGames[i].gameID === data.gameID) {
                        this.messagesGame.splice(i, 1, data.chatEntries);
                        break;
                    }
                }
            },
            add_active_game_message(data) {
                for (let i = 0; i < this.activeGames.length; i++) {
                    if (this.activeGames[i].gameID === data.gameID) {
                        this.messagesGame[i].push(data.message);
                    }
                }
            },
        },
        methods: {
            refresh() {
                this.refreshing = true;
                this.activeGames.splice(0, this.activeGames.length);
                this.$socket.disconnect();
                this.$socket.io.opts.query = 'token=' + sessionStorage.getItem('access_token');
                setTimeout(() => {
                    this.$socket.connect();
                    this.loadActiveGames();
                }, 1000);

            },
            loadActiveGames() {
                this.$socket.emit('get_active_games');
            },
            leaveGame(gameID) {
                this.$socket.emit('leave_active_game', {gameID: gameID});
            },
            removeGame(index) {
                this.activeGames.splice(index, 1);
                this.messagesGame.splice(index, 1);
            },
            addBot(gameID) {
                this.$socket.emit('add_bot', {gameID: gameID});
            },
            startGame(gameID) {
                this.$socket.emit('init_active_game', {gameID: gameID});
            },
            clickPiece(gameID, row, column) {
                this.$socket.emit('game_click', {
                    gameID: gameID,
                    row: row,
                    column: column
                });
            },
            onNewMessageGame(message, gameID) {
                this.$socket.emit('send_active_game_message', {
                    username: this.$root.user.nickname,
                    text: message,
                    gameID: gameID
                });
            },

        },
        created() {
            this.loadActiveGames();
        },
    }
</script>

<style scoped>
    .game-separator {
        border-style: solid;
        border-width: 2px 0 0 0;
        border-color: black;
    }

    .game-board {
        white-space: nowrap;
    }

    .game-piece {
        display: inline-block;
    }

    h1 {
        text-align: center;
        color: #f8bb86;
        font-size: 50px;
    }

    h4 {
        text-align: center;
        font-family: Trebuchet MS, Helvetica, sans-serif;
        font-size: 22px;
    }

    .tut-info-box h3 {
        color: rgb(255, 165, 36);
    }

    .simplert__icon {
        position: relative;
        width: 80px;
        height: 80px;
        margin: 10px auto;
        -webkit-border-radius: 50%;
        -moz-border-radius: 50%;
        border-radius: 50%;
    }

    simplert__icon--warning {
        border: 4px solid #f8bb86;
    }

    .simplert__line--warning {
        position: absolute;
        top: 10px;
        left: 50%;
        width: 5px;
        height: 35px;
        margin-left: -2px;
        -webkit-border-radius: 2px;
        border-radius: 2px;
        background-color: #f8bb86;
    }

    .simplert__line--warning-2 {
        position: absolute;
        bottom: 10px;
        left: 50%;
        width: 7px;
        height: 7px;
        margin-left: -3px;
        -webkit-border-radius: 50%;
        border-radius: 50%;
        background-color: #f8bb86;
    }

    .simplert *, .simplert :after, .simplert :before {
        box-sizing: inherit;
    }

    .simplert {
        position: fixed;
        z-index: 999;
        top: 0;
        left: 0;
        display: none;
        overflow: auto;
        width: 100%;
        height: 100%;
        text-align: center;
        background-color: #000;
        background-color: rgba(0, 0, 0, .4);
    }


</style>