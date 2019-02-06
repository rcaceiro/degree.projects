<template><div>
    <nav class="navbar navbar-default navbar-fixed-top" key="gameArea">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand">{{ $root.title }}</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <router-link to="/game/lobby"
                                 tag="li"
                                 active-class="active">
                        <a>Lobby</a>
                    </router-link>
                    <router-link to="/game/games/0"
                                 active-class="active"
                                 tag="li">
                        <a>Games</a>
                    </router-link>
                    <router-link to="/game/statistics"
                                 active-class="active"
                                 tag="li">
                        <a>Statistics</a>
                    </router-link>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle"
                           data-toggle="dropdown" role="button"
                           aria-haspopup="true" aria-expanded="false">
                            {{ $root.user.nickname }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <router-link to="/game/profile"
                                         active-class="active"
                                         tag="li">
                                <a>Edit Profile</a>
                            </router-link>
                            <li role="separator" class="divider"></li>
                            <li><a href="#"
                                   @click.prevent="logout">Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>
    <br>
    <br>
    <br>
    <br>
    <simplert :useRadius="true"
              :useIcon="true"
              ref="simplert">
    </simplert>
    <router-view></router-view>
</div></template>

<script type="text/javascript">
    import io from 'socket.io-client';
    import Simplert from 'vue2-simplert';
    import VueNotifications from 'vue-notifications';
    import iziToast from 'izitoast';
    import 'izitoast/dist/css/iziToast.min.css';

    export default {
        components: {Simplert},
        sockets:{
            connect(){
                this.connected = true;
            },
            disconnect(){
                console.log('disconnected');
            },
            active_game_started(gameID)
            {
                let confirmFn = () =>{
                    if(this.$router.history.current.name == 'playerAGames'){
                        this.$el.querySelector("#game" + gameID).scrollIntoView(true);
                        window.scrollBy(0, -45);
                    } else {
                        this.$router.push(
                            {path: '/game/games/' + gameID});

                    }
                };
                let obj = {
                    title: 'Game ' + gameID,
                    message: gameID + ' has started!',
                    type: 'info',
                    useConfirmBtn: true,
                    customConfirmBtnText: 'Go to game',
                    onConfirm: confirmFn
                };
                this.$refs.simplert.openSimplert(obj);
            },
            your_turn(gameID)
            {
                this.showInfoMsg({
                    title: 'Your turn to play on game ' + gameID,
                    message: '',
                    buttons: [
                        ['<button><b>Go to game</b></button>', (instance, toast)=>{
                            instance.hide(toast, { transitionOut: 'fadeOut' }, 'button');
                            if(this.$router.history.current.name == 'playerAGames'){
                                this.$el.querySelector("#game" + gameID).scrollIntoView(true);
                                window.scrollBy(0, -45);
                            } else {
                                this.$router.push(
                                    {path: '/game/games/' + gameID});

                            }
                        }, true]

                    ]

                });
            },
            active_game_ended(data)
            {
                if(this.$root.user.nickname == data.winner){
                    this.showSuccessMsg({
                        title: 'Congratulations!',
                        message: 'you are the winner on game ' + data.gameID
                    });
                } else {
                    this.showInfoMsg({
                        title: 'Too bad!',
                        message: 'Game ' + data.gameID + ' ended and you lost...'
                    });
                }
            }
        },
        methods: {
            logout: function(){
                this.$root.logout();
            },
        },
        notifications: {
            showInfoMsg: {
                type: VueNotifications.types.info,
                title: 'default',
                message: 'default'
            },
            showSuccessMsg: {
                type: VueNotifications.types.success,
                title: 'default',
                message: 'default'
            }
        },
        created(){
            //disconnect incase 1st page to be loaded
            //(app connects with invalid token)
            this.$socket.disconnect();
            this.$socket.io.opts.query = 'token=' + sessionStorage.getItem('access_token');
            this.$socket.connect();
        },
        destroyed(){
            this.$socket.disconnect();
        }
    }
</script>

<style>
    .vue-steam-chat__wrapper--scroll::-webkit-scrollbar-corner, div::-webkit-scrollbar {
        background: unset;
    }
    thead {
        background-color: rgba(100, 218, 242, 0.43);
    }
</style>
