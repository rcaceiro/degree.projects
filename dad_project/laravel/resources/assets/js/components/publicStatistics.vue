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
        <div class="row">
            <div class="col-md-6">
                <h2>All MemoryGames played</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>MultiPlayer</th>
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
                <br/>
                <br/>
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
        <br/>
        <br/>
        <div class="col-md-3 col-xs-6">
            <router-link to="/login"
                         class="btn btn-lg btn-info btn-block"
                         tag="button">
                Back to Login
            </router-link>
        </div>
        <br/>
    </div>
</template>

<script type="text/javascript">
    export default {
        data: function () {
            return {
                title: "Games Statistics",
                multiTotals: '',
                singleTotals: '',
                totals: '',
                showFailure: false,
                failMessage: '',
                gamesTop:[],
            }
        },
        methods: {
            getStatistics: function () {
                axios.get('api/game/publicStatistics')

                    .then(response => {
                        //console.dir(response.data);
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
                    //console.dir(response.response);
                    this.showFailure = true;
                    this.failMessage = 'Error'
                });
            },
        },
        created() {
            this.getStatistics();
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