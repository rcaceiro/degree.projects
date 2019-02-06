<template>
    <div>
        <div class="row">
            <div class="col-md-offset-1 col-md-3 col-xs-offset-2 col-xs-8">
                <button class="btn btn-lg btn-warning btn-block"
                        @click.prevent="openCreateGame">New Game
                </button>
            </div>
            <!--<div hidden="true" class="col-md-offset-6 col-md-1 col-xs-offset-1 col-xs-3">-->
                <!--<button class="btn btn-lg btn-primary"-->
                        <!--:disabled="refreshing"-->
                        <!--@click.prevent="refresh">-->
                    <!--<span class="glyphicon glyphicon-refresh"></span>-->
                <!--</button>-->
            <!--</div>-->
        </div>
        <br>
        <br>
        <div class="row">
            <div class="table-responsive col-md-8 col-sm-8 col-xs-12">
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Created by</th>
                        <th>Players</th>
                        <th>Created at</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="game in lobbyGames" :key="game.gameID">
                        <td>{{ game.gameID }}</td>
                        <td>{{ game.createdBy }}</td>
                        <td>{{ game.players.length }}/{{ game.maxPlayers }}</td>
                        <td>{{ game.createdAt }}</td>
                        <td>
                            <a class="btn btn-xs btn-primary" v-on:click.prevent="join(game.gameID)">Join</a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12" align="center">
                <vue-steam-chat
                        style="height: 400px; width: 300px;"
                        :messages="this.messagesLobby"
                        @vue-steam-chat-on-message="onNewMessageLobby">
                </vue-steam-chat>
            </div>
        </div>
        <modal name="create-game"
               :adaptive="true"
               :height="450">
            <div>
                <h1 class="modal-title">New Game</h1>
                <button class="btn btn-link pull-right"
                        @click="$modal.hide('create-game')">
                    ❌
                </button>
            </div>
            <div class="modal-box">
                <div class="row">
                    <div class="col-md-6 col-xs-8">
                        <h3>Game Type</h3>
                        <input type="radio" id="singleplayer" value="false" v-model="multiPlayer">
                        <label for="singleplayer">Single Player</label>&nbsp;&nbsp;&nbsp;<p></p>

                        <input type="radio" id="multiplayer" value="true" v-model="multiPlayer">
                        <label for="multiplayer">Multi Player</label>
                        <br/>
                    </div>
                    <div class="col-md-6 col-xs-4" align="center">
                        <h4><span style="color:red"
                              v-if="gameRows%2===1 && gameColumns%2===1">
                            Invalid number of pieces!
                        </span></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <h3>Board Size</h3>
                        <div class="col-md-5">
                            <label v-if="gameRows==0" for="rowsSlider">Rows: Default</label>
                            <label v-else for="rowsSlider">Rows: {{ gameRows }}</label>
                            <input type="range" min="0" max="8"
                                   class="slider" id="rowsSlider"
                                   v-model="gameRows">
                        </div>
                        <div class="col-md-5">
                            <label v-if="gameColumns==0" for="columnsSlider">Columns: Default</label>
                            <label v-else for="columnsSlider">Columns: {{ gameColumns }}</label>
                            <input type="range" min="0" max="8"
                                   class="slider" id="columnsSlider"
                                   v-model="gameColumns">
                        </div>
                    </div>
                </div>
                <br/>
                <br/>
                <div class="row">
                    <div class="col-md-offset-6 col-md-5
                            col-s-offset-6 col-xs-5
                            col-xs-offset-4 col-xs-8">
                        <button class="btn btn-lg btn-warning btn-block"
                                @click.prevent="createGame">Create Game
                        </button>
                    </div>
                </div>
            </div>
        </modal>
    </div>
</template>

<script type="text/javascript">
    import VueSteamChat from 'vue-steam-chat';

    require('vue-steam-chat/dist/index.css');

    export default {
        components: {
            VueSteamChat,
        },
        data: function () {
            return {
                multiPlayer: true,
                gameRows: 0,
                gameColumns: 0,
                refreshing: false,
                lobbyGames: [],
                messagesLobby: []
            };
        },
        sockets: {
            set_lobby(lobbyGames) {
                this.lobbyGames = lobbyGames;
                this.refreshing = false;
            },
            add_lobby_game(game) {
                this.lobbyGames.push(game);
            },
            remove_lobby_game(gameID) {
                for (let i = 0; i < this.lobbyGames.length; i++) {
                    if (this.lobbyGames[i].gameID === gameID) {
                        this.lobbyGames.splice(i, 1);
                        break;
                    }
                }
            },
            update_lobby_game_players(game) {
                for (let i = 0; i < this.lobbyGames.length; i++) {
                    if (this.lobbyGames[i].gameID === game.gameID) {
                        this.lobbyGames.splice(i, 1, game);
                        break;
                    }
                }
            },
            set_lobby_messages(messages)
            {
                this.messagesLobby = messages;
            },
            add_lobby_message(message)
            {
                this.messagesLobby.push(message);
            }
        },
        methods: {
            refresh() {
                this.refreshing = true;
                this.lobbyGames.splice(0, this.lobbyGames.length);
                this.$socket.disconnect();
                this.$socket.io.opts.query = 'token=' + sessionStorage.getItem('access_token');
                setTimeout(() => {
                    this.$socket.connect();
                    this.loadLobby();
                    this.loadLobbyChat();
                }, 1000);

            },
            onNewMessageLobby(message) {
                this.$socket.emit('send_lobby_message', {
                    username: this.$root.user.nickname,
                    text: message
                });
            },
            loadLobby() {
                this.$socket.emit('get_lobby');
            },
            loadLobbyChat() {
                this.$socket.emit('get_lobby_chat');
            },
            openCreateGame() {
                this.$modal.show('create-game');
            },
            createGame() {
                if (this.gameRows % 2 === 1 && this.gameColumns % 2 === 1) {

                    return;
                }
                this.$modal.hide('create-game');
                this.$socket.emit('create_game',
                    {
                        multiPlayer: this.multiPlayer,
                        gameRows: this.gameRows,
                        gameColumns: this.gameColumns
                    });
                this.multiPlayer = true;
                this.gameRows = 0;
                this.gameColumns = 0;
            },
            join(gameID) {
                this.$socket.emit('join_game', {gameID: gameID});
            }
        },

        created() {
            this.loadLobby();
            this.loadLobbyChat();
        }
    }
</script>

<style scoped>
    .modal-box {
        margin: 0 5% 0 5%;
    }

    .modal-title {
        display: inline-block;
        margin: 2% 0 0 2%;
    }

    /*.pull-right { margin: 0 2% 0 0; }*/
    .btn-xs {
        width: 4.5em;
        padding: 3% 0 3% 0;
    }

    .slider {
        -webkit-appearance: none;
        width: 100%;
        height: 15px;
        border-radius: 5px;
        background: #d3d3d3;
        outline: none;
        opacity: 0.7;
        -webkit-transition: .2s;
        transition: opacity .2s;
    }

    .slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        background: #27d8b7;
        cursor: pointer;
    }

    .slider::-moz-range-thumb {
        width: 25px;
        height: 25px;
        border-radius: 50%;
        background: #27d8b7;
        cursor: pointer;
    }
</style>
