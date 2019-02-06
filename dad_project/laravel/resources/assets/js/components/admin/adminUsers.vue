<template>
    <div>

        <div class="row">
            <div class="alert alert-success" v-if="showSuccess">
                <button type="button" class="btn btn-link pull-right" v-on:click="showSuccess=false">&times;</button>
                <strong>{{ successMessage }}</strong>
            </div>

            <div class="alert alert-danger" v-if="showFailure">
                <button type="button" class="btn btn-link pull-right" v-on:click="showFailure=false">&times;</button>
                <strong>{{ failMessage }}</strong>
            </div>
        </div>



        <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.7/semantic.min.css"-->
              <!--media="screen" title="no title" charset="utf-8">-->

        <!--
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Nickname</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="player in players" :key="player.nickname" :class="player.blocked===1 ? 'danger' : ''">
                    <td>{{ player.name }}</td>
                    <td>{{ player.nickname }}</td>
                    <td>{{ player.email }}</td>
                    <td>
                        <a v-if="player.blocked===0" class="btn btn-xs btn-warning"
                           v-on:click.prevent="blockPlayer(player)">Block</a>
                        <a v-if="player.blocked===1" class="btn btn-xs btn-success"
                           v-on:click.prevent="unblockPlayer(player)">Unblock</a>
                        <a class="btn btn-xs btn-danger" v-on:click.prevent="deletePlayer(player)">Delete</a>
                    </td>
                </tr>
                </tbody>
            </table>


            <div :class="'ui right floated pagination menu'">

                <button :class="[pages.current_page > 1 ? '' : 'disabled', 'btn btn-info']"
                        v-on:click.prevent="previousPage(pages.current_page-1)">Previous
                </button>

                <button v-on:click.prevent="firstPage(1)"
                        :class="[pages.current_page === 1 ? 'disabled':'btn-link']"> 1
                </button>

                <button v-if="pages.current_page !== 1 && pages.current_page !== pages.last_page" :class="'disabled'">
                    {{pages.current_page}}

                </button>

                <button v-on:click.prevent="lastPage(pages.last_page)"
                        :class="[pages.current_page === pages.last_page ? 'disabled':'btn-link']"> {{pages.last_page}}
                </button>

                <button :class="[pages.current_page < pages.last_page ? '' : 'disabled', 'btn btn-info']"
                        v-on:click.prevent="nextPage(pages.current_page+1)">Next
                </button>

            </div>

            -->

            <div>
                <h2>Users</h2>
                <vue-good-table
                        title=""
                        :columns="columns"
                        :rows="rows"
                        :paginate="true"
                        :lineNumbers="true"
                        :globalSearch="true"
                        :responsive="true"
                        :defaultSortBy="{field: 'name', type: 'asc'}"
                        styleClass="table table-bordered table-striped condensed ">
                    <template slot="table-row-after" slot-scope="props">
                        <td align="center">
                            <a v-if="props.row.blocked===0" class="btn btn-xs btn-warning"
                               v-on:click.prevent="blockPlayer(props.row)">Block</a>
                            <a v-if="props.row.blocked===1" class="btn btn-xs btn-success"
                               v-on:click.prevent="unblockPlayer(props.row)">Unblock</a>
                            <a class="btn btn-xs btn-danger" v-on:click.prevent="deletePlayer(props.row)">Delete</a>
                        </td>
                    </template>
                </vue-good-table>
            </div>


        <!--</div>-->


    </div>
</template>

<script type="text/javascript">


    export default {
        data: function () {

            return {
                title: "Players List",
                showSuccess: false,
                showFailure: false,
                successMessage: '',
                failMessage: '',
                players: [],
                player: {},
                pages: [],
                links: [],
                columns: [
                    {
                        label: 'Name',
                        field: 'name',
                        filterable: false,
                    },
                    {
                        label: 'Nickname',
                        field: 'nickname',
                        filterable: false,
                    },
                    {
                        label: 'Email',
                        field: 'email',
                        type: 'number',
                        filterable: false,
                    },
                    {
                        label: '  Actions',
                    },


                ],
                rows: [],
            }
        },
        methods: {
            deletePlayer: function (player) {
                this.$root.axiosInstance.delete('../api/players/' + player.id)
                    .then(response => {
                        this.showSuccess = true;
                        this.successMessage = 'Player Deleted';
                        this.showFailure = false;
                        this.getPlayers();
                    }).catch(response => {
                    this.showSuccess = false;
                    this.showFailure = true;
                    this.failMessage = 'Error'
                });
            },
            blockPlayer: function (player) {
                var currentPlayer = {
                    blocked: 1,
                    reason_blocked: prompt("Please enter a reason: ")
                };

                if (currentPlayer.reason_blocked) {
                    this.toggleBlockFunction('Player Blocked', player, currentPlayer);
                }

            },
            unblockPlayer: function (player) {

                var currentPlayer = {
                    blocked: 0,
                    reason_reactivated: prompt("Please enter a reason: ")
                };

                if (currentPlayer.reason_reactivated) {
                    this.toggleBlockFunction('Player Unblocked', player, currentPlayer);
                }
            },
            toggleBlockFunction($confirmation, player, currentPlayer) {
                this.player = player;
                this.$root.axiosInstance.patch('../api/players/block/' + player.id, currentPlayer)
                    .then(response => {
                        this.showSuccess = true;
                        this.successMessage = $confirmation;
                        this.showFailure = false;
                        Object.assign(this.player, response.data.data);

                    }).catch(response => {
                    this.showSuccess = false;
                    this.showFailure = true;
                    this.failMessage = 'Error'


                });
            },
            /*getPlayers: function (num) {
                this.$root.axiosInstance.get('../api/players?page=' + num)
                    .then(response => {
                        this.players = response.data.data;
                        this.pages = response.data.meta;
                        this.links = response.data.links;

                        this.rows = this.players;
                    }).catch(response => {
                    console.dir(response);
                    this.showSuccess = false;
                    this.showFailure = true;
                    this.failMessage = 'Error'

                });
            },
            nextPage: function (num) {
                if (num - 1 < this.pages.last_page) {
                    this.getPlayers(num);
                }

            },
            previousPage: function (num) {
                console.log(num);
                if (num >= 1) {
                    this.getPlayers(num);
                }

            },
            firstPage: function (num) {
                if (num > 0 && num <= this.pages.last_page) {
                    this.getPlayers(num);
                }
            },
            lastPage: function (num) {
                if (num === this.pages.last_page) {
                    this.getPlayers(num);
                }
            },*/

            getPlayers: function () {
                this.$root.axiosInstance.get('../api/players')
                    .then(response => {
                        this.players = response.data.data;

                        this.rows = this.players;
                    }).catch(response => {
                    this.showSuccess = false;
                    this.showFailure = true;
                    this.failMessage = 'Error'

                });
            },

        },
        created() {
            //this.getPlayers(1);
            this.getPlayers();


        }
    }
</script>

<style scoped>
    .btn-xs {
        width: 4.5em;
        padding: 3% 0 3% 0;
        margin: 3%;
    }
</style>