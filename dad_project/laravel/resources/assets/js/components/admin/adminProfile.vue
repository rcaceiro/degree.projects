<template><div>
    <div>
        <div class="alert alert-success" v-if="showSuccess">
            <button type="button" class="btn btn-link pull-right" v-on:click="showSuccess=false">&times;</button>
            <strong>{{ successMessage }}</strong>
        </div>
        <div class="alert alert-danger" v-if="showFailure">
            <button class="btn btn-link pull-right" @click="showFailure=false">
                &times;
            </button>
            <strong><p v-for="msg in failMessage">{{ msg }}</p></strong>
        </div>
    </div>

    <form v-if="!authenticated">
        <div class="row">
            <div class="col-md-offset-4 col-md-4 col-xs-12">
                <h2 class="form-signin-heading">To continue, please validate your account</h2>

                <label for="inputPassword">Password</label>
                <input type="password" id="inputPassword"
                       class="form-control"
                       autofocus=""
                       placeholder="Password"
                       v-model="user.password">
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-offset-6 col-md-2 col-xs-offset-6 col-xs-6">
                <button type="submit"
                        class="btn btn-lg btn-primary btn-block"
                        @click.prevent="authenticate">
                    Continue
                </button>
            </div>
        </div>
    </form>
    <form v-else>
        <div class="row">
            <div class="col-md-offset-4 col-md-4 col-xs-12">
                <h2 class="form-signin-heading">Edit Profile</h2>

                <label for="email">Admin e-mail address:</label>
                <input type="text" id="email"
                       class="form-control"
                       autofocus=""
                       placeholder="New admin e-mail address"
                       :disabled="!authenticated"
                       v-model="user.email">
                <br>
                <label for="newPassword">New password</label>
                <input type="password" id="newPassword"
                       class="form-control"
                       placeholder="New password"
                       :disabled="!authenticated"
                       v-model="user.password">
                <br>
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword"
                       class="form-control"
                       placeholder="Confirm Password"
                       :disabled="!authenticated"
                       v-model="user.password_confirmation">
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-offset-4 col-md-2 col-xs-6">
                <button type="submit"
                        :disabled="!authenticated"
                        class="btn btn-lg btn-primary btn-block"
                        @click.prevent="editAdmin">
                    Save
                </button>
            </div>
            <div class="col-md-2 col-xs-6">
                <button type="submit"
                        class="btn btn-lg btn-info btn-block"
                        @click.prevent="goBack">
                    Cancel
                </button>
            </div>
        </div>
    </form>
</div></template>

<script type="text/javascript">

    export default{
        data: function() {
            return {
                authenticated: false,
                showSuccess: false,
                showFailure: false,
                successMessage: '',
                failMessage: [],
                user: {
                    nicknameOrEmail: '',
                    id:'',
                    email: '',
                    password: '',
                    password_confirmation: '',
                }
            }
        },
        methods: {
            goBack: function(){
                this.$router.push({ path: '/admin' });
            },
            editAdmin: function(){
                this.showSuccess = true;
                this.showFailure = false;
                this.successMessage = 'Updating...';

                this.$root.axiosInstance.patch(
                    '../api/admin/' + this.user.id, this.user)
                .then(response => {
                    this.showSuccess = true;
                    this.showFailure = false;
                    this.successMessage = response.data;
                }).catch(response => {
                    this.showSuccess = false;
                    this.showFailure = true;
                    this.$root.pushLaravelValidationErrorsTo(
                        this.failMessage,
                        response,
                        "Unable to update profile:"
                    );
                });
            },
            authenticate: function(){
                this.showSuccess = true;
                this.showFailure = false;
                this.successMessage = 'Validating...';
                this.user.nicknameOrEmail = this.user.email;

                this.$root.axiosInstance.post(
                    '../api/verify-user', this.user)
                .then(response => {
                    this.showSuccess = false;
                    this.authenticated = true;
                    this.user.password = '';
                })
                .catch(response => {
                    this.showSuccess = false;
                    this.showFailure = true;
                    this.failMessage.splice(0);
                    this.failMessage.push(response.response.data);
                });
            }
        },
        created() {
            Object.assign(this.user, this.$root.user);
        },
        destroyed(){
            this.$root.axiosInstance.get('../api/details')
            .then(response => {
                this.$root.user = response.data.data;
            });
        }
    }
</script>