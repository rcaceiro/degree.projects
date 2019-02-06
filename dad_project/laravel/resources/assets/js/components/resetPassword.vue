<template><div>
    <div class="row">
        <div class="alert alert-danger" v-if="showError">
            <button class="btn btn-link pull-right" @click="showError=false">
                &times;
            </button>
            <strong><p v-for="msg in failMessage">{{ msg }}</p></strong>
        </div>
        <div class="alert alert-success" v-if="showSuccess">
            <button class="btn btn-link pull-right" @click="showSuccess=false">
                &times;
            </button>
            <strong >{{ successMessage }}</strong>
        </div>
    </div>

    <form>
        <div class="row">
            <div class="col-md-offset-4 col-md-4 col-xs-12">
                <h2 class="form-signin-heading">Reset Password</h2>

                <label for="inputPassword" class="sr-only">New Password</label>
                <input type="password" id="inputPassword"
                       class="form-control" placeholder="New Password"
                       required="" autofocus=""
                       v-model="user.password">

                <label for="confirmPassword" class="sr-only">Confirm Password</label>
                <input type="password" id="confirmPassword"
                       class="form-control" placeholder="Confirm New Password"
                       required=""
                       v-model="user.password_confirmation">
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-offset-6 col-md-2 col-xs-offset-6 col-xs-6">
                <button type="submit"
                        class="btn btn-lg btn-primary btn-block"
                        @click.prevent="resetPassword()">
                    Reset
                </button>
            </div>
        </div>
    </form>
</div></template>

<script type="text/javascript">
    export default {
        data: function () {
            return {
                showSuccess: false,
                successMessage: '',
                showError: false,
                failMessage: [],
                user: {
                    password: "",
                    password_confirmation: "",
                    email: this.$route.params.email,
                    token: this.$route.params.token
                }
            }
        },
        methods: {
            resetPassword: function(){
                this.showError = false;
                this.showSuccess = true;
                this.successMessage = 'Loading..'

                axios.post('../../api/reset-password', this.user)
                    .then(response => {
                        this.showError = false;
                        this.showSuccess = true;
                        this.successMessage = 'Password reset successfully! Redirecting to login..'
                        setTimeout(() => {
                            this.$router.push({ name: 'login' });
                        }, 3000);
                    })
                    .catch(response => {
                        this.showSuccess = false;
                        this.showError = true;
                        this.$root.pushLaravelValidationErrorsTo(
                            this.failMessage,
                            response
                        );
                    });
            },
        },
        mounted(){
            axios.post('../../api/verify-reset-request', this.user)
            .catch(response => {
                this.$router.push({ name: 'errorPage', params:{ errorCode: 404 }});
            });
        }
    }
</script>