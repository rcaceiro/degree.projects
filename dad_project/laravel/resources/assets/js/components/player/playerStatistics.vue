<template>
    <div>

        <div class="row">
            <div class="alert alert-danger" v-if="showFailure">
                <button type="button" class="btn btn-link pull-right" v-on:click="showFailure=false">&times;</button>
                <strong>{{ failMessage }}</strong>
            </div>
        </div>

        <br/>
        <br/>
        <h2>All MemoryGames played by {{this.$root.user.nickname}}</h2>
        <div class="row">
            <div class="table-responsive col-offset-1 col-md-10">
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>MultiPlayer</th>
                        <th>SinglePlayer</th>
                        <th>Total</th>
                        <th>Victories</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{{ playerMultiTotals }}</td>
                        <td>{{ playerSingleTotals }}</td>
                        <td>{{ playerTotalGames }}</td>
                        <td>{{ playerTotalVictories }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-1"></div>
        </div>
        <br/>
        <br/>
        <div class="row">
            <div class="table-responsive col-md-6">
                <h2>All MemoryGames played</h2>
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Multiplayer</th>
                        <th>SinglePlayer</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{{ multiTotals }}</td>
                        <td>{{ singleTotals }}</td>
                        <td>{{ totals }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <h2>MemoryGame Top3 Players</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Position</th>
                            <th>Nickname</th>

                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(top3, pos) in gamesTop">
                            <td>{{pos+1}}</td>
                            <td>{{ top3 }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
    export default {
        data: function () {
            return {
                title: "Games Statistics",
                playerMultiTotals: '',
                playerSingleTotals: '',
                playerTotalGames:'',
                playerTotalVictories:'',
                showFailure: false,
                failMessage: '',
                multiTotals: '',
                singleTotals: '',
                totals: '',
                gamesTop:[],
            }
        },
        methods: {
            getStatisticsPlayer: function () {
                this.$root.axiosInstance.get('../api/game/playerStatistics/' + this.$root.user.nickname )
                    .then(response => {
                        this.playerMultiTotals = response.data.playerMultiTotals;
                        this.playerSingleTotals = response.data.playerSingleTotals;
                        this.playerTotalGames = response.data.playerTotalGames;
                        this.playerTotalVictories = response.data.playerTotalVictories;

                    }).catch(response => {
                    this.showFailure = true;
                    this.failMessage = 'Error'
                });
            },
            getStatisticsAll: function () {
                this.$root.axiosInstance.get('../api/game/publicStatistics')

                    .then(response => {
                        this.multiTotals = response.data.multiTotals;
                        this.singleTotals = response.data.singleTotals;
                        this.totals = response.data.totals;
                        this.gamesTop = response.data.top3;
                        
                        for(let i=0; i <= this.gamesTop.length; i++){
                            if (this.gamesTop[i]=='admin'){
                                this.gamesTop[i]='Cool-Botz';
                            }
                        }

                    }).catch(response => {
                    this.showFailure = true;
                    this.failMessage = 'Error'
                });
            },
        },
        created() {
            this.getStatisticsPlayer();
            this.getStatisticsAll();
        }
    }
</script>