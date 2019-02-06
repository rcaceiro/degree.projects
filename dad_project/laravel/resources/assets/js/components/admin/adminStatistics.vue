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
            <h1 style="text-align: center; font-size:40px">Platform Statistics</h1>
            <br/>
            <br/>

            <div class="chart col-md-8" align="center">
                <h2>Daily MemoryGames played</h2>
                <chartjs-line v-if="loaded"
                              :labels="mylabels"
                              :datasets="mydatasets"
                              :responsive="true"
                              :maintainAspectRatio="true"
                              :option="myoption">

                </chartjs-line>
            </div>
            <br/>
            <br/>

            <div>
                <vue-good-table class="col-md-4" align="center"
                                title="All MemoryGames played"
                                :columns="columnsPlatformGames"
                                :rows="rowsPlatformGames"
                                :paginate="false"
                                :lineNumbers="false"
                                :globalSearch="false"
                                :responsive="true"
                                styleClass="table table-bordered condensed">
                </vue-good-table>
            </div>

            <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.7/semantic.min.css"-->
                  <!--media="screen" title="no title" charset="utf-8">-->




        </div>

        <br/>
        <br/>
        <div >
            <vue-good-table
                    title="Player Statistics"
                    :columns="columns"
                    :rows="rows"
                    :paginate="true"
                    :lineNumbers="true"
                    :globalSearch="false"
                    :responsive="true"
                    :defaultSortBy="{field: 'playerTotalVictories', type: 'desc'}"
                    styleClass="table table-bordered table-striped condensed">
            </vue-good-table>
        </div>
    </div>
</template>


<script type="text/javascript">


    export default {
        //
        data: function () {

            return {
                title: "Games Statistics",
                multiTotals: '',
                singleTotals: '',
                totals: '',
                showFailure: false,
                failMessage: '',
                playersStatistics: [],
                chartsDate: [],
                chartsTotal: [],
                mylabels: [],
                mydatasets: [{
                    label: "Memory Games Played",
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.2)',
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',

                    ],
                    borderWidth: 1,
                }],
                myoption: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        yAxes: [{
                            id: 'y-axis-0',
                            stacked: true,
                            ticks: {
                                beginAtZero: true,
                                mirror: false,
                                suggestedMin: 0,
                                suggestedMax: 0,


                            },
                        }],
                    }
                },
                loaded: false,
                columns: [
                    {
                        label: 'Name',
                        field: 'playerName',
                        filterable: false,
                    },
                    {
                        label: 'Nickname',
                        field: 'playerNickname',
                        filterable: false,
                    },
                    {
                        label: 'MultiPlayer',
                        field: 'playerMultiTotals',
                        type: 'number',
                        filterable: false,
                    },
                    {
                        label: 'SinglePlayer',
                        field: 'playerSingleTotals',
                        type: 'number',
                        filterable: false,
                    },
                    {
                        label: 'Total',
                        field: 'playerTotalGames',
                        type: 'number',
                        filterable: false,
                    },
                    {
                        label: 'Victories',
                        field: 'playerTotalVictories',
                        type: 'number',
                        filterable: false,
                    },
                ],
                rows: [],
                columnsPlatformGames: [
                    {
                        label: 'MultiPlayer',
                        field: 'multiTotals',
                        filterable: false,
                    },
                    {
                        label: 'SinglePlayer',
                        field: 'singleTotals',
                        filterable: false,
                    },
                    {
                        label: 'Total',
                        field: 'totals',
                        type: 'number',
                        filterable: false,
                    },
                ],
                rowsPlatformGames: [],
            }
        },
        methods: {
            getStatistics: function () {
                this.$root.axiosInstance.get('../api/game/publicStatistics')
                    .then(response => {
                        this.multiTotals = response.data.multiTotals;
                        this.singleTotals = response.data.singleTotals;
                        this.totals = response.data.totals;

                        this.rowsPlatformGames.push({
                            'multiTotals': this.multiTotals,
                            'singleTotals': this.singleTotals,
                            'totals': this.totals
                        });

                    }).catch(response => {
                    this.showFailure = true;
                    this.failMessage = 'Error'
                });
            },

            getStatisticsPlayer: function () {

                this.$root.axiosInstance.get('../api/game/adminStatistics')
                    .then(response => {
                        this.playersStatistics = response.data.users;

                        this.chartsDate = response.data.chartsDate;
                        this.chartsTotal = response.data.chartsTotal;

                        this.rows = this.playersStatistics;

                        this.mylabels = response.data.chartsDate;
                        this.mydatasets[0].data = response.data.chartsTotal;
                        this.myoption.scales.yAxes[0].ticks.suggestedMax = Math.max(...this.chartsTotal) * 1.1;


                        this.loaded = true;


                    }).catch(response => {
                    this.showFailure = true;
                    this.failMessage = 'Error'

                })

            },

        },
        created() {
            this.getStatistics();
            this.getStatisticsPlayer();
        }

    }
</script>
