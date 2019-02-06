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
                <h2 class="form-signin-heading">Forgot your password?</h2>

                <label for="email" class="sr-only">Email address</label>
                <input type="text" id="email"
                       class="form-control" placeholder="Email address"
                       required="" autofocus=""
                       v-model="user.email">
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-offset-4 col-md-2 col-xs-6">
                <button type="submit"
                        class="btn btn-lg btn-primary btn-block"
                        @click.prevent="forgotPassword()">
                    Reset
                </button>
            </div>
            <div class="col-md-2 col-xs-6">
                <router-link to="/"
                             class="btn btn-lg btn-info btn-block"
                             tag="button">
                    Cancel
                </router-link>
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
                    email: "",
                },
            }
        },
        methods: {
            forgotPassword: function () {
                this.showSuccess = true;
                this.showError = false;
                this.successMessage = "Resolving request..."

                axios.post('api/forgot-password', this.user)
                .then(response => {
                    this.showSuccess = true;
                    this.showError = false;
                    this.successMessage = response.data.success
                })
                .catch(response => {
                    this.showSuccess = false;
                    this.showError = true;
                    this.failMessage.splice(0);
                    if(response.response.data.error != null){
                        this.failMessage.push(response.response.data.error);
                    } else {
                        this.failMessage.push("An unexpected error occurred, please try again later.");
                    }
                });
            }
        }
    }
</script>