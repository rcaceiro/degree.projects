<template><div>
    <div class="row co">
        <div class="alert alert-danger" v-if="showFailure">
            <button type="button" class="btn btn-link pull-right" v-on:click="showFailure=false">&times;</button>
            <strong>{{ failMessage }}</strong>
        </div>
    </div>

    <el-form>

        <div class="row">
            <div class="col-md-offset-3 col-md-6 col-xs-12">
                <h2 class="form-signin-heading">Please sign in</h2>

                <label for="username" class="sr-only">Email address or Nickname</label>
                <input type="text" id="username"
                       class="form-control" placeholder="Email address or Nickname"
                       required="" autofocus="" key="loginNick"
                       v-model="user.nicknameOrEmail"
                       v-on:keyup.13="login()">

                <label for="inputPassword" class="sr-only">Password</label>
                <input type="password" id="inputPassword"
                       class="form-control" placeholder="Password"
                       required="" key="loginPass"
                       v-model="user.password"
                       v-on:keyup.13="login()">
            </div>
        </div>
        <div class="row">
            <div class="checkbox col-md-offset-3 col-md-3 col-xs-6">
                <label>
                    <input type="checkbox" value="remember-me"
                    v-model="rememberMe">Remember me
                </label>
            </div>
            <div class="col-md-3 col-xs-6">
                <router-link to="/forgot-password"
                             class="btn btn-link pull-right"
                             tag="button">
                    Forgot password?
                </router-link>
            </div>
        </div>

        <div class="row">
            <div class="col-md-offset-3 col-md-3 col-xs-6">
                <router-link to="/register"
                             class="btn btn-lg btn-info btn-block"
                             tag="button">
                    Register
                </router-link>
                <br/>
            </div>
            <div class="col-md-3 col-xs-6">
                <button type="submit"
                        class="btn btn-lg btn-primary btn-block"
                        @click.prevent="login()"
                        @keydown.enter="login()">
                    Sign in
                </button>
                <br/>
            </div>
            <div class="col-md-offset-3 col-md-3 col-xs-6">
                <router-link to="/statistics"
                             class="btn btn-lg btn-success btn-block"
                             tag="button">
                    Statistics
                </router-link>
            </div>
            <div class="col-md-3 col-xs-6" align="center">
                <a class="btn btn-sm" href="/login/github">
                    <i class="fa fa-github fa-3x"
                    style="color: #333"></i>
                </a>
                <a class="btn btn-sm" href="/login/google">
                    <i class="fa fa-google fa-3x"
                    style="color:#dd4b39"></i>
                </a>
                <a class="btn btn-sm" href="/login/facebook">
                    <i class="fa fa-facebook fa-3x"
                    style="color:#3b5998"></i>
                </a>
                <a class="btn btn-sm" href="/login/twitter">
                    <i class="fa fa-twitter fa-3x"
                    style="color:#55acee"></i>
                </a>
            </div>
        </div>



        <modal name="loading"
               :classes="'transparentmodal'"
               :adaptive="true"
               :clickToClose="false"
               :height="300"
               :width="300">
            <circle8 class="circle"></circle8>
        </modal>

    </el-form>
</div></template>

<script type="text/javascript">
    import jwt from 'jsonwebtoken';
    import {Circle8} from 'vue-loading-spinner';

    export default {
        components: {
            Circle8
        },
       data: function () {
           return {
               showFailure: false,
               successMessage: '',
               failMessage: '',
               user: {
                   nicknameOrEmail: "",
                   password: "",
               },
               rememberMe: false,
           }
       },
        methods: {
            login: function(){
                this.$modal.show('loading');
                this.showFailure = false;
                axios.post('/api/login', this.user)
                    
                .then(response => {
                    let access_token = response.data.access_token;
                    let refresh_token = response.data.refresh_token;

                    sessionStorage.setItem('access_token', access_token);
                    if(this.rememberMe){
                        localStorage.setItem('refresh_token', refresh_token);
                    }
                    //ADD TOKEN TO AXIOS INSTANCE
                    this.$root.initAxios(access_token);

                    this.$root.getUser(() => {
                        var decoded = jwt.decode(access_token,
                                                {complete: true});
                        if (decoded.payload.scopes.includes('admin')) {
                            this.$router.push({path: '/admin'});
                        } else {
                            this.$router.push({path: '/game'});
                        }
                    });
                })
                .catch(response => {
                    this.$modal.hide('loading');
                    this.showFailure = true;
                    this.failMessage = response.response.data;
                });
            },
        }
    }
</script>

<style>
    .circle { position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);}

    .transparentmodal { background: transparent; box-shadow: unset;}
</style>