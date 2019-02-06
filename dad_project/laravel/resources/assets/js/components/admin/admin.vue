<template><div>
    <nav class="navbar navbar-default navbar-fixed-top" key="adminArea">
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
                    <router-link to="/admin/players"
                                 tag="li"
                                 active-class="active">
                        <a>Players</a>
                    </router-link>
                    <router-link to="/admin/gamepieces"
                                 tag="li"
                                 active-class="active">
                        <a>Game Pieces</a>
                    </router-link>
                    <router-link to="/admin/statistics"
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
                            {{ this.$root.user.nickname }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <router-link to="/admin/profile"
                                         active-class="active"
                                         tag="li">
                                <a>Edit Profile</a>
                            </router-link>
                            <router-link to="/admin/config"
                                         active-class="active"
                                         tag="li">
                                <a>Configurations</a>
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
    <router-view></router-view>
</div></template>


<script type="text/javascript">
    export default {
        methods: {
            logout: function(){
                this.$root.logout();
            }
        },
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