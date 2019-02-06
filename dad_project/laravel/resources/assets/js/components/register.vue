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
                <h2 class="form-signin-heading" >Register new player</h2>

                <label for="name" class="sr-only">Name</label>
                <input type="text" id="name"
                       class="form-control" placeholder="Name"
                       required="" autofocus=""
                       v-model="user.name">

                <label for="nickname" class="sr-only">Nickname</label>
                <input type="text" id="nickname"
                       class="form-control" placeholder="Nickname"
                       required=""
                       v-model="user.nickname">

                <label for="email" class="sr-only">Email address</label>
                <input type="text" id="email"
                       class="form-control" placeholder="Email address"
                       required=""
                       v-model="user.email">

                <label for="inputPassword" class="sr-only">Password</label>
                <input type="password" id="inputPassword"
                       class="form-control" placeholder="Password"
                       required=""
                       v-model="user.password">

                <label for="confirmPassword" class="sr-only">Confirm Password</label>
                <input type="password" id="confirmPassword"
                       class="form-control" placeholder="Confirm Password"
                       required=""
                       v-model="user.password_confirmation">
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-offset-4 col-md-2 col-xs-6">
                <button type="submit"
                        class="btn btn-lg btn-primary btn-block"
                        @click.prevent="register()">
                    Register
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
                    name: "",
                    nickname: "",
                    email: "",
                    password: "",
                    password_confirmation: "",
                },
            }
        },
        methods: {
            register: function () {
                this.showSuccess = true;
                this.showError = false;
                this.successMessage = "Creating user..."

                axios.post('api/register', this.user)
                    .then(response => {
                        this.showSuccess = true;
                        this.showError = false;
                        this.successMessage = response.data.success
                    })
                    .catch(response => {
                        this.showSuccess = false;
                        this.showError = true;
                        this.$root.pushLaravelValidationErrorsTo(
                            this.failMessage,
                            response,
                            "Unable to register user:"
                        );
                    });
            }
        }
    }
</script>